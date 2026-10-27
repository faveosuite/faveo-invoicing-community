<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Front\PageController;
use App\Jobs\SendEmail;
use App\Model\Common\Setting;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\Mailjob\FaveoQueue;
use App\Model\Mailjob\QueueService;
use App\Payment_log;
use App\Traits\QueueTrait;
use Config;
use Crypt;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Log;
use Logger;
use Mail;

class PhpMailController extends Controller
{
    protected CommonMailer $commonMailer;

    protected mixed $queueManager = null;

    public function __construct()
    {
        $this->commonMailer = new CommonMailer;
        $this->queueManager = resolve('queue');
    }

    /**
     * Send email using queue.
     *
     * @param  array<mixed>  $attach
     * @param  array<mixed>  $bcc
     * @param  array<mixed>  $replace
     * @param  array<mixed>  $cc
     */
    public function sendEmail(
        string $from,
        string $to,
        string $template_data,
        string $template_name,
        string $categoryName,
        array $replace = [],
        string $type = '',
        array $bcc = [],
        string $fromname = '',
        string $toname = '',
        array $cc = [],
        array $attach = [],
        bool $autoReply = false
    ): void {
        // Step 1: Log email entry
        /** @var Model $logIdentifier */
        $logIdentifier = Logger::logMailByCategory(
            $from,
            $to,
            $cc,
            $bcc,
            $template_name,
            $this->transformEmailData($template_data, $replace, $type),
            $categoryName
        );

        $this->setQueue();

        // Step 2: Prepare the job
        $job = new SendEmail(
            $from,
            $to,
            $template_data,
            $template_name,
            $replace,
            $type,
            $bcc,
            $fromname,
            $toname,
            $cc,
            $attach,
            $logIdentifier->id,
            $autoReply
        );

        // Step 3: Update log with encrypted job payload
        $logIdentifier->update([
            'job_payload' => Crypt::encrypt(new QueueTrait()->getPayloadData($job)),
        ]);

        // Step 4: Dispatch job only after DB commit
        DB::afterCommit(function () use ($job): void {
            dispatch($job);
        });
    }

    /**
     * set the queue service.
     */
    public function setQueue(): void
    {
        $this->queueManager->setDefaultDriver($this->getActiveQueue()->driver);
    }

    private function getActiveQueue(): mixed
    {
        return persistentCache('queue_configuration', function () {
            $short = 'database';
            $field = [
                'driver' => 'database',
                'table' => 'jobs',
                'queue' => 'default',
                'expire' => 60,
            ];

            $queue = new QueueService;
            $active_queue = $queue->where('status', 1)->first();
            if ($active_queue) {
                $short = $active_queue->short_name;
                $fields = new FaveoQueue;
                $field = $fields->where('service_id', $active_queue->id)->pluck('value', 'key')->toArray();
            }

            return (object) ['driver' => $short, 'config' => $field];
        });
    }

    /**
     * @param  array<mixed>  $attach
     * @param  array<mixed>  $bcc
     * @param  array<mixed>  $cc
     * @param  array<mixed>  $replace
     */
    public function mailing(
        string $from,
        string $to,
        string $data,
        string $subject,
        array $replace = [],
        string $type = '',
        array $bcc = [],
        string $fromname = '',
        string $toname = '',
        array $cc = [],
        array $attach = [],
        int $logIdentifier = 0,
        bool $autoReply = false
    ): string {
        try {
            // Transform data
            $transformedData = $this->transformEmailData($data, $replace, $type);

            // Set up email configuration
            $emailConfig = $this->prepareEmailConfig($replace, $type, $subject, $fromname, $autoReply);

            // Configure mail settings
            /** @var Setting $mailSetting */
            $mailSetting = Setting::first();
            $this->setMailConfig($mailSetting);

            // Send email
            $this->sendMailMessage($from, $to, $subject, $transformedData, $emailConfig, $toname, $cc, $bcc, $attach);

            Logger::outgoingMailSent($logIdentifier);

            return 'success';
        } catch (Exception $exception) {
            Logger::outgoingMailFailed($logIdentifier, $exception);
            throw $exception;
        }
    }

    public function setMailConfig(object $settings): bool|string|null
    {
        switch ($settings->driver) { // @phpstan-ignore property.notFound
            case 'smtp':

                $config = ['host' => $settings->host, // @phpstan-ignore property.notFound
                    'port' => $settings->port, // @phpstan-ignore property.notFound
                    'security' => $settings->encryption, // @phpstan-ignore property.notFound
                    'username' => $settings->email, // @phpstan-ignore property.notFound
                    'password' => $settings->password, // @phpstan-ignore property.notFound
                ];

                $mail = new CommonMailer;
                $mailer = $mail->setSmtpDriver($config);
                if (! $this->commonMailer->setSmtpDriver($config)) {
                    Log::info('Invaid configuration :- '.json_encode($config));

                    return 'invalid mail configuration';
                }

                return $mailer;

            case 'send_mail':
                $config = [
                    'host' => Config::get('mail.host'),
                    'port' => Config::get('mail.port'),
                    'security' => Config::get('mail.encryption'),
                    'username' => Config::get('mail.username'),
                    'password' => Config::get('mail.password'),
                ];
                $this->commonMailer->setSmtpDriver($config);
                break;

            default:
                setServiceConfig($settings);
                break;
        }

        return null;
    }

    public function payment_log(mixed $from, mixed $method, mixed $status, mixed $order, mixed $exception = null, mixed $amount = null, mixed $payment_type = null): void
    {
        $data = [
            'date' => date('Y-m-d H:i:s'),
            'from' => $from,
            'status' => $status,
            'payment_method' => $method,
            'order' => $order,
            'amount' => $amount,
            'payment_type' => $payment_type,
        ];

        if ($exception !== null) {
            $data['exception'] = $exception;
        }

        Payment_log::insert($data);
    }

    /**
     * Prepare email configuration.
     *
     * @param  array<mixed>  $replace
     * @return array<mixed>
     */
    protected function prepareEmailConfig(array $replace, string $type, string $subject, string $fromname, bool $autoReply): array
    {
        $config = [
            'fromname' => $fromname === '' || $fromname === '0' ? Setting::firstOrFail()->from_name : $fromname,
            'reply_to' => null,
            'auto_reply' => $autoReply,
        ];

        // Handle special subjects
        if (in_array($subject, ['Contact us', 'Requesting a demo for '], strict: true) && isset($replace['name'])) {
            $config['fromname'] = $replace['name'];
        }

        // Set reply-to address
        $config['reply_to'] = $this->determineReplyTo($type, $replace);

        return $config;
    }

    /**
     * Determine reply-to address.
     *
     * @param  array<mixed>  $replace
     */
    protected function determineReplyTo(string $type, array $replace): ?string
    {
        $tempId = TemplateType::where('name', $type)->value('id');
        $replyEmailFromDb = Template::where('type', $tempId)->value('reply_to');

        if (filter_var($replyEmailFromDb, FILTER_VALIDATE_EMAIL)) {
            return $replyEmailFromDb;
        }

        if (isset($replace['reply_email']) && filter_var($replace['reply_email'], FILTER_VALIDATE_EMAIL)) {
            return $replace['reply_email'];
        }

        return null;
    }

    /**
     * Transform email data.
     *
     * @param  array<mixed>  $replace
     */
    protected function transformEmailData(string $data, array $replace, string $type): string
    {
        $transform = [$replace];
        $pageController = new PageController;

        return $pageController->transform($type, $data, $transform);
    }

    /**
     * Send mail message.
     *
     * @param  array<mixed>  $attach
     * @param  array<mixed>  $bcc
     * @param  array<mixed>  $config
     * @param  array<mixed>  $cc
     */
    protected function sendMailMessage(
        string $from,
        string $to,
        string $subject,
        string $data,
        array $config,
        string $toname,
        array $cc,
        array $bcc,
        array $attach
    ): void {
        Mail::send('emails.mail', ['data' => $data], function ($message) use (
            $from, $to, $subject, $config, $toname, $cc, $bcc, $attach
        ): void {
            $message->from($from, $config['fromname']);
            $message->to($to, $toname)->subject($subject);

            $this->addCcRecipients($message, $cc);
            $this->addBccRecipients($message, $bcc);
            $this->addAttachments($message, $attach);
            $this->autoReplyHeader($message, $config);

            if (! empty($config['reply_to'])) {
                $message->replyTo($config['reply_to'], $config['fromname']);
            }
        });
    }

    /**
     * @param  array<mixed>  $config
     */
    protected function autoReplyHeader(mixed $message, array $config): void
    {
        if (isset($config['auto_reply']) && $config['auto_reply']) {
            $message->getHeaders()->addTextHeader('X-Autoreply', 'true');
            $message->getHeaders()->addTextHeader('Auto-Submitted', 'auto-replied');
        }
    }

    /**
     * Add CC recipients to message.
     *
     * @param  array<mixed>  $cc
     */
    protected function addCcRecipients(mixed $message, array $cc): void
    {
        foreach ($cc as $address) {
            if (is_array($address)) {
                $message->cc($address['address'], $address['name'] ?? null);
            } else {
                $message->cc($address);
            }
        }
    }

    /**
     * Add BCC recipients to message.
     *
     * @param  array<mixed>  $bcc
     */
    protected function addBccRecipients(mixed $message, array $bcc): void
    {
        foreach ($bcc as $address) {
            if (is_array($address)) {
                $message->bcc($address['address'], $address['name'] ?? null);
            } else {
                $message->bcc($address);
            }
        }
    }

    /**
     * Add attachments to message.
     *
     * @param  array<mixed>  $attach
     */
    protected function addAttachments(mixed $message, array $attach): void
    {
        foreach ($attach as $file) {
            if (is_array($file)) {
                $message->attach($file['path'], $file['options'] ?? []);
            } else {
                $message->attach($file);
            }
        }
    }
}
