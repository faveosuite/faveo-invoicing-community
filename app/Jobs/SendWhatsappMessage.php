<?php

namespace App\Jobs;

use App\FailedWhatsappMessage;
use App\WhatsappIntegrationUser;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Log;
use Session;

class SendWhatsappMessage implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(protected mixed $message)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $client = new Client;
        $urls = Session::has('NonReachableUrls') ? Session::get('NonReachableUrls') : [];
        try {
            $data = json_decode((string) $this->message, associative: true);
            if (isset($data['entry']) && $data['entry'][0]['id'] !== '') {
                $phoneNumberId = $data['entry'][0]['changes'][0]['value']['metadata']['phone_number_id'];
                $url = WhatsappIntegrationUser::where('phone_number_id', $phoneNumberId)->value('user_callback_url');
                if ($url && ! in_array($url, $urls)) {
                    $client->post($url, [
                        'body' => $this->message,
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ],
                    ]);
                }
            }
        } catch (Exception $exception) {
            $urls[] = $url;
            Session::put('NonReachableUrls', $urls);
            Log::error('Whatsapp Message Failure: '.$exception->getMessage());
        }
    }

    public function failed(): void
    {
        FailedWhatsappMessage::create(['message' => $this->message]);
        $this->delete();
    }
}
