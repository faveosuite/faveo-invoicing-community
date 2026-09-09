<?php

namespace App\Policies\Csp;

use Illuminate\Support\Facades\Vite;
use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;

class CspPolicy implements Preset
{
    public function configure(Policy $policy): void
    {
        if (Vite::isRunningHot()) {
            return;
        }

        $policy
            ->add(Directive::DEFAULT, Keyword::SELF)
            ->add(Directive::SCRIPT, [
                Keyword::REPORT_SAMPLE,
                Keyword::UNSAFE_INLINE,
                Keyword::UNSAFE_EVAL,
                Keyword::SELF,
                'cdn.jsdelivr.net',
                'cdnjs.cloudflare.com',
                'checkout.razorpay.com',
                'embed.tawk.to',
                'googleads.g.doubleclick.net',
                'js.stripe.com',
                'www.google.com',
                'www.googleadservices.com',
                'www.googletagmanager.com',
                'www.gstatic.com',
                'unpkg.com',
                'https://cdn.jsdelivr.net',
                'https://connect.facebook.net',
            ])
            ->add(Directive::STYLE, [
                Keyword::REPORT_SAMPLE,
                Keyword::UNSAFE_INLINE,
                Keyword::SELF,
                'cdnjs.cloudflare.com',
                'embed.tawk.to',
                'fast.fonts.net',
                'fonts.bunny.net',
                'fonts.googleapis.com',
                'unpkg.com',
                'https://cdn.jsdelivr.net',
            ])
            ->add(Directive::OBJECT, [Keyword::NONE])
            ->add(Directive::BASE, [Keyword::SELF])
            ->add(Directive::CONNECT, [
                Keyword::SELF,
                'api.razorpay.com',
                'checkout.razorpay.com',
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
                'stats.g.doubleclick.net',
            ])
            ->add(Directive::FONT, [
                Keyword::SELF,
                'cdnjs.cloudflare.com',
                'embed.tawk.to',
                'fonts.bunny.net',
                'fonts.gstatic.com',
            ])
            ->add(Directive::FRAME, [
                Keyword::SELF,
                'api.razorpay.com',
                'embed.tawk.to',
                'js.stripe.com',
                'www.google.com',
                'www.googletagmanager.com',
                'td.doubleclick.net',
            ])
            ->add(Directive::IMG, [
                Keyword::SELF,
                'data:',
                'cdnjs.cloudflare.com',
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
            ->setReportUri((string) url(config('csp.report_uri'))); // @phpstan-ignore cast.string
    }
}
