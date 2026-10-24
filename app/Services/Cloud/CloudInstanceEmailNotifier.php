<?php

namespace App\Services\Cloud;

use App\Http\Controllers\Common\PhpMailController;
use App\Model\Common\Setting;
use App\Model\Mailjob\CloudEmail;
use Exception;
use GuzzleHttp\Client;
use Logger;

/**
 * Emails a new cloud customer their login once their custom domain answers over HTTPS.
 * Pending emails are rows in cloud_email; a row is removed once its email is sent.
 * Run every five minutes by `cloud:send-instance-emails`.
 */
class CloudInstanceEmailNotifier
{
    /** Seconds before giving up on a domain, so one hanging domain cannot stall the scheduler. */
    private const int REQUEST_TIMEOUT = 10;

    /** Alert on Google Chat when a domain is still unreachable after this many checks. */
    private const int ALERT_AFTER_FAILED_CHECKS = 30;

    private readonly Client $http;

    public function __construct()
    {
        $this->http = new Client(['timeout' => self::REQUEST_TIMEOUT, 'connect_timeout' => self::REQUEST_TIMEOUT]);
    }

    public function __invoke(): void
    {
        try {
            /** @var Setting $setting */
            $setting = Setting::find(1);
            $mail = new PhpMailController;

            foreach (CloudEmail::cursor() as $cloud) {
                if (! $this->domainIsLive($cloud)) {
                    continue;
                }

                $userData = $cloud->result_message.'.<br> Email:'.' '.$cloud->user.'<br>'.'Password:'.' '.$cloud->result_password;
                $mail->SendEmail($setting->email, $cloud->user, $userData, 'New instance created', 'cloud-instance-created');
                CloudEmail::where('domain', $cloud->domain)->delete();
            }
        } catch (Exception $exception) {
            $this->notifyGoogleChat($exception->getMessage());
        }
    }

    private function domainIsLive(CloudEmail $cloud): bool
    {
        try {
            $status = $this->http->get('https://'.$cloud->domain)->getStatusCode();
            $live = $status >= 200 && $status < 300;
        } catch (Exception) {
            // Not reachable yet, or its SSL certificate is not valid yet.
            $live = false;
        }

        if ($live) {
            $this->notifyGoogleChat('Hello, It has come to my notice that this domain has been created successfully Domain name:'.$cloud->domain.' and this is their email: '.$cloud->user."\u{2705}\u{2705}\u{2705}");

            return true;
        }

        CloudEmail::where('domain', $cloud->domain)->increment('counter');
        if ($cloud->counter == self::ALERT_AFTER_FAILED_CHECKS) {
            $this->notifyGoogleChat('Hello, It has come to my notice that this domain has not been created successfully Domain name:'.$cloud->domain.' and this is their email: '.$cloud->user.'&#10060;'."\u{2716}\u{2716}\u{2716}");
        }

        return false;
    }

    /** Best effort: a chat outage is logged, never allowed to stop the emails. */
    private function notifyGoogleChat(string $text): void
    {
        $url = config('custom.google_chat');
        if (! $url) {
            return;
        }

        try {
            $this->http->post($url, [
                'headers' => ['Content-Type' => 'application/json; charset=UTF-8'],
                'body' => json_encode(['text' => $text]),
            ]);
        } catch (Exception $exception) {
            Logger::exception($exception);
        }
    }
}
