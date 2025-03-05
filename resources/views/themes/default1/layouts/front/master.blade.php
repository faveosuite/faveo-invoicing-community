<!DOCTYPE html>
<html>
<head>
    <style>
        .list-styled.columns-lg-2.px-2 li a {
            color: #777 !important; /* Set the anchor text color to black */
        }
        .product-thumbnail-remove, .btn-remove{
            cursor: pointer;
        }
    .page-transition-active {
    opacity: 1 !important; /* Setting the opacity to 1 for full visibility */
}
.custom-input {
    border: 1px solid transparent;
    border-radius: 4px;
    padding: 11.2px 16px;
    color: #777;
    width: 86%;

}
        .custom-input.newsletterEmail.is-invalid {
            border-color: #dc3545 !important;
        }

.custom-input:focus {
    border-color: #777;
    outline: none; 
}
/*This is added because of the eye icon is automatically added in edge browser*/
        input[type="password"]::-ms-reveal {
            display: none !important;
        }

        .form-control.is-invalid {
            background-image: none !important;
        }

        .form-control.is-valid {
            background-image: none !important;
        }


</style>
  <html>
<?php 
 $dataCenters = \App\Model\CloudDataCenters::all();
?>
<?php 
$setting = \App\Model\Common\Setting::where('id', 1)->first();
$everyPageScripts = ''; 
$scripts = \App\Model\Common\ChatScript::where('on_every_page', 1)->get();

foreach($scripts as $script) {
    if (strpos($script->script, '<script>') === false && strpos($script->script, '</script>') === false) {
        $everyPageScripts .= "<script>{$script->script}</script>";
    } else {
        $everyPageScripts .= $script->script;
    }

}
?>



            <!-- Basic -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title') | {{$setting->favicon_title_client}}</title>

    <meta name="keywords" content="Faveo" />
    <meta name="description" content="Faveo">
    <meta name="author" content="Sakthivel Munusami">

    <!-- Favicon -->
    @if($setting->fav_icon)
        <link rel="shortcut icon"  href='{{ $setting->fav_icon }}' type="image/x-icon" />
    @endif
    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Web Fonts  -->
    <link id="googleFonts" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800%7CShadows+Into+Light&display=swap" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>


    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{asset('client/porto/css-2/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('client/porto/css-2/all.min.css')}}">
    <link rel="stylesheet" href="{{asset('client/porto/css-2/animate.compat.css')}}">
    <link rel="stylesheet" href="{{asset('client/porto/css-2/simple-line-icons.min.css')}}">
    <link rel="stylesheet" href="{{asset('client/porto/css-2/owl.carousel.min.css')}}">
    <link rel="stylesheet" href="{{asset('client/porto/css-2/owl.theme.default.min.css')}}">
    <link rel="stylesheet" href="{{asset('client/porto/css-2/magnific-popup.min.css')}}">
    <link rel="stylesheet" href="{{asset('admin/css-1/flag-icons.min.css')}}">

    <!-- Theme CSS -->
    <link id="default-styles" rel="stylesheet" href="{{asset('client/porto/css-2/theme.css')}}">
    <link id="default-styles-2" rel="stylesheet" href="{{asset('client/porto/css-2/theme-elements.css')}}">
    <link id="default-styles-3" rel="stylesheet" href="{{asset('client/porto/css-2/theme-blog.css')}}">
    <link id="default-styles-4" rel="stylesheet" href="{{asset('client/porto/css-2/theme-shop.css')}}">



    <!-- Demo CSS -->
    <link rel="stylesheet" href="{{asset('client/porto/css-2/demo-transportation-logistic.css')}}">

    <!-- Skin CSS -->
    <link id="skinCSS" rel="stylesheet" href="{{asset('client/porto/css-2/skin-transportation-logistic.css')}}">

    <!-- Theme Custom CSS -->
    <link rel="stylesheet" href="{{asset('client/porto/css-2/custom.css')}}">

    <link rel="stylesheet" href="{{asset('common/intl-tel-input/css/intlTelInput.css')}}">

    <!-- Head Libs -->
    <script src="{{asset('client/porto/js-2/modernizr.min.js')}}"></script>
    <script src="{{asset('client/js/modernizr.min.js')}}"></script>

    <!--<script src="{{asset('common/js/jquery-2.1.4.js')}}" type="text/javascript"></script>-->

{{--this need to be change in local when package updated--}}
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" />
    <script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>


    <style>

        .sticky-header-active .d-sticky-header-negative-none {
            display: block !important;
        }
        #footer h1, #footer h2, #footer h3, #footer h4, #footer h5, #footer h6
        {
            color: black !important;
        }
        html .border-color-hover-dark:hover {
            border-color: lightgrey !important;
        }
    </style>

</head>
<?php
$domain = [];
$set = new \App\Model\Common\Setting();
$set = $set->findOrFail(1);
$pay = new \App\Model\Payment\Plan();
$days = $pay->where('product','117')->value('days');
?>
@include('themes.default1.front.demoForm')
<body>
<?php
$bussinesses = App\Model\Common\Bussiness::pluck('name', 'short')->toArray();
$status =  App\Model\Common\StatusSetting::select('recaptcha_status','v3_recaptcha_status', 'msg91_status', 'emailverification_status', 'terms')->first();
$apiKeys = App\ApiKey::select('nocaptcha_sitekey', 'captcha_secretCheck', 'msg91_auth_key', 'terms_url')->first();
$analyticsTag = App\Model\Common\ChatScript::where('google_analytics', 1)->where('on_registration', 1)->value('google_analytics_tag');
$location = getLocation();
?>

<?php
$domain = [];
$set = new \App\Model\Common\Setting();
$set = $set->findOrFail(1);
$pay = new \App\Model\Payment\Plan();
$days = $pay->where('product','117')->value('days');
?>
@php
    $social = App\Model\Common\SocialMedia::get();
@endphp

<?php
function getBaseUrl() {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);
    return $protocol . '://' . $host . $path;
}
function fetchLang() {
    //$baseUrl = getBaseUrl();
    $langUrl = getBaseUrl() . "/lang";
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json;charset=UTF-8\r\n",
            'content' => ''
        ]
    ];
    $context = stream_context_create($options);
    $response = file_get_contents($langUrl, false, $context);

    if ($response === false) {
        return null;
    }

    $langData = json_decode($response, true);

    return $langData['data'];
}

$lang = fetchLang();
?>

<div class="body p-relative bottom-1" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" >

    <header id="header" class="header-effect-reveal" data-plugin-options="{'stickyEnabled': true, 'stickyEffect': 'reveal', 'stickyEnableOnBoxed': true, 'stickyEnableOnMobile': false, 'stickyChangeLogo': false, 'stickyStartAt': 200, 'stickySetTop': '-44px'}">

        <div class="header-body border-0 border-bottom-light">

            <div class="header-container container-fluid p-0">

                <div class="header-row">

                    <div class="header-column header-column-border-right flex-grow-0 d-sticky-header-active-none">

                        <div class="header-row">

                            <div id="main-logo" class="header-logo p-relative top-sm-40 top-30 m-0" style="width: 250px;height: 150px;text-align: center;">

                                <a href="{{Auth::check() ? url('client-dashboard') : url('login')}}">

                                    <img alt="Logo" width="130" height="75" src="{{ $setting->logo }}">
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="header-column">

                        <div class="border-bottom-light w-100">

                            <div class="hstack gap-4 px-4 py-2 font-weight-semi-bold d-none d-lg-flex">
                                @if($set->phone != NULL)
                                    <div class="d-none d-lg-inline-block ps-1">

                                        <a class="text-color-default text-color-hover-primary text-2" href="tel: +{{$set->phone_code}} {{$set->phone}}">

                                            <i class="fas fa-phone text-4 p-relative top-2"></i> &nbsp;+{{$set->phone_code}} {{$set->phone}}
                                        </a>
                                    </div>
                                @endif
                                <div class="vr d-lg-inline-block opacity-2 d-none d-xl-inline-block"></div>
                                @if($set->company_email != NULL)
                                    <div class="d-none d-xl-inline-block">

                                        <a class="text-color-default text-color-hover-primary text-2" href="mailto:{{$set->company_email}}">

                                            <i class="fas fa-envelope text-4 p-relative top-2"></i> &nbsp;{{$set->company_email}}
                                        </a>
                                    </div>
                                @endif
                                <div class="ms-auto d-none d-lg-inline-block">

                                </div>
                                @php
                                    $social = App\Model\Common\SocialMedia::get();
                                @endphp

                                <div class="vr opacity-2 d-none d-lg-inline-block"></div>

                                <div class="d-none d-lg-inline-block">

                                    <ul class="nav nav-pills me-1">
                                        @foreach($social as $media)
                                            <li class="nav-item pe-2 mx-1">
                                                <a href="{{$media->link}}" target="_blank" data-bs-toggle="tooltip" title="{{$media->name}}" class="text-color-default text-color-hover-primary text-4">
                                                    @if ($media->name === 'Facebook')
                                                        <i class="fab fa-facebook-f"></i>
                                                    @elseif ($media->name === 'Twitter')
                                                        <i class="fab fa-twitter"></i>
                                                    @elseif ($media->name === 'Linkedin')
                                                        <i class="fab fa-linkedin-in"></i>
                                                    @endif
                                                </a>
                                            </li>
                                        @endforeach

                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php
                        $groups = \App\Model\Product\ProductGroup::where('hidden','!=', 1)->get();
                        ?>

                        <div class="header-row h-100">

                            <div class="hstack h-100 w-100">

                                <div class="h-100 w-100 w-xl-auto">

                                    <div class="header-nav header-nav-links h-100 justify-content-end justify-content-lg-start me-4 me-lg-0 ms-lg-3">

                                        <div class="header-nav-main header-nav-main-square header-nav-main-dropdown-no-borders header-nav-main-text-capitalize header-nav-main-text-size-4 header-nav-main-arrows header-nav-main-full-width-mega-menu header-nav-main-mega-menu-bg-hover header-nav-main-effect-5">

                                            <nav class="collapse">

                                                <ul class="nav nav-pills" id="mainNav">

                                                    <li class="d-sticky-header-negative-none" style="display:none">

                                                        <a class="nav-link" href="{{Auth::check() ? url('client-dashboard') : url('login')}}">

                                                            <img alt="Logo" width="75" height="50" src="{{ $setting->logo }}">
                                                        </a>
                                                    </li>


                                                    <li class="dropdown">

                                                        <a class="nav-link dropdown-toggle {{ strpos(request()->url(), 'group') !== false ? 'active' : '' }}" href="javascript:;">
                                                            &nbsp;{{ __('message.store') }}&nbsp;
                                                        </a>

                                                        <ul class="dropdown-menu border-light mt-n1">
                                                            @foreach($groups as $group)
                                                                <li>
                                                                    <a class="dropdown-item" href="{{url("group/$group->pricing_templates_id/$group->id")}}">{{$group->name}}</a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </li>
                                                    <?php $pages = \App\Model\Front\FrontendPage::where('publish', 1)->orderBy('created_at','asc')->get();
                                                    ?>

                                                    @foreach($pages as $page)
                                                        @if($page->parent_page_id==0)

                                                            <li class="dropdown">
                                                                    <?php
                                                                    $ifdrop = \App\Model\Front\FrontendPage::where('publish', 1)->where('parent_page_id', $page->id)->count();
                                                                    $class = $ifdrop > 0 ? 'nav-link dropdown-toggle' : 'nav-link';
                                                                    ?>
                                                                @if($page->type == 'contactus')
                                                                    <a class="dropdown-item {{ Request::url() == url('contact-us') ? $class.' active' : $class }}" href="{{ url('contact-us') }}">
                                                                        @else
                                                                            <a class="dropdown-item {{ Request::url() == $page->url ? $class.' active' : $class }}" href="{{ $page->url }}">
                                                                                @endif
                                                                                {{ ucfirst($page->name) }}
                                                                            </a>

                                                                            @if(\App\Model\Front\FrontendPage::where('publish', 1)->where('parent_page_id', $page->id)->count() > 0)
                                                                                    <?php $childs = \App\Model\Front\FrontendPage::where('publish', 1)->where('parent_page_id', $page->id)->get(); ?>
                                                                                <ul class="dropdown-menu border-light mt-n1">
                                                                                    @foreach($childs as $child)
                                                                                        <li>
                                                                                            <a href="{{ $child->url }}" class="dropdown-item {{ Request::url() == $child->url ? 'active' : '' }}">
                                                                                                {{ ucfirst($child->name) }}
                                                                                            </a>
                                                                                        </li>
                                                                                    @endforeach
                                                                                </ul>
                                                                @endif
                                                            </li>
                                                        @endif
                                                    @endforeach


                                                    @if(Auth::user())

                                                        <li class="dropdown">

                                                            <a class="nav-link dropdown-toggle {{ Request::is('client-dashboard', 'my-orders', 'my-invoices', 'my-profile') ? 'active' : '' }}" href="javascript:;">
                                                                &nbsp;{{ __('message.my_account') }}&nbsp;
                                                            </a>

                                                            <ul class="dropdown-menu border-light mt-n1">
                                                                @if(Auth::user()->role == 'admin')
                                                                    <li>
                                                                        <a href="{{url('/')}}" class="dropdown-item">{{ __('message.admin_dashboard') }}</a>
                                                                    </li>
                                                                @endif
                                                                <li>
                                                                    <a href="{{url('client-dashboard')}}" class="dropdown-item">{{ __('message.dashboard') }}</a>
                                                                </li>
                                                                <li>
                                                                    <a href="{{url('my-orders')}}" class="dropdown-item">{{ __('message.my_orders') }}</a>
                                                                </li>

                                                                <li>
                                                                    <a href="{{url('my-invoices')}}" class="dropdown-item">{{ __('message.my_invoices') }}</a>
                                                                </li>

                                                                <li>
                                                                    <a href="{{url('my-profile')}}" class="dropdown-item">{{ __('message.my_profile') }}</a>
                                                                </li>

                                                                <li>
                                                                    <a href="{{url('auth/logout')}}" class="dropdown-item">{{ __('message.logout') }}</a>
                                                                </li>
                                                            </ul>
                                                        </li>
                                                    @else

                                                        <li>
                                                            <a class="nav-link {{ Request::is('login') ? 'active' : '' }}" href="{{url('login')}}">{{ __('message.sign-up') }}</a>
                                                        </li>
                                                    @endif
                                                    <?php
                                                    $cloud = \App\Model\Common\StatusSetting::where('id','1')->value('cloud_button');
                                                    $Demo_page = App\Demo_page::first();
                                                    ?>
                                                    @if($cloud == 1)
                                                        <li class="demo-icons">
                                                            <a class="nav-link open-createTenantDialog startFreeTrialBtn">{{ __('message.start_free_trial') }}</a>
                                                        </li>
                                                        @endif
                                                        </li>
                                                        @if($Demo_page->status)
                                                            <li class="demo-icons">
                                                                <a class="nav-link" id="demo-req">{{ __('message.request_for_demo') }}</a>
                                                            </li>
                                                        @endif
                                                </ul>
                                            </nav>
                                        </div>

                                        <div class="header-nav-features header-nav-features-no-border header-nav-features-lg-show-border order-1 order-lg-2 me-2 me-lg-0">
                                            <div class="header-nav-feature header-nav-features-cart d-inline-flex ms-2 mx-3">
                                                <a href="{{ url('show/cart') }}" class="header-nav-features-toggle text-decoration-none">
                                                    <span class="text-dark opacity-8 font-weight-bold text-color-hover-primary"> {{ __('message.cart') }}</span>
                                                    <img src="{{asset('client/porto/fonts/icon-cart.svg')}}" width="14" alt="" class="header-nav-top-icon-img">
                                                    <span class="cart-info">
                                                <span class="cart-qty">{{ Cart::getTotalQuantity() }}</span>
                                            </span>
                                                </a>
                                                <div class="header-nav-features-dropdown right-15" id="headerTopCartDropdown">
                                                    <ol class="mini-products-list">
                                                        @forelse(Cart::getContent() as $key => $item)
                                                                <?php
                                                                $product = App\Model\Product\Product::where('id', $item->id)->first();
                                                                if ($product->require_domain == 1) {
                                                                    $domain[$key] = $item->id;
                                                                }
                                                                $currency = $item->attributes['currency'];
                                                                $total = rounding($item->getPriceSumWithConditions());
                                                                ?>
                                                            <li class="item">
                                                                <a href="#" data-bs-toggle="tooltip" title="{{ $product->name }}" class="product-image">
                                                                    <img src="{{ $product->image }}" alt="{{ $product->name }}"  width="70">
                                                                </a>
                                                                <div class="product-details">
                                                                    <p class="product-name">
                                                                        <a href="#">{{ $item->name }}</a><br>
                                                                        <span class="amount"><strong>{{ currencyFormat($total, $code = $currency) }}</strong></span>
                                                                    </p>
                                                                    <a onclick="removeItem('{{$item->id}}');"data-bs-toggle="tooltip" title="{{ __('message.remove_this_item') }}" class="btn-remove">
                                                                        <i class="fas fa-times"></i>
                                                                    </a>
                                                                </div>
                                                            </li>
                                                        @empty


                                                            @php
                                                                $data = \App\Model\Product\ProductGroup::where('hidden','!=', 1)->first();
                                                            @endphp                                                                      
                                                  
                                                           <div class="product-details d-flex justify-content-between align-items-center" style="margin-bottom: 20px;font-weight: 500;font-size: 13px;font-family: Poppins,sans-serif;letter-spacing: -0.12px;">
                                                            <span class="text-muted">0 ITEMS</span>
                                                            @if (Auth::check() && $data)
                                                            <a class="text-v-dark text-uppercase" style="color: black;font-family: Poppins,sans-serif;font-weight: 700;font-size: 13px;letter-spacing: -0.12px;" href="{{url("show/cart")}}">{{ __('message.view_cart') }}</a>
                                                            @else
                                                             <a class="text-v-dark text-uppercase" href="{{ url('login') }}">{{ __('message.view_cart') }}</a>
                                                            @endif
                                                        </div>

                                                        <hr style="border-top: 0.5px solid #ccc;">

                                                        <span  style="display: block; text-align: center;">{{ __('message.no_products_cart') }}</span>

                                                          
                                                        @endforelse
                                                        @if (!Cart::isEmpty())
                                                            <div class="totals">
                                                                <span class="label">{{ __('message.total') }}:</span>
                                                                <span class="price-total"><span class="price">{{ currencyFormat(Cart::getTotal(), $code = $currency) }}</span></span>
                                                            </div>

                                                            <li>
                                                                <div class="actions">
                                                                    <a class="btn btn-dark btn-modern text-uppercase font-weight-semi-bold"
                                                                       href="{{ url('show/cart') }}">{{ __('message.view_cart') }}</a>
                                                                    @if (count($domain) > 0)
                                                                        <a href="#domain" data-toggle="modal" data-target="#domain"
                                                                           class="btn btn-primary">{{ __('message.proceed_checkout') }}</a>
                                                                    @else
                                                                        <a href="{{ url('checkout') }}" class="btn btn-primary">{{ __('message.checkout') }}</a>
                                                                    @endif
                                                                </div>
                                                            </li>
                                                        @endif
                                                    </ol>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="header-nav-features header-nav-features-no-border header-nav-features-lg-show-border order-1 order-lg-2 me-2 me-lg-0">
                                            <div class="header-nav-feature header-nav-features-cart d-inline-flex ms-2 mx-3">
                                                <a href="#" class="header-nav-features-toggle text-decoration-none">
                                                    <i id="flagIcon" class="flag-icon flag-icon-us"></i>
                                                </a>
                                                <div class="header-nav-features-dropdown right-15" id="language-dropdown">

                                                </div>
                                            </div>
                                        </div>



                                        <button class="btn header-btn-collapse-nav" data-bs-toggle="collapse" data-bs-target=".header-nav-main nav">

                                            <i class="fas fa-bars"></i>
                                        </button>
                                    </div>
                                </div>


                                <div class="vr opacity-2 ms-auto d-none d-lg-inline-block"></div>

                                <div class="px-4 d-none d-lg-inline-block ws-nowrap">
                                    @if($cloud == 1)
                                        <a class="btn border-0 px-4 py-2 line-height-9 btn-tertiary me-2 open-createTenantDialog startFreeTrialBtn" style="color: white;">{{ __('message.start_free_trial') }}</a>
                                    @endif
                                    @if($Demo_page->status)
                                        <a id="demo-req" class="btn border-0 px-4 py-2 line-height-9 btn-primary" style="color: white;">{{ __('message.request_for_demo') }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div role="main" class=@yield('main-class')>

        <div class="custom-page-header border-bottom-light">
            <section class="page-header page-header-modern bg-color-light-scale-1 border-0 my-0">
                <div class="container my-3">
                    <div class="row">
                        <div class="col-md-12 align-self-center p-static order-2 text-center">
                            <h1 class="font-weight-bold text-10 text-dark">@yield('page-heading')</h1>
                        </div>
                        <div class="col-md-12 align-self-center order-1">
                            <ul class="breadcrumb breadcrumb-light d-block text-center">
                                @yield('breadcrumb')
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <br>

        <div class="container">
            @if(request()->has('message'))
                <div class="alert alert-info alert-dismissible fade show alert-cloud" role="alert">
                    {{ urldecode(request('message')) }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if(Session::has('warning'))

                <div class="alert alert-warning alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{Session::get('warning')}}
                </div>
            @endif

            @if(Session::has('Success'))

                <div class="container">

                    {!!Session::get('Success')!!}
                </div>

            @endif
            @if(!Session::has('Success'))
                @if(Session::has('success'))

                    <div class="alert alert-success">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span></button>
                        <strong><i class="far fa-thumbs-up"></i> {{ __('message.well_done') }}</strong>

                        {!!Session::get('success')!!}
                    </div>
                @endif
            @endif

            <!--fail message -->
            @if(Session::has('fails') )

                <div class="alert alert-danger alert-dismissable" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    {{Session::get('fails')}}
                </div>

            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissable" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    
                    @if ($errors->count() > 1)
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{!! $error !!}</li>
                            @endforeach
                        </ul>
                    @else
                        {!! $errors->first() !!}
                    @endif
                </div>
            @endif


            @include('themes.default1.front.domain')
            @yield('content')

        </div>

    </div>



    @auth

        <div class="modal fade" id="tenant" tabindex="-1" role="dialog" aria-labelledby="trialModalLabel" aria-hidden="true">

            <div class="modal-dialog">

                <div class="modal-content">
                    {!! Form::open() !!}

                    <div class="modal-header">

                        <h4 class="modal-title" id="trialModalLabel">{{optional(cloudPopUpDetails())->cloud_top_message}}</h4>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                    </div>

                    <div class="modal-body">
                        <form action="" method="post" style="width:500px; margin: auto auto;" class="card card-body">

                            <div id="cloudsuccess">
                            </div>
                            <div id="clouderror">
                            </div>

                            <div class="row">

                                <div class="form-group col">

                                    <label class="form-label">{!! optional(cloudPopUpDetails())->cloud_label_field !!}</label>

                                    <div class="input-group mb-2">

                                        <input type="text" name="domain" autocomplete="off" id="userdomain" class="form-control col col-7 rounded-0" placeholder="{{ __('message.admin_domain') }}" required>
                                        <input type="text" class="form-control col col-5 rounded-0" value=".{{cloudSubDomain()}}" disabled="true" style="background-color: #4081B5; color:white; border-color: #0088CC">
                                        <p id="validationMessage"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">

                                <div class="form-group col">

                                    <label class="form-label">{!!optional(cloudPopUpDetails())->cloud_label_radio !!}</label>

                                    <br>
                                    <?php $cloudProducts = \App\Model\Product\CloudProducts::get(); ?>
                                    @foreach($cloudProducts as $cloudProduct)
                                    <div class="form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" name="option" class="product" value="{!! $cloudProduct->cloud_product_key !!}" checked>
                                            {!! \DB::table('products')->where('id',$cloudProduct->cloud_product)->value('name') !!}
                                        </label>
                                    </div>
                                    @endforeach

                                </div>
                            </div>

                            <hr>
                            @if($dataCenters->count()==1)
                                <div class="text-center">
                                    <p>{{ __('message.data_center_location') }} <b data-nearest-center="">{!! array_first($dataCenters)->cloud_countries !!} </b><!--<a role="button" href="javascript:void(0)" data-center-link="" aria-labelledby="data-center-text-label-dataCenter119678097062480"><b>Change</b></a>--></p>
                                </div>
                            @else
                                <label style="margin-top: 2px; text-align: left;"><b>{{ __('message.choose_data_center') }}</b></label>
                                <div class="row">
                                    <div class="col col-12">
                                        <div class="input-group"> <!-- Wrap select and icon within input-group -->
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fa fa-globe"></i></span>
                                            </div>
                                            <select name="cloud-data-centers" id="cloud-data-centers" class="form-control rounded-0">
                                                @foreach($dataCenters as $data)
                                                    <option value="{{$data->id}}">{{$data->cloud_countries}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </form>
                    </div>
                    <script>

                        $(document).ready(function() {
                            $.ajax({
                                url: '{{url("/api/domain")}}',
                                method: 'GET',
                                dataType: 'json',
                                success: function(data) {
                                    if(data.data.length !== 0){
                                        $('.createTenant').attr('disabled', false);
                                    }
                                    $('#userdomain').val(data.data);
                                },
                                error: function(error) {
                                }
                            });
                        });
                    </script>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-default pull-left closebutton" id="closebutton" data-dismiss="modal"><i class="fa fa-times">&nbsp;&nbsp;</i>{{ __('message.close') }}</button>
                        <button type="submit"  class="btn btn-primary createTenant" id="createTenant" onclick="firstlogin({{Auth::user()->id}})"><i class="fa fa-check">&nbsp;&nbsp;</i>{{ __('message.submit') }}</button>

                        {!! Form::close()  !!}
                    </div>
                </div>
            </div>
        </div>
    @endauth

    <div class="modal fade" id="tenancy" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                {!! Form::open() !!}
                <div class="modal-header">
                    <h4 class="modal-title">{{optional(cloudPopUpDetails())->cloud_top_message}}</h4>
                </div>

                <div class="modal-body">
                    <div id="success">
                    </div>
                    <div id="error">
                    </div>
                    <!-- Form  -->

                    <div class="container">
                        <form action="" method="post" style="width:500px; margin: auto auto;" class="card card-body">
                            <input type="hidden" id="orderNo" name="order" value="117">
                            <div class="form-group">
                                <label><b>{!! optional(cloudPopUpDetails())->cloud_label_field !!}</b></label>
                                <div class="input-group mb-2">
                                    <input type="hidden"  name="order" id="orderId"/>
                                    <input type="text" name="domain" autocomplete="off" id="userdomainPurchase" class="form-control col col-7 rounded-0" placeholder="{{ __('message.admin_domain') }}" required>
                                    <input type="text" class="form-control col col-5 rounded-0" value=".{{cloudSubDomain()}}" disabled="true" style="background-color: #4081B5; color:white; border-color: #0088CC">

                                </div>
                                <p id="validationMessagePurchase"></p>
                            </div>
                                <div class="row data-center">
                                    <div class="col col-12">
                                        @if($dataCenters->count()==1)
                                            <div class="text-center">
                                                <p>{{ __('message.data_center_location') }} <b data-nearest-center="">{!! array_first($dataCenters)->cloud_countries !!} </b><!--<a role="button" href="javascript:void(0)" data-center-link="" aria-labelledby="data-center-text-label-dataCenter119678097062480"><b>Change</b></a>--></p>
                                            </div>
                                        @else
                                            <label style="margin-top: 2px; text-align: left;"><b>{{ __('message.choose_data_center') }}</b></label>
                                            <div class="row">
                                                <div class="col col-12">
                                                    <div class="input-group"> <!-- Wrap select and icon within input-group -->
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fa fa-globe"></i></span>
                                                        </div>
                                                        <select name="cloud-data-centers" id="cloud-data-centers" class="form-control rounded-0">
                                                            @foreach($dataCenters as $data)
                                                                <option value="{{$data->id}}">{{$data->cloud_countries}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                        </form>
                    </div>
                </div>
                <script>
                    $(document).ready(function() {
                        $('#tenancy').on('show.bs.modal', function (event) {
                            var button = $(event.relatedTarget); // Button that triggered the modal
                            var myData = button.data('mydata'); // Extract info from data-* attributes

                            // Update the modal content with the data
                            $('#orderId').val(myData);
                        });
                    });
                </script>
                <script>
                    $(document).ready(function() {
                        $.ajax({
                            url: '{{url("/api/domain")}}',
                            method: 'GET',
                            dataType: 'json',
                            success: function(data) {
                                if(data.data.length !== 0){
                                    $('.createtenancy').attr('disabled', false);
                                }
                                $('#userdomainPurchase').val(data.data);
                            },
                            error: function(error) {
                            }
                        });
                    });
                </script>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left closebutton" id="closebutton" data-dismiss="modal"><i class="fa fa-times">&nbsp;&nbsp;</i>{{ __('message.close') }}</button>
                    <button type="submit"  class="btn btn-primary createtenancy" id="createtenancy" onclick="createtenancy()"><i class="fa fa-check">&nbsp;&nbsp;</i>{{ __('message.submit') }}</button>
                    {!! Form::close()  !!}
                </div>
                <!-- /Form -->
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->




    <footer id="footer" class="position-relative bg-color-light-scale-1 border-top-0">

        <div class="container pt-5 pb-3">
            <div class="row">
                <?php
                function renderWidget($widget, $set, $social, $mailchimpKey)
                {
                    $tweetDetails = $widget->allow_tweets == 1 ? '<div id="tweets" class="twitter"></div>' : '';

                    $socialMedia = '';
                    if ($widget->allow_social_media) {
                        // Social Media Icons
                        $socialMedia .= '<ul class="list list-unstyled">';
                        if ($set->company_email) {
                            $socialMedia .= '<li class="d-flex align-items-center mb-4">
                                    <i class="fa-regular fa-envelope fa-xl"></i>&nbsp;&nbsp;
                                    <a href="mailto:' . $set->company_email . '" class="d-inline-flex align-items-center text-decoration-none text-color-grey text-color-hover-primary font-weight-semibold text-4-5">' . $set->company_email . '</a>
                                </li>';
                        }
                        if ($set->phone) {
                            $socialMedia .= '<li class="d-flex align-items-center mb-4">
                                    <i class="fas fa-phone text-4 p-relative top-2"></i>&nbsp;
                                    <a href="tel:' . $set->phone . '" class="d-inline-flex align-items-center text-decoration-none text-color-grey text-color-hover-primary font-weight-semibold text-4-5">+' . $set->phone_code . ' ' . $set->phone . '</a>
                                </li>';
                        }
                        $socialMedia .= '</ul>';

                        // Social Icons
                        $socialMedia .= '<ul class="social-icons social-icons-clean social-icons-medium">';
                        foreach ($social as $media) {
                            $socialMedia .= '<li class="social-icons-' . strtolower($media->name) . '">
                                    <a href="' . $media->link . '" target="_blank" data-bs-toggle="tooltip" title="' . ucfirst($media->name) . '">
                                        <i class="fab fa-' . strtolower($media->name) . ' text-color-grey-lighten"></i>
                                    </a>
                                </li>';
                        }
                        $socialMedia .= '</ul>';
                    }

                    $status =  App\Model\Common\StatusSetting::select('recaptcha_status','v3_recaptcha_status', 'msg91_status', 'emailverification_status', 'terms')->first();

                    $mailchimpSection = '';
                     if ($mailchimpKey !== null && $widget->allow_mailchimp == 1) {
                        $mailchimpSection .= '<div id="mailchimp-message" style="width: 86%;"></div>
                                                <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center">
                                                    <form id="newsletterForm" class="form-style-3 w-100">
                                                        <div class="input-group mb-3">
                                                            <input class="custom-input newsletterEmail" placeholder="Email Address" name="newsletterEmail" id="newsletterEmail" type="email">
                                                        </div>
                                                        <!-- Honeypot fields (hidden) -->
                                                        <div class="mb-3" style="display: none;">
                                                            <label>Leave this field empty</label>
                                                            <input type="text" name="mailhoneypot_field" value="">
                                                        </div>';
                         if ($status->recaptcha_status === 1 || $status->v3_recaptcha_status === 1) {

                             if ($status->recaptcha_status === 1) {
                                 $mailchimpSection .= '
            <div class="mb-3">
                <div id="mailchimp_recaptcha"></div>
                <div class="robot-verification mb-3" id="mailchimpcaptcha"></div>
                <span id="mailchimpcaptchacheck"></span>
            </div>
        ';
                             } elseif ($status->v3_recaptcha_status === 1) {
                                 $mailchimpSection .= '
                <input type="hidden" id="g-recaptcha-mailchimp" class="g-recaptcha-token" name="g-recaptcha-response">
        ';
                             }
                         }
                         $mailchimpSection .= '<button class="btn btn-primary mb-3" id="mailchimp-subscription" type="submit"><strong>GO!</strong></button>
                                            </form>
                                          </div>';
                    }

                    // Check if the 'menu' class exists in the widget content
                    $hasMenuClass = strpos($widget->content, 'menu') !== false;

                    // Add class if 'menu' class exists in the widget content
                    if ($hasMenuClass) {
                        $widget->content = str_replace('<ul', '<ul class="list list-styled columns-lg-2 px-2"', $widget->content);
                    }

                    return '<div class="col-lg-4">
                    <div class="widget-container">
                        <h4 class="text-color-dark font-weight-bold mb-3">' . $widget->name . '</h4>
                        <div class="widget-content">
                            <p class="text-3-5 font-weight-medium pe-lg-2">' . $widget->content . '</p>
                            ' . $tweetDetails . '
                            ' . ($widget->allow_social_media ? $socialMedia : '') . '
                        </div>
                        ' . $mailchimpSection . '
                    </div>
                </div>';
                }

                $footerWidgetTypes = ['footer1', 'footer2', 'footer3'];
                foreach ($footerWidgetTypes as $widgetType) {
                    $widget = \App\Model\Front\Widgets::where('publish', 1)->where('type', $widgetType)->select('name', 'content', 'allow_tweets', 'allow_mailchimp', 'allow_social_media')->first();
                    $mailchimpKey = \App\Model\Common\Mailchimp\MailchimpSetting::value('api_key');
                    if($widget && $widget->allow_mailchimp === 1){
                        $isV2RecaptchaEnabledForNewsletter = 1;
                    }
                    if ($widget) {
                        echo renderWidget($widget, $set, $social, $mailchimpKey);
                    }
                }
                ?>
            </div>
        </div>


        <div class="footer-copyright bg-transparent">

            <div class="container">

                <hr class="bg-color-dark opacity-1">

                <div class="row">
                    <div class="col mt-4 mb-4 pb-5">

                        <p class="text-center text-3 mb-0 text-color-grey">Copyright © <?php echo date('Y') ?> .

                            <a href="{{$set->website}}" class="text-color-grey text-color-hover-primary font-weight-bold">{{$set->company}}. </a>

                            All Rights Reserved. Powered by

                            <a href="{{$set->website}}" class="text-color-grey text-color-hover-primary font-weight-bold" target="_blank">Faveo</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</div>


<!-- Vendor -->
<script src="{{asset('client/porto/js-2/plugins.min.js')}}"></script>


<!--<script src="{{asset('client/js/jquery.min.js')}}"></script>-->
<script src="{{asset('client/js/jquery.appear.min.js')}}"></script>
<script src="{{asset('client/js/jquery.easing.min.js')}}"></script>
<script src="{{asset('client/js/jquery-cookie.min.js')}}"></script>
<!-- <script src="{{asset('client/js/popper.js')}}"></script> -->
<!-- <script src="{{asset('client/js/popper.min.js')}}"></script> -->
<!--<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>-->

<!-- Popper JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!--<script src="{{asset('client/js/bootstrap.min.js')}}"></script>-->
<script src="{{asset('client/js/common.min.js')}}"></script>
<script src="{{asset('client/js/jquery.easy-pie-chart.min.js')}}"></script>
<script src="{{asset('client/js/jquery.gmap.min.js')}}"></script>
<script src="{{asset('client/js/jquery.lazyload.min.js')}}"></script>
<script src="{{asset('client/js/jquery.isotope.min.js')}}"></script>
<script src="{{asset('client/js/owl.carousel.min.js')}}"></script>
<script src="{{asset('client/js/jquery.magnific-popup.min.js')}}"></script>
<script src="{{asset('client/js/vide.min.js')}}"></script>

<!-- Theme Base, Components and Settings -->
<script src="{{asset('client/porto/js-2/theme.js')}}"></script>

<!-- any custom js/effects can be defined in this -->
<script src="{{asset('client/porto/js/custom.js')}}"></script>

<!-- Theme Initialization Files -->
<script src="{{asset('client/porto/js-2/theme.init.js')}}"></script>
<script src="{{asset('common/intl-tel-input/js/intlTelInputWithUtils.js')}}"></script>
<!-- Current Page Vendor and Views -->
<script src="{{asset('client/porto/js-2/view.contact.js')}}"></script>

@extends('mini_views.intl_tel_input')

<script type="text/javascript">

var csrfToken = $('meta[name="csrf-token"]').attr('content');

setInterval(refreshToken, 3600000); 

function refreshToken(){
    $.get('refresh-csrf').done(function(data){
        csrfToken = data.token;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });
    });
}

$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    });

    // Refresh recaptcha token for every ajax request
    $( document ).on( "ajaxComplete", function() {
        try {
            if(recaptchaV3Enabled) {
                updateRecaptchaTokens();
            }
        }catch (ex){
        }
    });

    // Always allow email type only in lowercase
    document.addEventListener("input", function (event) {
        if (event.target.matches('input[type="email"]')) {
            event.target.value = event.target.value.toLowerCase();
        }
    });
});

setTimeout(function() {
    $('.alert-success, .alert-danger').alert('close');
}, 5000);

</script>

  <script>

      let mailchimp_recaptcha_id;
      let recaptchaTokenMailChimp;
      @if(!Auth::check() && $status->recaptcha_status === 1 && isset($isV2RecaptchaEnabledForNewsletter) && $isV2RecaptchaEnabledForNewsletter === 1)
      recaptchaFunctionToExecute.push(() => {
          mailchimp_recaptcha_id = grecaptcha.render('mailchimp_recaptcha', { 'sitekey': siteKey });
      });
      @endif
    </script>
<script>

    $(document).ready(function() {
        $('#mailchimp-subscription').on('click', function(e) {
            e.preventDefault();
            console.log("in");

            // Select elements using jQuery
            var $form = $('#newsletterForm');
            var form = $form[0]; // raw DOM element for FormData
            var $honeypot = $('input[name="mailhoneypot_field"]');
            var $recaptchaResponse = $('input[name="g-recaptcha-response"]');
            var $emailField = $('#newsletterEmail');
            var $mailchimpMessage = $('#mailchimp-message');
            var alertTimeout;

            // Function to display inline error messages
            function placeErrorMessage(error, $element) {
                $element.addClass("is-invalid");
                // Remove any existing error message
                $element.next('.invalid-feedback').remove();
                // Insert the error message
                $element.after(`<div class="invalid-feedback">${error}</div>`);
            }

            // Function to show alerts
            function showAlert(type, message) {
                // Convert "error" to "danger" for Bootstrap styling
                var alertType = type === 'error' ? 'danger' : type;
                var $alertDiv = $(`
        <div class="alert alert-${alertType} alert-dismissible">
          <i class="fa ${alertType === 'success' ? 'fa-check-circle' : 'fa-ban'}"></i> ${message}
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        </div>
      `);
                // Clear any previous message and display the new alert
                $mailchimpMessage.html($alertDiv);

                if (alertTimeout) {
                    clearTimeout(alertTimeout);
                }
                alertTimeout = setTimeout(function() {
                    $alertDiv.fadeOut();
                }, 5000);
            }

            // Stop processing if the honeypot field is filled
            if ($honeypot.val()) {
                return false;
            }

            // Validate email field
            if (!$emailField.val()) {
                placeErrorMessage("Please enter a valid email address.", $emailField);
                return;
            }

            // Validate recaptcha response if it exists
            if ({{ isCaptchaRequired()['status'] }} && recaptchaEnabled && $recaptchaResponse.length && !$recaptchaResponse.val().trim()) {
                placeErrorMessage("Please verify that you are not a robot.", $recaptchaResponse);
                return;
            }

            // Prepare form data for AJAX submission
            var formData = new FormData(form);

            $.ajax({
                type: 'POST',
                url: '{{url("mail-chimp/subcribe")}}',  // Fixed the endpoint URL
                data: formData,
                processData: false,  // Required for FormData
                contentType: false,  // Required for FormData
                beforeSend: function() {
                    $('#mailchimp-subscription').html('Wait ...')
                },
                success: function(data) {
                    showAlert('success', data.message);
                },
                error: function(jqXHR, status, error) {
                    // Extract an error message if available
                    var errorMsg = (jqXHR.responseJSON && jqXHR.responseJSON.message) ?
                        jqXHR.responseJSON.message :
                        '{{ __('message.error_occurred') }}';
                    showAlert('error', errorMsg);
                },
                complete: function() {
                    $('#mailchimp-subscription').html('GO!')
                }
            });
        });
    });
    function removeItem(id) {
        $.ajax({
            type: "post",
            data:{
                "id": id,
                "_token": "{!! csrf_token() !!}",
            },
            url: "{{url('cart/remove/')}}",
            success: function (data) {
                location.reload();
            }
        });
    }


    $(document).ready(function(){
        $('.createTenant').attr('disabled',true);
        $('#userdomain').keyup(function(){
            if($(this).val().length ==0 || $(this).val().length>28)
                $('.createTenant').attr('disabled', true);
            else
                $('.createTenant').attr('disabled',false);
        })
    });



    function firstlogin(id)
    {
        $('#createTenant').attr('disabled',true)
        $("#createTenant").html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait') }}");
        var domain = $('#userdomain').val();
        var password = $('#password').val();
        var product = $('input[name="option"]:checked').val();

        $.ajax({
            type: 'POST',
            data: {'id':id,'password': password,'domain' : domain,'product':product},
            url: "{{url('first-login')}}",
            success: function (data) {
                $('#createTenant').attr('disabled',false)
                $("#createTenant").html("<i class='fa fa-check'>&nbsp;&nbsp;</i>{{ __('message.submit') }}");
                if(data.status == 'validationFailure') {

                    var html = '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>{{ __('message.whoops') }} </strong>{{ __('message.something_wrong') }}<ul>';
                    for (var key in data.message)
                    {
                        html += '<li>' + data.message[key][0] + '</li>'
                    }
                    html += '</ul></div>';
                    $('#clouderror').show();
                    $('#cloudsuccess').hide();
                    document.getElementById('error').innerHTML = html;
                } else if(data.status == 'false') {
                    $('#clouderror').show();
                    $('#cloudsuccess').hide();
                    var result =  '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong>{{ __('message.whoops') }} </strong>{{ __('message.something_wrong') }}!!<br><ul><li>'+data.message+'</li></ul></div>';
                    $('#clouderror').html(result);
                } else if(data.status == 'success_with_warning') {
                    console.log('here');
                    $('#clouderror').show();
                    $('#cloudsuccess').hide();
                    var result =  '<div class="alert alert-warning alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong>{{ __('message.whoops') }} </strong><br><ul><li>'+data.message+'</li></ul></div>';
                    $('#clouderror').html(result);
                } else {
                    $('#clouderror').hide();
                    $('#cloudsuccess').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong>{{ __('message.success') }}! </strong>'+data.message+'!</div>';
                    $('#cloudsuccess').html(result);
                }
            },error: function (response) {
                $('#createTenant').attr('disabled',false)
                $("#createTenant").html("<i class='fa fa-check'>&nbsp;&nbsp;</i>Submit");
                $("#generate").html("<i class='fa fa-check'>&nbsp;&nbsp;</i>Submit");

                var html = '<div class="alert alert-danger alert-dismissable">' +
                    '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                    '<strong>Whoops! </strong>Something went wrong<ul>';

                if (response.status == 422) {
                    for (var key in response.responseJSON.errors) {
                        html += '<li>' + response.responseJSON.errors[key][0] + '</li>';
                    }

                } else {
                    html += '<li>' + response.responseJSON.message + '</li>';
                }

                html += '</ul></div>';

                $('#clouderror').show();
                $('#cloudsuccess').hide();
                document.getElementById('clouderror').innerHTML = html;

                // Hide the error message after 5 seconds (5000 milliseconds)
                setTimeout(function () {
                    $('#clouderror').fadeOut('slow');
                }, 5000);
            }


        }) ;
    }


    $(document).on("click", ".open-createTenantDialog", function () {

        $('#tenant').modal('show');
    });
    $('.closebutton').on('click',function(){
        location.reload();
    });

    $(document).on("click", "#demo-req", function () {
        $('#demo-req').modal('show');
    });
    $('.closebutton').on('click',function(){
        location.reload();
    });

    const domainInput = document.getElementById("userdomain");
    const validationMessage = document.getElementById("validationMessage");

    domainInput.addEventListener("input", function() {
        const domain = domainInput.value;

        if (domain.length > 28) {
            validationMessage.textContent = "{{ __('message.domain_characters') }}";
            validationMessage.style.color = "red";
        } else {
            validationMessage.textContent = "";
            validationMessage.style.color = "";
        }
    });

    function togglePasswordVisibility(iconElement) {
        const inputGroup = iconElement.closest('.input-group');
        const passwordInput = inputGroup.querySelector('input[type="password"], input[type="text"]');
        const icon = iconElement.querySelector('i');

        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';

        icon.classList.toggle('fa-eye-slash', !isPassword);
        icon.classList.toggle('fa-eye', isPassword);
    }
</script>
@yield('script')

<!--Start of Tawk.to Script-->
<!--Start of Tawk.to Script-->
{!! $everyPageScripts !!}




@if(request()->path() !== 'my-profile' && request()->path() !== 'verify')

    <script type="text/javascript">
        var demotelInput = $('#mobilenumdemo'),
            errorMsgdemo = document.querySelector("#error-msgdemo"),
            validMsgdemo = document.querySelector("#valid-msgdemo"),
            addressDropdowndemo = $("#country");

        var resetdemo = function() {
            errorMsgdemo.innerHTML = "";
            errorMsgdemo.classList.add("hide");
            validMsgdemo.classList.add("hide");
        };

        $('.intl-tel-input').css('width', '100%');

        demotelInput.on('input blur', function () {
            resetdemo();
            if ($.trim(demotelInput.val())) {
                if (validatePhoneNumber(demotelInput.get(0))) {
                    $('#mobilenumdemo').css("border-color","");
                    $("#error-msgdemo").html('');
                    errorMsgdemo.classList.add("hide");
                    $('#demoregister').attr('disabled',false);
                } else {
                    errorMsgdemo.classList.remove("hide");
                    errorMsgdemo.innerHTML = "{{ __('message.error_valid_number') }}";
                    $('#mobilenumdemo').css("border-color","red");
                    $('#error-msgdemo').css({"color":"red","margin-top":"5px"});
                    $('#demoregister').attr('disabled',true);
                }
            }
        });
        $('input').on('focus', function () {
            $(this).parent().removeClass('has-error');
        });
        addressDropdowndemo.change(function() {
            if ($.trim(demotelInput.val())) {
                if (validatePhoneNumber(demotelInput.get(0))) {
                    $('#mobilenumdemo').css("border-color","");
                    $("#error-msgdemo").html('');
                    errorMsgdemo.classList.add("hide");
                    $('#demoregister').attr('disabled',false);
                } else {
                    errorMsgdemo.classList.remove("hide");
                    errorMsgdemo.innerHTML = "{{ __('message.error_valid_number') }}";
                    $('#mobilenumdemo').css("border-color","red");
                    $('#error-msgdemo').css({"color":"red","margin-top":"5px"});
                    $('#demoregister').attr('disabled',true);
                }
            }
        });


        $(document).ready(function() {
            $('#tenancy').on('shown.bs.modal', function () {
                $('#userdomainPurchase').focus();
            });
        });

        const domainInputPurchase = document.getElementById("userdomainPurchase");
        const validationMessagePurchase = document.getElementById("validationMessagePurchase");

        domainInputPurchase.addEventListener("input", function() {
            const domainName = domainInputPurchase.value;

            if (domainName.length > 28) {
                validationMessagePurchase.textContent = "{{ __('message.domain_characters') }}";
                validationMessagePurchase.style.color = "red";
            } else {
                validationMessagePurchase.textContent = "";
                validationMessagePurchase.style.color = "";
            }
        });
        $(document).ready(function(){
            $('.createtenancy').attr('disabled',true);
            $('#userdomainPurchase').keyup(function(){
                if($(this).val().length ==0 || $(this).val().length>28)
                    $('.createtenancy').attr('disabled', true);
                else
                    $('.createtenancy').attr('disabled',false);
            })
        });

        function createtenancy(){
            $('#createtenancy').attr('disabled',true)
            $("#createtenancy").html("<i class='fas fa-circle-notch fa-spin'></i> {{ __('message.please_wait') }}");
            var domain = $('#userdomainPurchase').val();
            var order = $('#orderId').val();
            $.ajax({
                url: "{{url('create/tenant/purchase')}}",
                type: "POST",
                data: {'domain': domain, 'id': order},
                success: function (data) {
                    $('#createtenancy').attr('disabled',false)
                    $("#createtenancy").html("<i class='fa fa-check'>&nbsp;&nbsp;</i>{{ __('message.submit') }}");
                    if(data.status == 'validationFailure') {

                        var html = '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>{{ __('message.whoops') }} </strong>{{ __('message.something_wrong') }}<ul>';
                        for (var key in data.message)
                        {
                            html += '<li>' + data.message[key][0] + '</li>'
                        }
                        html += '</ul></div>';
                        $('#error').show();
                        $('#success').hide();
                        document.getElementById('error').innerHTML = html;
                    } else if(data.status == 'false') {
                        $('#error').show();
                        $('#success').hide();
                        var result =  '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>{{ __('message.whoops') }} </strong>{{ __('message.something_wrong') }}!<br><ul><li>'+data.message+'</li></ul></div>';
                        $('#error').html(result);
                    } else if(data.status == 'success_with_warning') {
                        console.log('here');
                        $('#error').show();
                        $('#success').hide();
                        var result =  '<div class="alert alert-warning alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Whoops! </strong><br><ul><li>'+data.message+'</li></ul></div>';
                        $('#error').html(result);
                    } else {
                        window.location.href = data.redirectTo;
                    }
                },error: function (response) {
                    $('#createtenancy').attr('disabled',false)
                    $("#createtenancy").html("<i class='fa fa-check'>&nbsp;&nbsp;</i>{{ __('message.submit') }}");
                    $("#generate").html("<i class='fa fa-check'>&nbsp;&nbsp;</i>{{ __('message.submit') }}");
                    if(response.status == 422) {

                        var html = '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>{{ __('message.whoops') }} </strong>{{ __('message.something_wrong') }}<ul>';
                        for (var key in response.responseJSON.errors)
                        {
                            html += '<li>' + response.responseJSON.errors[key][0] + '</li>'
                        }

                    } else {
                        var html = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>{{ __('message.whoops') }} </strong>{{ __('message.something_wrong') }}<ul>';
                        html += '<li>' + response.responseJSON.message + '</li>'
                    }

                    html += '</ul></div>';
                    $('#error').show();
                    $('#success').hide();
                    document.getElementById('error').innerHTML = html;

                }

            })
        }
        $(document).ready(function () {

            // Check if the user is authenticated
            @auth
            // If authenticated, check if localStorage indicates a click
            var freeTrialClicked = localStorage.getItem('freeTrialClicked');
            if (freeTrialClicked === 'true') {
                // If localStorage indicates a click, open the free trial dialog
                openFreeTrialDialog();
                localStorage.removeItem('freeTrialClicked');
            }

            // Attach a click event handler to the "START FREE TRIAL" button
            $('.startFreeTrialBtn').on('click', function () {
                // If the button is clicked, open the free trial dialog
                openFreeTrialDialog();
            });
            @else
            // If not authenticated, redirect to the login/register page only if localStorage indicates a click
            var freeTrialClicked = localStorage.getItem('freeTrialClicked');
            // Attach a click event handler to the "START FREE TRIAL" button
            $('.startFreeTrialBtn').on('click', function () {
                // If not authenticated, remember that the button was clicked
                localStorage.setItem('freeTrialClicked', 'true');
                var message = "{{ __('message.log_free_trial') }}";
                var baseUrl = "{{ env('APP_URL') }}";

                // Redirect to the login/register page
                window.location.href =  `${baseUrl}/login?message=${encodeURIComponent(message)}`;
            });
            @endauth

            // Function to open the free trial dialog
            function openFreeTrialDialog() {
                // Check if the modal is already open (to prevent multiple opens)
                if (!$('#tenant').hasClass('show')) {
                    $('#tenant').modal('show');
                }
            }
        });


        setTimeout(function () {
            $(".alert-cloud").alert('close');
        }, 10000);
    </script>
@endif
<script>
    $(document).ready(function() {
        $.fn.modal.Constructor.Default.backdrop = 'static';
    });

    const flagIcon = document.getElementById('flagIcon');
    const languageDropdown = document.getElementById('language-dropdown');

    $(document).ready(function() {
        const localeMap = { 'ar': 'ae', 'bsn': 'bs', 'de': 'de', 'en': 'us', 'en-gb': 'gb', 'es': 'es', 'fr': 'fr', 'id': 'id', 'it': 'it', 'kr': 'kr', 'mt': 'mt', 'nl': 'nl', 'no': 'no', 'pt': 'pt', 'ru': 'ru', 'vi': 'vn', 'zh-hans': 'cn', 'zh-hant': 'cn' };
        const currentLocale = '{{ app()->getLocale() }}';
        const mappedLocale = localeMap[currentLocale] || 'us';
        $('#flagIcon').addClass('flag-icon flag-icon-' + mappedLocale);

        $.ajax({
            url: '<?php echo getBaseUrl(); ?>/language/settings',
            type: 'GET',
            dataType: 'JSON',
            success: function(response) {
                $.each(response.data, function(key, value) {

                    const mappedLocale = localeMap[value.locale] || value.locale;
                    const isSelected = value.locale === currentLocale ? 'selected' : 'us';
                    $('#language-dropdown').append(
                        '<a href="javascript:;" class="dropdown-item ' + isSelected + '" data-locale="' + value.locale + '">' +
                        '<i class="flag-icon flag-icon-' + (mappedLocale || 'us') + ' mr-2"></i> ' + value.name +
                        '</a>'
                    );
                });

                // Add event listeners for the dynamically added language options
                $(document).on('click', '.dropdown-item', function() {
                    const selectedLanguage = $(this).data('locale');
                    const mappedLocale = localeMap[selectedLanguage] || selectedLanguage;
                    const flagClass = 'flag-icon flag-icon-' + mappedLocale;
                    const dir = selectedLanguage === 'ar' ? 'rtl' : 'ltr';

                    updateLanguage(selectedLanguage, flagClass, dir);
                });
            },
            error: function(error) {
                console.error('Error fetching languages:', error);
            }
        });

        $.ajax({
            url: '<?php echo getBaseUrl() ?>/current-language',
            type: 'GET',
            dataType: 'JSON',
            success: function(response) {
                const currentLanguage = response.data.language;
                const flagClass = 'flag-icon flag-icon-' + (localeMap[currentLanguage] || 'us');
                $('#flagIcon').attr('class', flagClass);
            },
            error: function(error) {
                console.error('Error fetching current language:', error);
            }
        });
    });

    function updateLanguage(language, flagClass, dir) {
        $('#flagIcon').attr('class', flagClass);
        $.ajax({
            url: '<?php echo getBaseUrl(); ?>/update/language',
            type: 'POST',
            data: { language: language },
            success: function(response) {
                window.location.reload();
            },
            error: function(xhr, status, error) {
                console.error('Error updating language:', xhr.responseText);
            }
        });
    }
</script>

<style>
    .custom-line {
        border: none;
        border-top: 1px solid #ccc;
        margin: 10px 0;
    }
    #validationMessage {
        position: absolute;
        top: 40px; /* Adjust this value to align the error message properly */
        left: 0;
        font-size: 12px;
        color: red;
    }

</style>
</body>
</html>
@yield('end')