<?php

namespace App\Policies\Csp;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policies\Policy;

class CspPolicy extends Policy
{
    public function configure()
    {
        $this
            ->addDirective(Directive::DEFAULT, [Keyword::SELF])
            ->addDirective(Directive::SCRIPT, [
                Keyword::REPORT_SAMPLE,
                Keyword::UNSAFE_INLINE,
                Keyword::UNSAFE_EVAL,
                Keyword::SELF,
                'ajax.googleapis.com',
                'cdn.datatables.net',
                'cdn.jsdelivr.net',
                'cdn.tiny.cloud',
                'cdn.tinymce.com',
                'cdnjs.cloudflare.com',
                'checkout.razorpay.com',
                'code.jquery.com',
                'embed.tawk.to',
                'googleads.g.doubleclick.net',
                'js.stripe.com',
                'maxcdn.bootstrapcdn.com',
                'www.google.com',
                'www.googleadservices.com',
                'www.googletagmanager.com',
                'www.gstatic.com',
            ])
            ->addDirective(Directive::STYLE, [
                Keyword::REPORT_SAMPLE,
                Keyword::UNSAFE_INLINE,
                Keyword::SELF,
                'cdn.datatables.net',
                'cdn.tiny.cloud',
                'cdnjs.cloudflare.com',
                'code.ionicframework.com',
                'embed.tawk.to',
                'fast.fonts.net',
                'fonts.bunny.net',
                'fonts.googleapis.com',
                'stackpath.bootstrapcdn.com',
                'www.tinymce.com',
            ])
            ->addDirective(Directive::OBJECT, ['none'])
            ->addDirective(Directive::BASE, [Keyword::SELF])
            ->addDirective(Directive::CONNECT, [
                Keyword::SELF,
                'embed.tawk.to',
                'google.com',
                'ipapi.co',
                'va.tawk.to',
                'www.google.com',
                'wss://*.tawk.to',
                'analytics.google.com',
                'www.google-analytics.com',
                'www.google.co.in',
                'googleads.g.doubleclick.net',
                'www.googleadservices.com',
                'www.googletagmanager.com',
                'wss://*.tawk.to',
                'stats.g.doubleclick.net',
                'cdn.tiny.cloud',
            ])
            ->addDirective(Directive::FONT, [
                Keyword::SELF,
                'cdnjs.cloudflare.com',
                'code.ionicframework.com',
                'embed.tawk.to',
                'fonts.bunny.net',
                'fonts.gstatic.com',
            ])
            ->addDirective(Directive::FRAME, [
                Keyword::SELF,
                'api.razorpay.com',
                'js.stripe.com',
                'www.google.com',
                'www.googletagmanager.com',
                'td.doubleclick.net',
            ])
            ->addDirective(Directive::IMG, [
                Keyword::SELF,
                'data:',
                'cdn.datatables.net',
                'embed.tawk.to',
                'encrypted-tbn0.gstatic.com',
                'pngimg.com',
                'sp.tinymce.com',
                'static.vecteezy.com',
                'www.google.co.in',
                'www.google.com',
                'www.gravatar.com',
            ])
            ->addDirective(Directive::MANIFEST, [Keyword::SELF])
            ->addDirective(Directive::MEDIA, [
                Keyword::SELF,
                'embed.tawk.to',
            ])
            ->addDirective(Directive::WORKER, [Keyword::SELF])
            ->reportTo(url(config('csp.report_uri')));
    }
}
