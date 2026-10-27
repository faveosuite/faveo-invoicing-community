<?php

namespace App\Http\Controllers\Front;

use App\Enums\OrderAbility;
use App\Http\Controllers\Controller;
use App\License\Services\ProductBundleStampingService;
use App\Model\Common\Setting;
use App\Model\Order\Order;
use App\Model\Product\ProductUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage as Attach;
use Illuminate\Validation\Rule;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Exception\NoKeyLoadedException;
use phpseclib3\Net\SFTP;
use phpseclib3\Net\SSH2;
use RuntimeException;
use Symfony\Component\HttpFoundation\IpUtils;

class DeployController extends Controller
{
    /** Resolved once per request; every SSH/SFTP connection goes to this IP, never back to the hostname. */
    private string $ip = '';

    private mixed $credential = null;

    public function __construct(protected ProductBundleStampingService $stampingService)
    {
        $this->middleware('auth');
    }

    public function getVersions(int $orderId): JsonResponse
    {
        try {
            $order = Order::with('subscription')->findOrFail($orderId);

            if (! authorizeOwnership($order->client)) {
                return errorResponse(__('message.unauthorized_action'), 403);
            }

            if ($reason = $this->deployBlockedReason($order)) {
                return errorResponse($reason, 403);
            }

            $versions = $this->deployableUploads($order)
                ->select('id', 'version', 'title', 'file')
                ->latest()
                ->get();

            return successResponse('', $versions->toArray());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return errorResponse(__('message.record_not_found'));
        } catch (\Exception $e) {
            \Logger::exception($e);

            return errorResponse(__('message.sorry_something_wrong'), 500);
        }
    }

    public function deployStep(Request $request): JsonResponse
    {
        $installing = fn (): bool => $request->step === 'install';

        $request->validate([
            'step' => 'required|in:verify,install,status,upload,extract',
            'offset' => 'nullable|integer|min:0',
            'order_id' => 'required|integer',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
            'auth_method' => 'required|in:private_key,password',
            'private_key' => 'nullable|string|required_if:auth_method,private_key',
            'password' => 'nullable|string|required_if:auth_method,password',
            'deploy_mode' => 'required|in:fresh_install,extract_only',
            // verify may run before the path is known (the wizard's connection-only check on "Next").
            'deploy_path' => ['nullable', 'string', 'max:1000', Rule::requiredIf(fn (): bool => $request->deploy_mode === 'extract_only' && $request->step !== 'verify')],
            'install_domain' => ['nullable', 'string', 'max:255', Rule::requiredIf($installing)],
            'install_email' => ['nullable', 'email', 'max:255', Rule::requiredIf($installing)],
            'web_server' => ['nullable', 'in:1,2', Rule::requiredIf($installing)],
            'ssl_type' => ['nullable', 'in:A,B,C', Rule::requiredIf($installing)],
            'ssl_cert_path' => ['nullable', 'string', 'max:500', Rule::requiredIf(fn (): bool => $installing() && $request->ssl_type === 'C')], // NOSONAR - Laravel validation rule, repeated by design
            'ssl_key_path' => ['nullable', 'string', 'max:500', Rule::requiredIf(fn (): bool => $installing() && $request->ssl_type === 'C')],
            'ssl_ca_path' => ['nullable', 'string', 'max:500', Rule::requiredIf(fn (): bool => $installing() && $request->ssl_type === 'C')],
            'web_user' => ['nullable', 'string', 'max:64', 'regex:/^[a-z_][a-z0-9_.-]*$/i'],
            'sudo_password' => 'nullable|string',
            'version_id' => 'nullable|integer',
        ], [
            'host.required' => __('message.deploy_host_required'),
            'port.required' => __('message.deploy_port_invalid'),
            'port.integer' => __('message.deploy_port_invalid'),
            'port.min' => __('message.deploy_port_invalid'),
            'port.max' => __('message.deploy_port_invalid'),
            'username.required' => __('message.deploy_username_required'),
            'password.required_if' => __('message.deploy_password_required'),
            'private_key.required_if' => __('message.deploy_private_key_required'),
            'deploy_path.required' => __('message.deploy_path_required'),
            'install_domain.required' => __('message.deploy_domain_required'),
            'install_email.required' => __('message.deploy_email_required'),
            'install_email.email' => __('message.deploy_email_invalid'),
            'ssl_cert_path.required' => __('message.deploy_cert_path_required'),
            'ssl_key_path.required' => __('message.deploy_key_path_required'),
            'ssl_ca_path.required' => __('message.deploy_ca_path_required'),
        ]);

        try {
            $order = Order::with('subscription')->find((int) $request->order_id);

            if (! $order || ! authorizeOwnership($order->client)) {
                return errorResponse(__('message.unauthorized_action'), 403);
            }

            if ($reason = $this->deployBlockedReason($order)) {
                return errorResponse($reason, 403);
            }

            $ip = $this->publicIp($request->host);
            if ($ip === null) {
                return errorResponse(__('message.deploy_host_not_allowed'), 422);
            }
            $this->ip = $ip;

            try {
                $this->credential = $request->auth_method === 'private_key'
                    ? PublicKeyLoader::load(trim($request->private_key))
                    : $request->password;
            } catch (NoKeyLoadedException $e) {
                return errorResponse(__('message.deploy_invalid_private_key', ['error' => $e->getMessage()]), 422);
            }

            $deployPath = $request->deploy_mode === 'fresh_install'
                ? '/var/www/faveo'
                : rtrim((string) $request->deploy_path, '/');

            return match ($request->step) { // @phpstan-ignore match.unhandled
                'verify' => $this->stepVerify($request, $deployPath),
                'install' => $this->stepInstall($request, $order),
                'status' => $this->stepStatus($request, $order),
                'upload' => $this->stepUpload($request, $order),
                'extract' => $this->stepExtract($request, $order, $deployPath),
            };
        } catch (\Exception $e) {
            \Logger::exception($e);

            return errorResponse(__('message.sorry_something_wrong'));
        }
    }

    private function stepVerify(Request $request, string $deployPath): JsonResponse
    {
        $ssh = new SSH2($this->ip, (int) $request->port);

        if ($error = $this->login($ssh, $request)) {
            return $error;
        }

        // No path yet = connection-only check; the full verify on Deploy also creates the path.
        if ($request->deploy_mode === 'extract_only' && $deployPath !== '') {
            [, $exitCode] = $this->run($ssh, 'mkdir -p '.escapeshellarg($deployPath));

            if ($exitCode !== 0) {
                return errorResponse(__('message.deploy_path_create_failed', ['path' => $deployPath]));
            }
        }

        return successResponse(__('message.deploy_ssh_verified'));
    }

    private function stepInstall(Request $request, Order $order): JsonResponse
    {
        // A GitHub page link (…/blob/…) serves HTML; rewrite it to the raw file.
        $installScript = (string) preg_replace('#^https://github\.com/([^/]+/[^/]+)/blob/#', 'https://raw.githubusercontent.com/$1/', (string) Setting::value('help_support_url'));
        if ($installScript === '') {
            return errorResponse(__('message.deploy_install_script_not_configured'), 422);
        }

        $ssh = new SSH2($this->ip, (int) $request->port);
        $ssh->setTimeout(120);

        if ($error = $this->login($ssh, $request)) {
            return $error;
        }

        // Every option the script would otherwise prompt for — there is no keyboard, so a missed one would hang it.
        // License and order number come from the order itself, never from the request.
        $args = [
            '--domain' => $request->install_domain,
            '--email' => $request->install_email,
            '--license' => (string) $order->serial_key,
            '--order' => (string) $order->number,
            '--webserver' => (string) $request->web_server === '2' ? 'nginx' : 'apache',
            '--ssl' => ['A' => 'certbot', 'B' => 'self-signed', 'C' => 'paid-ssl'][$request->ssl_type],
            '--sql' => 'mysql',
            '--sqlreser' => 'no',
            '--php' => '8.4',
            '--release' => 'official',
            '--faveoenv' => 'production',
            '--meili' => 'yes',
            '--nats' => 'no',
            '--node' => 'no',
        ];
        if ($request->ssl_type === 'C') {
            $args += ['--certfile' => $request->ssl_cert_path, '--keyfile' => $request->ssl_key_path, '--cacertfile' => $request->ssl_ca_path];
        }
        $argv = implode(' ', array_map(fn ($flag, $value) => $flag.' '.escapeshellarg((string) $value), array_keys($args), $args));

        [$dir, $log, $rc] = $this->installFiles($order);
        $main = basename((string) parse_url($installScript, PHP_URL_PATH));

        // faveo.sh only checks the OS, then runs debian_block.sh / rhel_block.sh from its own folder — fetch them too.
        // ponytail: sibling names follow Faveo's installer layout; make them a setting if another product's script differs.
        $base = dirname($installScript);
        $downloads = [$main => $installScript, 'debian_block.sh' => $base.'/debian_block.sh', 'rhel_block.sh' => $base.'/rhel_block.sh'];

        // Download synchronously (quick) so a bad URL fails here with the downloader's own message.
        // Minimal images ship wget or curl, not always both.
        $fetch = fn (string $file, string $url): string => 'if command -v wget >/dev/null; then wget -nv -O '.escapeshellarg($file).' '.escapeshellarg($url).' 2>&1; '.
            'else curl -fsSL -o '.escapeshellarg($file).' '.escapeshellarg($url).' 2>&1; fi';
        [$output, $exitCode] = $this->run($ssh,
            'rm -rf '.escapeshellarg($dir).' && mkdir -m 700 '.escapeshellarg($dir).' && cd '.escapeshellarg($dir).' && '.
            implode(' && ', array_map(fn ($file, $url) => '{ '.$fetch($file, $url).'; }', array_keys($downloads), $downloads))
        );

        if ($exitCode !== 0) {
            return errorResponse(__('message.deploy_install_script_errors'), 422, ['exit_code' => $exitCode, 'output' => $output]);
        }

        // The script needs root. -n: a sudo user without a password fails fast with sudo's own message in the log.
        $run = 'bash '.escapeshellarg($main).' '.$argv;
        if ($request->username !== 'root') {
            $run = $request->filled('sudo_password')
                ? "printf '%s\\n' ".escapeshellarg($request->sudo_password)." | sudo -S -p '' ".$run
                : 'sudo -n '.$run;
        }

        // The install takes many minutes — far past any web timeout — so it runs detached on the server,
        // writing to a log the wizard polls via the "status" step. The rc file appears when it ends.
        // It runs inside $dir because the script writes credentials.txt to its working directory.
        $job = 'cd '.escapeshellarg($dir).' && { '.$run.'; } > '.escapeshellarg($log).' 2>&1 < /dev/null; '.
            'echo $? > '.escapeshellarg($rc).'; rm -f '.escapeshellarg($dir).'/*.sh';
        $ssh->exec('nohup setsid sh -c '.escapeshellarg($job).' < /dev/null > /dev/null 2>&1 &');

        return successResponse(__('message.deploy_install_started'));
    }

    /** One poll of the background install: log bytes after `offset`, and the result once it has ended. */
    private function stepStatus(Request $request, Order $order): JsonResponse
    {
        $ssh = new SSH2($this->ip, (int) $request->port);

        if ($error = $this->login($ssh, $request)) {
            return $error;
        }

        [$dir, $log, $rc] = $this->installFiles($order);
        $offset = (int) $request->offset;
        $chunkSize = 65536;

        // rc before log: once rc exists the log is complete, so nothing written in between is missed.
        $exitCode = trim((string) $ssh->exec('cat '.escapeshellarg($rc).' 2>/dev/null'));
        $chunk = (string) $ssh->exec('tail -c +'.($offset + 1).' '.escapeshellarg($log).' 2>/dev/null | head -c '.$chunkSize);
        $done = $exitCode !== '' && strlen($chunk) < $chunkSize;

        $data = [
            'offset' => $offset + strlen($chunk),
            // Colour codes are kept: the wizard colours the log and reads the script's red lines as the failure reason.
            // mb_scrub: a chunk can split a UTF-8 char, and invalid UTF-8 would break the JSON response.
            'output' => mb_scrub($chunk, 'UTF-8'),
            'done' => $done,
            'exit_code' => $done ? (int) $exitCode : null,
        ];

        if ($done && $data['exit_code'] === 0) {
            $data['credentials'] = trim((string) $ssh->exec('cat '.escapeshellarg($dir.'/credentials.txt').' 2>/dev/null'));
            // Every SSL option the script offers ends in HTTPS.
            $data['setup_url'] = 'https://'.$request->install_domain.'/';
        }

        return successResponse('', $data);
    }

    /**
     * Working dir, log and exit-code paths on the remote server — derived here, never sent by the client.
     *
     * @return array{string, string, string}
     */
    private function installFiles(Order $order): array
    {
        $dir = '/tmp/faveo_install_'.$order->id;

        return [$dir, $dir.'/install.log', $dir.'/install.rc'];
    }

    private function stepUpload(Request $request, Order $order): JsonResponse
    {
        $upload = $this->deployableUploads($order)
            ->when($request->filled('version_id'), fn ($q) => $q->where('id', $request->version_id))
            ->latest()
            ->first();

        if (! $upload) {
            return errorResponse(__('message.renew_subscription_download'));
        }

        $product = $upload->product;

        $resolvedFile = $upload->resolvedFile();
        $filePath = 'products/'.$resolvedFile;

        if (empty($resolvedFile) || ! Attach::exists($filePath)) {
            return errorResponse(__('message.deploy_file_not_found'));
        }

        try {
            $tmpFile = $this->stampingService->stampToLocalFile($filePath, $product, $upload->version, $order);
        } catch (RuntimeException) {
            return errorResponse(__('message.deploy_upload_failed'));
        }

        try {
            $sftp = new SFTP($this->ip, (int) $request->port);

            if ($error = $this->login($sftp, $request)) {
                return $error;
            }

            if (! $sftp->put($this->remoteZipPath($order), $tmpFile, SFTP::SOURCE_LOCAL_FILE)) {
                return errorResponse(__('message.deploy_upload_failed'));
            }
        } finally {
            @unlink($tmpFile);
        }

        return successResponse(__('message.deploy_file_uploaded'));
    }

    private function stepExtract(Request $request, Order $order, string $deployPath): JsonResponse
    {
        $sudoPassword = $request->filled('sudo_password') ? $request->sudo_password : null;

        $withSudo = fn (string $cmd) => $sudoPassword
            ? 'echo '.escapeshellarg($sudoPassword).' | sudo -S '.$cmd
            : $cmd;

        $ssh = new SSH2($this->ip, (int) $request->port);
        $ssh->setTimeout(300);

        if ($error = $this->login($ssh, $request)) {
            return $error;
        }

        $webUser = $request->filled('web_user') ? $request->web_user : $this->detectWebUser($ssh);
        $zip = escapeshellarg($this->remoteZipPath($order));
        $path = escapeshellarg($deployPath);

        // "user:" = that user plus its login group.
        [$output, $exitCode] = $this->run($ssh,
            $withSudo("unzip -o {$zip} -d {$path}").' 2>&1 && '.
            $withSudo('chown -R '.escapeshellarg($webUser.':')." {$path}").' 2>&1; '.
            "rc=\$?; rm -f {$zip}; exit \$rc"
        );

        if ($exitCode !== 0) {
            return str_contains($output, 'Permission denied')
                ? errorResponse(__('message.deploy_permission_denied', ['path' => $deployPath]))
                : errorResponse(__('message.deploy_failed_generic'));
        }

        $siteUrl = null;
        if ($request->deploy_mode === 'extract_only') {
            $domainCmd =
                'domain=""; '.
                'f=$(grep -rlF '.escapeshellarg('DocumentRoot '.$deployPath).' /etc/apache2/sites-enabled/ 2>/dev/null | head -1); '.
                '[ -n "$f" ] && domain=$(grep -iE "^[[:space:]]*ServerName[[:space:]]+" "$f" 2>/dev/null | head -1 | awk \'{print $2}\'); '.
                'if [ -z "$domain" ]; then '.
                ' f=$(grep -rlF '.escapeshellarg('root '.$deployPath).' /etc/nginx/sites-enabled/ /etc/nginx/conf.d/ 2>/dev/null | head -1); '.
                ' [ -n "$f" ] && domain=$(grep -iE "^[[:space:]]*server_name[[:space:]]+" "$f" 2>/dev/null | grep -v "#" | head -1 | awk \'{print $2}\' | tr -d ";"); '.
                'fi; '.
                'echo "$domain"';

            $detected = trim((string) $ssh->exec($domainCmd));
            if ($detected && ! str_contains($detected, ' ') && str_contains($detected, '.')) {
                $siteUrl = 'http://'.$detected;
            }
        }

        return successResponse(__('message.deploy_extract_completed'), [
            'output' => $output,
            'site_url' => $siteUrl,
        ]);
    }

    /** Deploy hands out the same files as download — see OrderPolicy::deploy. */
    private function deployBlockedReason(Order $order): ?string
    {
        $access = Gate::inspect(OrderAbility::Deploy, $order);

        return $access->allowed() ? null : (string) $access->message();
    }

    /** @return Builder<ProductUpload> */
    private function deployableUploads(Order $order): Builder
    {
        $updateEndsAt = $order->subscription?->update_ends_at;

        return ProductUpload::where('product_id', $order->getRawOriginal('product'))
            ->where('is_private', 0)
            ->whereNotNull('file')
            ->where('file', '!=', '')
            ->when($updateEndsAt, fn ($q) => $q->where('created_at', '<', $updateEndsAt));
    }

    /**
     * Null for hosts that resolve to private/loopback/reserved ranges — customers deploy to their own public servers, never ours.
     * WHITELIST_DEPLOY_IP (config custom.whitelist_deploy_ip) opens specific private IPs/ranges for dev/QA VMs.
     */
    private function publicIp(string $host): ?string
    {
        $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);

        if (filter_var($ip, FILTER_VALIDATE_IP) && IpUtils::checkIp($ip, config('custom.whitelist_deploy_ip', []))) {
            return $ip;
        }

        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) ?: null;
    }

    /**
     * Runs a command and returns [output, exit code].
     * The command prints its own code: some SSH servers never send phpseclib an exit status,
     * so getExitStatus() can be false even when everything worked.
     *
     * @return array{string, int}
     */
    private function run(SSH2 $ssh, string $command): array
    {
        $output = (string) $ssh->exec("( {$command}\n); echo \"__RC__\$?\"");

        if (preg_match('/__RC__(\d+)\s*$/', $output, $m, PREG_OFFSET_CAPTURE)) {
            return [substr($output, 0, $m[0][1]), (int) $m[1][0]];
        }

        // No marker = cut off (timeout or dropped connection) before the command finished.
        return [$output, -1];
    }

    private function login(SSH2 $ssh, Request $request): ?JsonResponse
    {
        if ($ssh->login($request->username, $this->credential)) {
            return null;
        }

        $errors = implode(' | ', $ssh->getErrors() ?: [__('message.deploy_auth_failed_fallback')]);

        return errorResponse(__('message.deploy_ssh_auth_failed', ['errors' => $errors]));
    }

    /** The PHP/web worker user. The deploy dir's owner is no use: verify just created it as the SSH user. */
    private function detectWebUser(SSH2 $ssh): string
    {
        $firstUserRunning = fn (string $pattern): string => trim((string) $ssh->exec(
            "ps -eo user=,comm= | awk '\$1 != \"root\" && \$2 ~ /{$pattern}/ {print \$1; exit}'"
        ));

        $user = $firstUserRunning('php-fpm') ?: $firstUserRunning('^(apache2|httpd|nginx)$');

        return preg_match('/^[a-z_][a-z0-9_.-]*$/i', $user) ? $user : 'www-data';
    }

    /** Derived on the server for both upload and extract — extract never trusts a client-sent path. */
    private function remoteZipPath(Order $order): string
    {
        return '/tmp/faveo_deploy_'.$order->id.'.zip';
    }
}
