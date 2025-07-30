<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenancy\TenantController;
use App\Model\Common\FaveoCloud;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\Mailjob\ExpiryMailDay;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Payment_log;
use App\Traits\QueueTrait;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class PhpMailController extends Controller
{
    protected $commonMailer;

    protected $queueManager;

    public function __construct()
    {
        $this->commonMailer = new CommonMailer();
        $this->queueManager = app('queue');
    }

    /**
     * Send email using queue.
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
        $logIdentifier = \Logger::logMailByCategory($from, $to, $cc, $bcc, $template_name, $template_data, $categoryName);
        $this->setQueue();
        $job = new \App\Jobs\SendEmail($from, $to, $template_data, $template_name, $replace, $type, $bcc, $fromname, $toname, $cc, $attach, $logIdentifier, $autoReply);
        $logIdentifier->update(['job_payload' => (new QueueTrait())->getPayloadData($job)]);
        dispatch($job);
    }

    public function NotifyMail($from, $to, $template_data, $template_name)
    {
        $this->setQueue();
        $job = new \App\Jobs\NotifyMail();
        dispatchNow($job);
    }

    /**
     * set the queue service.
     */
    public function setQueue()
    {
        $this->queueManager->setDefaultDriver($this->getActiveQueue()->driver);
    }

    private function getActiveQueue()
    {
        return persistentCache('queue_configuration', function () {
            $short = 'database';
            $field = [
                'driver' => 'database',
                'table' => 'jobs',
                'queue' => 'default',
                'expire' => 60,
            ];

            $queue = new \App\Model\Mailjob\QueueService();
            $active_queue = $queue->where('status', 1)->first();
            if ($active_queue) {
                $short = $active_queue->short_name;
                $fields = new \App\Model\Mailjob\FaveoQueue();
                $field = $fields->where('service_id', $active_queue->id)->pluck('value', 'key')->toArray();
            }

            return (object) ['driver' => $short, 'config' => $field];
        });
    }

    public function NotifyMailing()
    {
        try {
            $status = StatusSetting::value('cloud_mail_status');
            if ($status == 1) {
                $this->deleteCloudDetails();
            }
        } catch(\Exception $ex) {
            \Logger::exception($ex);
        }
    }

    public function deleteCloudDetails()
    {
        try {
            $contact = getContactData();
            $day = ExpiryMailDay::value('cloud_days');
            $today = Carbon::today();

            $sub = Subscription::whereNotNull('ends_at')
                ->where('is_deleted', 0)
                ->whereIn('product_id', cloudPopupProducts())
                ->whereDate(
                    \DB::raw("DATE_ADD(ends_at, INTERVAL {$day} DAY)"),
                    '<=',
                    $today
                )
                ->get();

            foreach ($sub as $data) {
                $cron = new CronController();
                $user = \DB::table('users')->find($data->user_id);
                $product = Product::find($data->product_id);
                $order = $cron->getOrderById($data->order_id);

                if (empty($order)) {
                    continue;
                }
                $id = \DB::table('installation_details')->where('order_id', $order->id)->value('installation_path');

                if (is_null($id) || $id == cloudCentralDomain()) {
//                    $order->delete();
                    continue;
                } else {
                    //Destroy the tenat
                    $destroy = (new TenantController(new Client, new FaveoCloud()))->destroyTenant(new Request(['id' => $id]));

                    //Mail Sending

                    if ($destroy->status() == 200) {
                        $data->is_deleted = 1;
                        $data->save();
                        //check in the settings
                        $settings = new \App\Model\Common\Setting();
                        $setting = $settings::find(1);

                        //template
                        $template = new \App\Model\Common\Template();
                        $temp_id = \DB::table('template_types')->where('name', 'cloud_deleted')->value('id');
                        $template = $template->where('id', $temp_id)->first();

                        $mail = new \App\Http\Controllers\Common\PhpMailController();
                        $type = '';
                        $replace = ['name' => $user->first_name.' '.$user->last_name,
                            'product' => $product->name,
                            'number' => $order->number,
                            'expiry' => date('j M Y', strtotime($data->update_ends_at)),
                            'contact' => $contact['contact'],
                            'logo' => $contact['logo'],
                            'reply_email' => $setting->company_email,
                        ];
                        if ($template) {
                            $type_id = $template->type;
                            $temp_type = new \App\Model\Common\TemplateType();
                            $type = $temp_type->where('id', $type_id)->first()->name;
                        }
                        $mail->SendEmail($setting->email, $user->email, $template->data, $template->name, 'cloud-delete', $replace, $type);
                    }
                }
            }
        } catch(\Exception $ex) {
            \Logger::exception($ex);
        }
    }

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
        int $logIdentifier,
        bool $autoReply = false
    ): string {
        try {
            // Transform data
            $transformedData = $this->transformEmailData($data, $replace, $type);

            // Set up email configuration
            $emailConfig = $this->prepareEmailConfig($replace, $type, $subject, $fromname, $autoReply);

            // Configure mail settings
            $this->setMailConfig(Setting::first());

            // Send email
            $this->sendMailMessage($from, $to, $subject, $transformedData, $emailConfig, $toname, $cc, $bcc, $attach);

            \Logger::outgoingMailSent($logIdentifier);

            return 'success';
        } catch (\Exception $ex) {
            \Logger::outgoingMailFailed($logIdentifier, $ex);
            throw $ex;
        }
    }

    public function setMailConfig($settings)
    {
        switch ($settings->driver) {
            case 'smtp':

                $config = ['host' => $settings->host,
                    'port' => $settings->port,
                    'security' => $settings->encryption,
                    'username' => $settings->email,
                    'password' => $settings->password,
                ];

                $mail = new \App\Http\Controllers\Common\CommonMailer();
                $mailer = $mail->setSmtpDriver($config);
                if (! $this->commonMailer->setSmtpDriver($config)) {
                    \Log::info('Invaid configuration :- '.$config);

                    return 'invalid mail configuration';
                }

                return $mailer;
                break;

            case 'send_mail':
                $config = [
                    'host' => \Config::get('mail.host'),
                    'port' => \Config::get('mail.port'),
                    'security' => \Config::get('mail.encryption'),
                    'username' => \Config::get('mail.username'),
                    'password' => \Config::get('mail.password'),
                ];
                $this->commonMailer->setSmtpDriver($config);
                break;

            default:
                setServiceConfig($settings);
                break;
        }
    }

    public function payment_log($from, $method, $status, $order, $exception = null, $amount = null, $payment_type = null)
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
     */
    protected function prepareEmailConfig(array $replace, string $type, string $subject, string $fromname, bool $autoReply): array
    {
        $config = [
            'fromname' => ! empty($fromname) ? $fromname : Setting::first()->from_name,
            'reply_to' => null,
            'auto_reply' => $autoReply,
        ];

        // Handle special subjects
        if (in_array($subject, ['Contact us', 'Requesting a demo for ']) && isset($replace['name'])) {
            $config['fromname'] = $replace['name'];
        }

        // Set reply-to address
        $config['reply_to'] = $this->determineReplyTo($type, $replace);

        return $config;
    }

    /**
     * Determine reply-to address.
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
     */
    protected function transformEmailData(string $data, array $replace, string $type): string
    {
        $transform = [$replace];
        $pageController = new \App\Http\Controllers\Front\PageController();

        return $pageController->transform($type, $data, $transform);
    }

    /**
     * Send mail message.
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
        \Mail::send('emails.mail', ['data' => $data], function ($message) use (
            $from, $to, $subject, $config, $toname, $cc, $bcc, $attach
        ) {
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

    protected function autoReplyHeader($message, array $config): void
    {
        if (isset($config['auto_reply']) && $config['auto_reply']) {
            $message->getHeaders()->addTextHeader('X-Autoreply', 'true');
            $message->getHeaders()->addTextHeader('Auto-Submitted', 'auto-replied');
        }
    }

    /**
     * Add CC recipients to message.
     */
    protected function addCcRecipients($message, array $cc): void
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
     */
    protected function addBccRecipients($message, array $bcc): void
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
     */
    protected function addAttachments($message, array $attach): void
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
