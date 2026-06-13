<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\NewsletterManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function __construct(private readonly NewsletterManager $manager) {}

    public function subscribe(Request $request): JsonResponse
    {
        $request->validate(['newsletterEmail' => 'required|email']);

        $email = $request->input('newsletterEmail');

        if (! $this->manager->hasEnabledProviders()) {
            return errorResponse(__('message.no_newsletter_provider_enabled'));
        }

        $this->manager->subscribeAll($email);

        return successResponse(__('message.subscribed_to_newsletter'));
    }
}
