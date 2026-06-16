<?php

namespace App\Jobs;

use Session;
use Exception;
use Log;
use App\FailedWhatsappMessage;
use App\WhatsappIntegrationUser;
use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWhatsappMessage implements ShouldQueue
{
    use Queueable;

    public $tries = 1;

    public $retryAfter = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $message)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $client = new Client();
        $urls = Session::has('NonReachableUrls') ? Session::get('NonReachableUrls') : [];
        try {
            $data = json_decode((string) $this->message, true);
            if (isset($data['entry']) && $data['entry'][0]['id'] !== '') {
                //$wabaId = $data['entry'][0]['id'];
                $phoneNumberId = $data['entry'][0]['changes'][0]['value']['metadata']['phone_number_id'];
                $url = WhatsappIntegrationUser::where('phone_number_id', $phoneNumberId)->value('user_callback_url');
                if ($url && ! in_array($url, $urls)) {
                    $response = $client->post($url, [
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
