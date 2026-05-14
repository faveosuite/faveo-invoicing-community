<?php

namespace App\Http\Controllers\Front;

use App\Facades\Attach;
use App\Model\Order\Order;
use App\Model\Product\ProductUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;
use phpseclib3\Net\SSH2;

class DeployController extends BaseClientController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Return latest version for the order (used to display version label in UI).
     */
    public function getVersions($orderId)
    {
        try {
            $order = Order::findOrFail($orderId);

            if (! authorizeOwnership($order->client)) {
                return errorResponse(__('message.unauthorized_action'), 403);
            }

            $versions = ProductUpload::where('product_id', $order->getRawOriginal('product'))
                ->where('is_private', 0)
                ->whereNotNull('file')
                ->where('file', '!=', '')
                ->select('id', 'version', 'title', 'file')
                ->latest()
                ->get();

            return successResponse('', $versions->toArray());
        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Step-based deployment entry point.
     * Each AJAX call handles one step and returns immediately,
     * allowing the frontend to show live progress.
     *
     * Steps:
     *   verify  → SSH auth + path preparation
     *   install → run install.sh (fresh_install only)
     *   upload  → SFTP upload zip to /tmp on remote
     *   extract → unzip + chown on remote
     */
    public function deployStep(Request $request): JsonResponse
    {
        $request->validate([
            'step' => 'required|in:verify,install,upload,extract',
            'order_id' => 'required|integer',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string|max:255',
            'auth_method' => 'required|in:private_key,password',
            'private_key' => 'nullable|string|required_if:auth_method,private_key',
            'password' => 'nullable|string|required_if:auth_method,password',
            'deploy_mode' => 'required|in:fresh_install,extract_only',
            'deploy_path' => 'nullable|string|max:1000|required_if:deploy_mode,extract_only',
            'install_domain' => 'nullable|string|max:255',
            'install_email' => 'nullable|email|max:255',
            'install_license' => 'nullable|string|size:16',
            'install_order' => 'nullable|string|size:8',
            'web_server' => 'nullable|in:1,2',
            'ssl_type' => 'nullable|in:A,B,C',
            'ssl_cert_path' => 'nullable|string|max:500',
            'ssl_key_path' => 'nullable|string|max:500',
            'web_user' => 'nullable|string|max:64',
            'sudo_password' => 'nullable|string',
            'version_id' => 'nullable|integer',
            'remote_path' => 'nullable|string|max:500', // passed from upload → extract
        ]);

        try {
            $credential = $this->resolveCredential($request);
            $deployPath = $request->deploy_mode === 'fresh_install'
                ? '/var/www/faveo'
                : rtrim($request->deploy_path, '/');

            switch ($request->step) {
                case 'verify':  return $this->stepVerify($request, $credential, $deployPath);
                case 'install': return $this->stepInstall($request, $credential);
                case 'upload':  return $this->stepUpload($request, $credential);
                case 'extract': return $this->stepExtract($request, $credential, $deployPath);
            }
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Step: verify — test SSH auth and prepare deploy path
    // ──────────────────────────────────────────────────────────────────────────

    private function stepVerify(Request $request, $credential, string $deployPath): JsonResponse
    {
        $ssh = new SSH2($request->host, (int) $request->port);

        if (! $ssh->login($request->username, $credential)) {
            $errors = implode(' | ', $ssh->getErrors() ?: [__('message.deploy_auth_failed_fallback')]);

            return errorResponse(__('message.deploy_ssh_auth_failed', ['errors' => $errors]));
        }

        if ($request->deploy_mode === 'extract_only') {
            $result = trim($ssh->exec(
                'mkdir -p '.escapeshellarg($deployPath).' 2>&1 && echo "ok" || echo "failed"'
            ));

            if (str_contains($result, 'failed')) {
                return errorResponse(__('message.deploy_path_create_failed', ['path' => $deployPath]));
            }
        }

        return successResponse(__('message.deploy_ssh_verified'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Step: install — download and run install.sh (fresh_install only)
    // ──────────────────────────────────────────────────────────────────────────

    private function stepInstall(Request $request, $credential): JsonResponse
    {
        $ssh = new SSH2($request->host, (int) $request->port);
        $ssh->setTimeout(1800); // 30 minutes

        if (! $ssh->login($request->username, $credential)) {
            $errors = implode(' | ', $ssh->getErrors() ?: [__('message.deploy_auth_failed_fallback')]);

            return errorResponse(__('message.deploy_ssh_auth_failed', ['errors' => $errors]));
        }

        $installScript = \App\Model\Common\Setting::value('help_support_url');

        if (empty($installScript)) {
            return errorResponse(__('message.deploy_install_script_not_configured'), 422);
        }
        $license = $request->install_license;
        $order = $request->install_order;

        // read -n 16 and read -n 8 consume fixed character counts from stdin.
        // license+order must be concatenated without a newline between them so each
        // fixed-width read gets exactly its N chars; the trailing 'y\n' is then
        // consumed by the confirmation prompt.
        if ($request->ssl_type === 'C') {
            $cmd = sprintf(
                'wget -q -O /tmp/faveo_install.sh %s && chmod +x /tmp/faveo_install.sh && '.
                'printf "%%s\n%%s\n%%sy\n%%s\nC\n%%s\n%%s\n" %s %s %s %s %s %s | bash /tmp/faveo_install.sh 2>&1; '.
                'rm -f /tmp/faveo_install.sh',
                escapeshellarg($installScript),
                escapeshellarg($request->install_domain),
                escapeshellarg($request->install_email),
                escapeshellarg($license.$order),
                escapeshellarg($request->web_server),
                escapeshellarg($request->ssl_cert_path),
                escapeshellarg($request->ssl_key_path)
            );
        } else {
            $cmd = sprintf(
                'wget -q -O /tmp/faveo_install.sh %s && chmod +x /tmp/faveo_install.sh && '.
                'printf "%%s\n%%s\n%%sy\n%%s\n%%s\n" %s %s %s %s %s | bash /tmp/faveo_install.sh 2>&1; '.
                'rm -f /tmp/faveo_install.sh',
                escapeshellarg($installScript),
                escapeshellarg($request->install_domain),
                escapeshellarg($request->install_email),
                escapeshellarg($license.$order),
                escapeshellarg($request->web_server),
                escapeshellarg($request->ssl_type)
            );
        }

        $output = $ssh->exec($cmd);

        if (str_contains($output, 'error') || str_contains($output, 'failed')) {
            return errorResponse(__('message.deploy_install_script_errors'));
        }

        $credentials = trim($ssh->exec(
            'cat ~/credentials.txt 2>/dev/null || cat /root/credentials.txt 2>/dev/null || echo ""'
        ));

        return successResponse(__('message.deploy_install_completed'), [
            'credentials' => $credentials,
            'setup_url' => 'http://'.$request->install_domain.'/',
            'output' => $output,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Step: upload — stream product zip to /tmp on remote server via SFTP
    // ──────────────────────────────────────────────────────────────────────────

    private function stepUpload(Request $request, $credential): JsonResponse
    {
        $order = Order::findOrFail($request->order_id);

        if (! authorizeOwnership($order->client)) {
            return errorResponse(__('message.unauthorized_action'), 403);
        }

        $query = ProductUpload::where('product_id', $order->getRawOriginal('product'))
            ->where('is_private', 0)
            ->whereNotNull('file')
            ->where('file', '!=', '');

        $upload = $request->filled('version_id')
            ? $query->findOrFail($request->version_id)
            : $query->latest()->firstOrFail();

        if (empty($upload->file)) {
            return errorResponse(__('message.deploy_no_file_attached'));
        }

        $filePath = 'products/'.$upload->file;

        if (! Attach::exists($filePath)) {
            return errorResponse(__('message.deploy_file_not_found'));
        }

        // Write to local temp file
        $stream = Attach::readStream($filePath);
        $tmpFile = tempnam(sys_get_temp_dir(), 'deploy_');
        $tmpHandle = fopen($tmpFile, 'wb');
        stream_copy_to_stream($stream, $tmpHandle);
        fclose($tmpHandle);
        if (is_resource($stream)) {
            fclose($stream);
        }

        $remotePath = '/tmp/'.basename($upload->file);

        $sftp = new SFTP($request->host, (int) $request->port);
        if (! $sftp->login($request->username, $credential)) {
            @unlink($tmpFile);

            return errorResponse(__('message.deploy_sftp_failed'));
        }

        if (! $sftp->put($remotePath, $tmpFile, SFTP::SOURCE_LOCAL_FILE)) {
            @unlink($tmpFile);

            return errorResponse(__('message.deploy_upload_failed'));
        }

        @unlink($tmpFile);

        return successResponse(__('message.deploy_file_uploaded'), [
            'remote_path' => $remotePath,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Step: extract — unzip + set ownership on remote server
    // ──────────────────────────────────────────────────────────────────────────

    private function stepExtract(Request $request, $credential, string $deployPath): JsonResponse
    {
        $remotePath = $request->remote_path;
        $sudoPassword = $request->filled('sudo_password') ? $request->sudo_password : null;

        $withSudo = fn (string $cmd) => $sudoPassword
            ? 'echo '.escapeshellarg($sudoPassword).' | sudo -S '.$cmd
            : $cmd;

        // Detect web user if not provided
        $ssh = new SSH2($request->host, (int) $request->port);
        if (! $ssh->login($request->username, $credential)) {
            $errors = implode(' | ', $ssh->getErrors() ?: [__('message.deploy_auth_failed_fallback')]);

            return errorResponse(__('message.deploy_ssh_auth_failed', ['errors' => $errors]));
        }

        if ($request->filled('web_user')) {
            $webUser = $request->web_user;
        } else {
            $detected = trim($ssh->exec('stat -c %U '.escapeshellarg($deployPath).' 2>/dev/null'));
            $webUser = ($detected && ! str_contains($detected, 'stat:')) ? $detected : 'www-data';
        }

        // New connection for the extraction exec (phpseclib3 single exec per connection)
        $ssh2 = new SSH2($request->host, (int) $request->port);
        $ssh2->setTimeout(300);
        $ssh2->login($request->username, $credential);

        $output = $ssh2->exec(
            $withSudo('unzip -o '.escapeshellarg($remotePath).' -d '.escapeshellarg($deployPath)).' 2>&1;'.
            'rm -f '.escapeshellarg($remotePath).';'.
            $withSudo('chown -R '.escapeshellarg($webUser).':'.escapeshellarg($webUser).' '.escapeshellarg($deployPath))
        );

        if (str_contains($output, 'Permission denied')) {
            return errorResponse(__('message.deploy_permission_denied', ['path' => $deployPath]));
        }

        // Detect domain mapped to deploy path from Apache/Nginx vhost configs
        $siteUrl = null;
        if ($request->deploy_mode === 'extract_only') {
            $ssh3 = new SSH2($request->host, (int) $request->port);
            $ssh3->login($request->username, $credential);

            $domainCmd =
                'domain=""; '.
                'f=$(grep -rl "DocumentRoot '.$deployPath.'" /etc/apache2/sites-enabled/ 2>/dev/null | head -1); '.
                '[ -n "$f" ] && domain=$(grep -iE "^[[:space:]]*ServerName[[:space:]]+" "$f" 2>/dev/null | head -1 | awk \'{print $2}\'); '.
                'if [ -z "$domain" ]; then '.
                '  f=$(grep -rl "root '.$deployPath.'" /etc/nginx/sites-enabled/ /etc/nginx/conf.d/ 2>/dev/null | head -1); '.
                '  [ -n "$f" ] && domain=$(grep -iE "^[[:space:]]*server_name[[:space:]]+" "$f" 2>/dev/null | grep -v "#" | head -1 | awk \'{print $2}\' | tr -d ";"); '.
                'fi; '.
                'echo "$domain"';

            $detected = trim($ssh3->exec($domainCmd));
            if ($detected && ! str_contains($detected, ' ') && str_contains($detected, '.')) {
                $siteUrl = 'http://'.$detected;
            }
        }

        return successResponse(__('message.deploy_extract_completed'), [
            'output' => $output,
            'site_url' => $siteUrl,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helper
    // ──────────────────────────────────────────────────────────────────────────

    private function resolveCredential(Request $request)
    {
        if ($request->auth_method === 'private_key') {
            try {
                return PublicKeyLoader::load(trim($request->private_key));
            } catch (\Exception $e) {
                throw new \Exception(__('message.deploy_invalid_private_key', ['error' => $e->getMessage()]));
            }
        }

        return $request->password;
    }
}
