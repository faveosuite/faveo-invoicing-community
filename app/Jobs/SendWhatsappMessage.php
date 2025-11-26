<?php

namespace App\Jobs;

use App\FailedWhatsappMessage;
use App\WhatsappIntegrationUser;
use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWhatsappMessage implements ShouldQueue
{
    use Queueable;

    public $connection='custom_db';
    protected $message;
    protected $client;
    /**
     * Create a new job instance.
     */
    public function __construct($message)
    {
        $this->message = $message;
        $this->client= new Client();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $data = json_decode($this->message, true);
            if (isset($data['entry']) && $data['entry'][0]['id'] !== '') {
                $wabaId = $data['entry'][0]['id'];
                $url = WhatsappIntegrationUser::where('waba_id', $wabaId)->value('user_callback_url');
                $response = $this->client->post($url, [
                    'body' => $this->message,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                ]);
            }
        }catch (\Exception $exception){
            \Log::error('Whatsapp Message Failure: '.$exception->getMessage());
        }
    }

    public function failed(): void
    {
        FailedWhatsappMessage::create(['message'=>$this->message]);
    }
}
