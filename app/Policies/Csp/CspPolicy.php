<?php

namespace App\Policies\Csp;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;

class CspPolicy implements Preset
{
    public function configure(Policy $policy): void
    {
        $policy
            ->add(Directive::DEFAULT, Keyword::SELF)
            ->add(Directive::SCRIPT, [
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
                'unpkg.com',
                'https://connect.facebook.net',
                'https://www.facebook.com/platform',
            ])
            ->add(Directive::STYLE, [
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
                'unpkg.com',
                'https://www.facebook.com/platform/',
                'https://www.facebook.com/',
            ])
            ->add(Directive::OBJECT, [Keyword::NONE])
            ->add(Directive::BASE, [Keyword::SELF])
            ->add(Directive::CONNECT, [
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
                'https://www.facebook.com/platform/',
                'https://www.facebook.com/',
            ])
            ->add(Directive::FONT, [
                Keyword::SELF,
                'cdnjs.cloudflare.com',
                'code.ionicframework.com',
                'embed.tawk.to',
                'fonts.bunny.net',
                'fonts.gstatic.com',
                'https://www.facebook.com/',
            ])
            ->add(Directive::FRAME, [
                Keyword::SELF,
                'api.razorpay.com',
                'js.stripe.com',
                'www.google.com',
                'www.googletagmanager.com',
                'td.doubleclick.net',
            ])
            ->add(Directive::IMG, [
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
                'a.tile.openstreetmap.org',
                'b.tile.openstreetmap.org',
                'c.tile.openstreetmap.org',
                'unpkg.com',
                'https://images.unsplash.com',
            ])
            ->add(Directive::MANIFEST, [Keyword::SELF])
            ->add(Directive::MEDIA, [
                Keyword::SELF,
                'embed.tawk.to',
            ])
            ->add(Directive::WORKER, [Keyword::SELF])
            ->setReportUri(url(config('csp.report_uri')));
    }
}
