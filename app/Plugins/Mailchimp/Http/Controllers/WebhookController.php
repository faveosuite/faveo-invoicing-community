<?php

namespace App\Plugins\Mailchimp\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;
use Logger;
use Throwable;

class WebhookController extends Controller
{
    /**
     * Mailchimp sends a GET to verify the webhook URL is reachable.
     */
    public function verify(): Response
    {
        return response('', 200);
    }

    /**
     * Handle incoming Mailchimp webhook events.
     *
     * Supported events:
     *  - unsubscribe  → mark member as unsubscribed in Mailchimp (already done by Mailchimp, log it)
     *  - cleaned      → bounce/spam, member removed by Mailchimp
     */
    public function handle(Request $request): Response
    {
        $type = $request->input('type');
        $email = $request->input('data.email');

        if (! $email) {
            return response('', 200);
        }

        try {
            match ($type) {
                'unsubscribe', 'cleaned' => $this->handleUnsubscribe($email),
                default => null,
            };
        } catch (Throwable $throwable) {
            Logger::exception($throwable);
        }

        return response('', 200);
    }

    private function handleUnsubscribe(string $email): void
    {
        // Log the event — the user is already unsubscribed in Mailchimp's side.
        // Extend here if you want to update a local flag on the User model.
        Log::info('Mailchimp unsubscribe webhook received for: '.$email);
    }
}
