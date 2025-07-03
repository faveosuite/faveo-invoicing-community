<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenancy\TenantController;
use App\Model\Common\FaveoCloud;
use App\Model\Common\StatusSetting;
use App\Model\Common\Template;
use App\Model\Common\TemplateType;
use App\Model\Mailjob\ExpiryMailDay;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Payment_log;
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

    public function sendEmail($from, $to, $template_data, $template_name, $replace = [], $type = '', $bcc = [])
    {
        $this->setQueue();
        $job = new \App\Jobs\SendEmail($from, $to, $template_data, $template_name, $replace, $type);
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
            \Log::error($ex->getMessage());
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
                $data->is_deleted=1;
                $data->save();
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
                        $mail->SendEmail($setting->email, $user->email, $template->data, $template->name, $replace, $type);
                    }
                }
            }
        } catch(\Exception $ex) {
            \Log::error($ex->getMessage());
        }
    }

    public function mailing($from, $to, $data, $subject, $replace = [],
         $type = '', $bcc = [], $fromname = '', $toname = '', $cc = [], $attach = [])
    {
        try {
            $transform = [];
            $page_controller = new \App\Http\Controllers\Front\PageController();

            $transform[0] = $replace;
            $data = $page_controller->transform($type, $data, $transform);
            $settings = \App\Model\Common\Setting::find(1);
            $fromname = $settings->from_name;
            if ($subject == 'Contact us' || $subject == 'Requesting a demo for ') {
                if (is_array($transform) && isset($transform[0]['name'])) {
                    $fromname = $transform[0]['name'];
                }
            }
            $temp_id = TemplateType::where('name', $type)->value('id');
            $reply_email_from_db = Template::where('type', $temp_id)->value('reply_to');
            $reply_to = null;
            if (filter_var($reply_email_from_db, FILTER_VALIDATE_EMAIL)) {
                $reply_to = $reply_email_from_db;
            } elseif (isset($replace['reply_email']) && filter_var($replace['reply_email'], FILTER_VALIDATE_EMAIL)) {
                $reply_to = $replace['reply_email'];
            }

            $this->setMailConfig($settings);
            \Mail::send('emails.mail', ['data' => $data], function ($m) use ($from, $to, $subject, $fromname, $toname, $cc, $attach, $bcc, $reply_to) {
                $m->from($from, $fromname);

                $m->to($to, $toname)->subject($subject);
                /* if cc is need  */
                if (! empty($cc)) {
                    foreach ($cc as $address) {
                        $m->cc($address['a ress'], $address['name']);
                    }
                }

                if (! empty($bcc)) {
                    foreach ($bcc as $address) {
                        $m->bcc($address);
                    }
                }

                /*  if attachment is need */
                if (! empty($attach)) {
                    foreach ($attach as $file) {
                        $m->attach($file['path'], $options = []);
                    }
                }
                if (! empty($reply_to)) {
                    $m->replyTo($reply_to, $fromname);
                }
            });
            \DB::table('email_log')->insert([
                'date' => date('Y-m-d H:i:s'),
                'from' => $from,
                'to' => $to,
                'subject' => $subject,
                'body' => $data,
                'status' => 'success',
            ]);

            return 'success';
        } catch (\Exception $ex) {
            \DB::table('email_log')->insert([
                'date' => date('Y-m-d H:i:s'),
                'from' => $from,
                'to' => $to,
                'subject' => $subject,
                'body' => $data,
                'status' => 'failed',
            ]);
            if ($ex instanceof \Swift_TransportException) {
                throw new \Exception($ex->getMessage());
            }

            throw new \Exception($ex->getMessage());
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
}
