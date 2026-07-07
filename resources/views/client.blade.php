<?php
$set = \App\Model\Common\Setting::findOrFail(1);
$rtl = isRtlForLang();
$cloudBtn = \App\Model\Common\StatusSetting::where('id', 1)->value('cloud_button');
$demoPage = App\Demo_page::first();

$cartCount = resolve(\App\Http\Controllers\Front\Cart\CartService::class)->resolveCart(request())->itemCount();

$social = App\Model\Common\SocialMedia::get(['name', 'link']);

$widgets = \App\Model\Front\Widgets::where('publish', 1)->get(['id', 'name', 'type', 'content', 'allow_mailchimp', 'allow_social_media', 'allow_tweets']);
$chatScripts = \App\Model\Common\ChatScript::get(['id', 'script', 'google_analytics', 'google_analytics_tag', 'on_registration', 'on_every_page']);

$languageList = array_map('basename', \Illuminate\Support\Facades\File::directories(lang_path()));
$dbLanguages = \App\Model\Common\Language::all()->keyBy('locale');
$languages = collect($languageList)->map(function (string $locale, $key) use ($dbLanguages): array {
    $config = config('languages.' . $locale, ['', '']);
    return [
        'id'          => $key,
        'locale'      => $locale,
        'name'        => $config[0] ?? $locale,
        'translation' => $config[1] ?? '',
        'status'      => $dbLanguages[$locale]->status ?? 0,
    ];
})->sortBy('name')->values();

$publishedPages = \App\Model\Front\FrontendPage::where('publish', 1)
    ->select('id', 'name', 'slug', 'url', 'type', 'parent_page_id')
    ->orderBy('created_at', 'asc')
    ->get();

$productGroups = \App\Model\Product\ProductGroup::select('id', 'name')
    ->where('hidden', '!=', 1)
    ->get()
    ->mapWithKeys(fn($g): array => [$g->id => [
        'name' => $g->name,
    ]]);

$seo = app(\App\Services\Seo\SeoMetaService::class)->resolve(request()->path());
$seoFormatter = app(\App\Services\Seo\SeoTemplateFormatter::class);

// Guest auth pages (login/forgot/reset) render via the client-side router,
// not their own Blade view — ship all 3 rows so the SPA can keep its title
// in sync with the admin-configured SEO text when navigating between them
// without a full page reload, instead of using a stale hardcoded title.
// {name}/{company} shortcodes are resolved here too, same as the hard-load
// path (SeoMetaService::fromDefaultPage), so SPA nav doesn't leak raw
// placeholder text.
$defaultPagesSeo = \App\Model\Common\SeoDefaultPage::whereIn('page_key', ['login', 'forgot_password', 'reset_password'])
    ->get(['page_key', 'meta_title', 'meta_description'])
    ->keyBy('page_key')
    ->map(function ($row) use ($seoFormatter): array {
        $name = ucwords(str_replace('_', ' ', $row->page_key));

        return [
            'meta_title' => $seoFormatter->resolveShortcodes($row->meta_title, $name),
            'meta_description' => $seoFormatter->resolveShortcodes($row->meta_description, $name),
        ];
    });

// Same General Description used by SeoMetaService's fallback() for
// authenticated/unknown routes — shipped down so clientRouter.js's
// afterEach can keep the client-rendered <meta name="description"> in
// sync with it on SPA navigation, instead of a stale per-route string.
$generalDescription = $seoFormatter->generalDescription()
    ?: 'Manage your billing, invoices, and subscriptions online.';
?>
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $seo['title'] }}</title>
    <meta name="description" content="{{ $seo['description'] }}">
    <meta name="robots" content="{{ $seo['robots'] }}">
    <link rel="canonical" href="{{ $seo['canonical'] }}">
    <meta property="og:title" content="{{ $seo['og_title'] }}">
    <meta property="og:description" content="{{ $seo['og_description'] }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $seo['canonical'] }}">
    @if(!empty($seo['image']))
    <meta property="og:image" content="{{ $seo['image'] }}">
    @endif

    @if($set->fav_icon)
        <link rel="shortcut icon" href="{{ $set->fav_icon }}" type="image/x-icon">
    @endif

    {{-- Google Fonts --}} {{-- NOSONAR --}}
    <link id="googleFonts"
          href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800%7CShadows+Into+Light&display=swap"
          rel="stylesheet">

    {{-- Porto vendor CSS --}}
    <link rel="stylesheet" href="{{ assetLink('css', 'porto-animate') }}">
    <link rel="stylesheet" href="{{ assetLink('css', 'porto-simple-icons') }}">
    <link rel="stylesheet" href="{{ assetLink('css', 'porto-owl') }}">
    <link rel="stylesheet" href="{{ assetLink('css', 'porto-owl-theme') }}">
    <link rel="stylesheet" href="{{ assetLink('css', 'porto-magnific') }}">

    {{-- Reuse common vendors (same as admin panel) --}}
    <link rel="stylesheet" href="{{ assetLink('css', $rtl ? 'bootstrap-rtl' : 'bootstrap') }}">
    <link rel="stylesheet" href="{{ assetLink('css', 'fontawesome') }}">
    <link rel="stylesheet" href="{{ assetLink('css', $rtl ? 'porto-theme-rtl' : 'porto-theme') }}">
    <link rel="stylesheet" href="{{ assetLink('css', $rtl ? 'porto-elements-rtl' : 'porto-elements') }}">
    <link rel="stylesheet" href="{{ assetLink('css', $rtl ? 'porto-blog-rtl' : 'porto-blog') }}">
    <link rel="stylesheet" href="{{ assetLink('css', $rtl ? 'porto-shop-rtl' : 'porto-shop') }}">
    <link rel="stylesheet" href="{{ assetLink('css', 'porto-skin') }}">
    <link rel="stylesheet" href="{{ assetLink('css', 'porto-custom') }}">

    <script src="{{ url('js/lang') }}"></script>

    {{-- Vue client SPA — Vite injects its CSS at build time --}}
    @vite(['resources/assets/js/client.js'])
</head>

<body data-plugin-scroll-offset="85">

<div id="app-client"
     data-theme="porto"
     data-authenticated="{{ auth()->check() ? 'true' : 'false' }}"
     data-base-url="{{ url('/') }}"
     data-client-url="{{ url('/') }}"
     data-asset-url="{{ asset('') }}"
     data-locale="{{ app()->getLocale() }}"
     data-locale-rtl="{{ $rtl ? 'true' : 'false' }}"
     data-page-title="{{ $set->favicon_title_client }}"
     data-app-logo="{{ $set->logo }}"
     data-company="{{ $set->company }}"
     data-website="{{ $set->website }}"
     data-user-id="{{ auth()->user()?->id ?? '' }}"
     data-user-first-name="{{ auth()->user()?->first_name ?? '' }}"
     data-user-last-name="{{ auth()->user()?->last_name ?? '' }}"
     data-user-name="{{ auth()->user() ? auth()->user()->first_name . ' ' . auth()->user()->last_name : '' }}"
     data-user-username="{{ auth()->user()?->user_name ?? '' }}"
     data-user-email="{{ auth()->user()?->email ?? '' }}"
     data-user-avatar="{{ auth()->user()?->profile_pic ?? '' }}"
     data-user-role="{{ auth()->user()?->role ?? '' }}"
     data-user-timezone="{{ auth()->user()?->timezone?->name ?? '' }}"
     data-phone="{{ $set->phone ?? '' }}"
     data-phone-code="{{ $set->phone_code ?? '' }}"
     data-company-email="{{ $set->company_email ?? '' }}"
     data-cloud="{{ ($cloudBtn == 1) ? 'true' : 'false' }}"
     data-demo="{{ ($demoPage && $demoPage->status) ? 'true' : 'false' }}"
     data-cart-count="{{ $cartCount }}"
     data-social="{{ $social->toJson() }}"
     data-widgets="{{ $widgets->toJson() }}"
     data-scripts="{{ $chatScripts->toJson() }}"
     data-languages="{{ $languages->toJson() }}"
     data-published-pages="{{ $publishedPages->toJson() }}"
     data-product-groups="{{ $productGroups->toJson() }}"
     data-default-pages-seo="{{ $defaultPagesSeo->toJson() }}"
     data-general-description="{{ $generalDescription }}">
</div>

{{-- Bootstrap 5 bundle JS (includes Popper) — used for dropdowns, collapse, tooltips. --}}
<script src="{{ assetLink('js', 'bootstrap') }}" defer></script>

</body>
</html>
