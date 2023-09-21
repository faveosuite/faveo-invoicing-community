<!DOCTYPE html>
<html>
<?php $setting = \App\Model\Common\Setting::where('id', 1)->first();
$everyPageScript = '';
$scripts = \App\Model\Common\ChatScript::get();
foreach($scripts as $script)
    if($script->on_every_page == 1) {
        $everyPageScript = $script->script;
    }

?>
<style type="text/css">
    /*for making datatable side scrollable whenever it has way too many columns for screen to accomodate   */
    .dataTables_wrapper {
        overflow-x: auto;
    }

    .highlight-button {
        background-color: #0088CC;
        color: white !important;
        font-weight: bold;
    }
    .highlight {
        border: 2px solid;
        border-radius: 5px;
        padding: 8px;
        transition: background-color 0.3s;
        height: 90%;
    }
    .highlight:hover {
        background-color: blue;
        color: white;
    }

    label.required:after {
        color: red;
     }
.highlight:hover {
    background-color: blue;
    color: white;
}

.custom-line {
    border: none;
    border-top: 1px solid #ccc;
    margin: 10px 0;
}
#validationMessage {
  position: absolute;
  top: 80px; /* Adjust this value to align the error message properly */
  margin-left:32px;
  left: 0;
  font-size: 12px;
  color: red;
}

  .breadcrumb > li + li:before {
        content: "\3e" !important;
    }
</style>
       


<head>
    <!-- Basic -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>@yield('title') | {{$setting->favicon_title_client}}</title>

    <meta name="keywords" content="HTML5 Template" />
    <meta name="description" content="Register, signup here to start using Faveo Helpdesk or signin to your existing account">
    <meta name="author" content="okler.net">

    <!-- Favicon -->
    @if($setting->fav_icon)
        <link rel="shortcut icon" href='{{asset("storage/common/images/$setting->fav_icon")}}' type="image/x-icon" />
    @endif
    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <!-- Web Fonts  -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800%7CShadows+Into+Light" rel="stylesheet" type="text/css">


    <link rel="stylesheet" href="{{asset('client/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('client/css/fontawesome-all.min.css')}}">
    <link rel="stylesheet" href="{{asset('client/css/font-awesome.min.css')}}">
    <link rel="stylesheet" href="{{asset('client/css/animate.min.css')}}">
    <link rel="stylesheet" href="{{asset('client/css/magnific-popup.min.css')}}">

    <link rel="stylesheet" href="{{asset('client/porto/css-1/theme.css')}}">
    <link rel="stylesheet" href="{{asset('client/porto/css-1/theme-elements.css')}}">
    <link rel="stylesheet" href="{{asset('client/porto/css-1/theme-shop.css')}}">

    <link rel="stylesheet" href="{{asset('common/css/intlTelInput.css')}}">


    {{-- this can be customised to any skin available --}}
    <link rel="stylesheet" href="{{asset('client/porto/css-1/skins/default.css')}}">
    {{--  any custom css can be defined in this  --}}
    <link rel="stylesheet" href="{{asset('client/porto/css/custom.css')}}">


    <!-- Head Libs -->
    <script src="{{asset('client/js/modernizr.min.js')}}"></script>

    <script src="{{asset("common/js/jquery-2.1.4.js")}}" type="text/javascript"></script>
    <script src="{{asset("common/js/jquery2.1.1.min.js")}}" type="text/javascript"></script>


</head>
<style>
    .alert {
        font-weight:bolder;
    }
</style>
<body>
<?php
$bussinesses = App\Model\Common\Bussiness::pluck('name', 'short')->toArray();
$status =  App\Model\Common\StatusSetting::select('recaptcha_status', 'msg91_status', 'emailverification_status', 'terms')->first();
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
@include('themes.default1.front.demoForm')
<div class="body">
    <header id="header" data-plugin-options="{'stickyEnabled': true, 'stickyEnableOnBoxed': true, 'stickyEnableOnMobile': true, 'stickyStartAt': 45, 'stickySetTop': '-45px', 'stickyChangeLogo': true}">
        <div class="header-body"  style="top: -50px;">
            <div class="header-container container">
                <div class="header-row">
                    <div class="header-column">
                        <div class="header-row">
                            <div class="header-logo">
                                <a href="{{Auth::check() ? url('my-invoices') : url('login')}}">
                                    <img alt="Logo" width="100" height="51" data-sticky-width="70" data-sticky-height="36" data-sticky-top="30" src="{{asset('storage/images/'.$setting->logo)}}">
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="header-column justify-content-end">
                        <div class="header-row pt-3">
                            <nav class="header-nav-top">
                                <ul class="nav nav-pills">
                                    @auth
                                        @php
                                            $amt = \DB::table('payments')->where('user_id',\Auth::user()->id)->where('payment_method','Credit Balance')->where('payment_status','success')->value('amt_to_credit');
                                            $formattedValue = currencyFormat($amt, \Auth::user()->currency , true);

                                        @endphp
                                        <button class="btn-credit open-createCreditDialog" style="background-color: white; border: none;">
                                            <i class="fas fa-credit-card"></i> Credits: {!! $formattedValue !!}
                                        </button>
                                    <script>
                                        $(document).ready(function () {
                                        $(document).on("click", ".open-createCreditDialog", function () {
                                        $('#credit').modal('show');
                                        });
                                        });
                                    </script>
                                    @endauth

                                    @if($set->company_email != NULL)
                                        <li class="nav-item nav-item-anim-icon d-none d-md-block">
                                                    <span class="ws-nowrap"><i class="fas fa-envelope"></i>
                                                        <a style="color: inherit" href="mailto:{{$set->company_email}}">{{$set->company_email}}</a>
                                                    </span>
                                        </li>
                                    @endif
                                    @if($set->phone != NULL)
                                        <li class="nav-item nav-item-left-border nav-item-left-border-remove nav-item-left-border-md-show">
                                                    <span class="ws-nowrap"><i class="fas fa-phone"></i>
                                                        <a style="color: inherit" href="tel:{{$set->phone}}">{{$set->phone}}</a>
                                                    </span>
                                        </li>
                                    @endif
                                    @if(!Auth::user())
                                        <li class="nav-item nav-item-left-border nav-item-left-border-remove nav-item-left-border-md-show">
                                                    <span class="ws-nowrap">
                                                        <a style="color: inherit" href={{url('login')}}><i class="fas fa-user"></i>My Account</a>
                                                    </span>
                                        </li>
                                    @endif

                                </ul>
                            </nav>
                        </div>


                        <div class="header-row">
                            <div class="header-nav pt-1" style="margin-top: 0px; margin-bottom: -10px;">

                                <button class="btn btn-sm header-btn-collapse-nav" data-toggle="collapse" data-target=".header-nav-main nav">
                                    <i class="fa fa-bars"></i>
                                </button>

                                <div class="header-nav-main header-nav-main-effect-1 header-nav-main-sub-effect-1">
                                    <nav class="collapse">
                                        <ul class="nav nav-pills" id="mainNav">

                                            @auth
                                                <?php
                                                $id = \Auth::user()->id;
                                                $cloud = \App\Model\Common\StatusSetting::where('id','1')->value('cloud_button');
                                                ?>


                                                @if(Auth::check() && $cloud == 1)



                                                   <a class="nav-link open-createTenantDialog" style=" text-decoration: none;cursor: pointer;background-color: #0088CC;color: white !important;font-weight: bold;">                                                            
                                                    <div>
                                                                <i class="fas fa-cloud"></i>
                                                                <span style="margin-left: 3px;">START FREE TRIAL</span>
                                                            </div>
                                                        </a>
                                                  

                                                @endif
                                            @endauth


                                            <?php
                                            $groups = \App\Model\Product\ProductGroup::where('hidden','!=', 1)->get();

                                            ?>

                                            <li class="dropdown">
                                                <a class="dropdown-item dropdown-toggle" href="#">
                                                    Store&nbsp;
                                                </a>
                                                <ul class="dropdown-menu">
                                                    @if(count($groups)>0)
                                                        @foreach($groups as $group)
                                                            <li>
                                                                <a class="dropdown-item" href="{{url("group/$group->pricing_templates_id/$group->id")}}">
                                                                    {{$group->name}}
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    @else
                                                        <li>
                                                            <a class="dropdown-item">
                                                                No Groups Added
                                                            </a>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </li>


                                            <?php $pages = \App\Model\Front\FrontendPage::where('publish', 1)->orderBy('created_at','asc')->get();
                                             ?>

                                            @foreach($pages as $page)

                                                <li class="dropdown">
                                                    @if($page->parent_page_id==0)
                                                            <?php
                                                            $ifdrop = \App\Model\Front\FrontendPage::where('publish', 1)->where('parent_page_id', $page->id)->count();
                                                            if ($ifdrop > 0) {
                                                                $class = 'nav-link dropdown-toggle';
                                                            } else {
                                                                $class = 'nav-link';
                                                            }
                                                            ?>
                                                        @if($page->type == 'contactus')
                                                            <a class="nav-link" href="{{url('contact-us')}}">
                                                                @else
                                                                    <a class="{{$class}}" href="{{$page->url}}">
                                                                        @endif
                                                                        {{ucfirst($page->name)}}
                                                                    </a>
                                                                @endif
                                                                @if(\App\Model\Front\FrontendPage::where('publish',1)->where('parent_page_id',$page->id)->count()>0)


                                                                        <?php $childs = \App\Model\Front\FrontendPage::where('publish', 1)->where('parent_page_id', $page->id)->get(); // dd($childs); ?>
                                                                    <ul class="dropdown-menu">

                                                                        @foreach($childs as $child)
                                                                            <li>
                                                                                <a href="{{$child->url}}">
                                                                                    {{ucfirst($child->name)}}
                                                                                </a>
                                                                            </li>

                                                                        @endforeach
                                                                    </ul>
                                                        @endif

                                                </li>
                                            @endforeach





                                            <li class="dropdown dropdown-mega dropdown-mega-shop" id="headerShop">
                                                <a class="dropdown-item dropdown-toggle" href="{{url('show/cart')}}">
                                                    <i class="fa fa-shopping-cart"></i> Cart ({{Cart::getTotalQuantity()}})
                                                </a>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <div class="dropdown-mega-content">
                                                            <table class="cart">
                                                                <tbody>

                                                                @forelse(Cart::getContent() as $key=>$item)

                                                                        <?php
                                                                        // dd($item);
                                                                        $product = App\Model\Product\Product::where('id', $item->id)->first();
                                                                        if ($product->require_domain == 1) {
                                                                            $domain[$key] = $item->id;
                                                                        }

                                                                        $currency =  $item->attributes['currency'];
                                                                        ?>
                                                                    <tr>

                                                                        <td class="product-thumbnail">
                                                                            <img width="100" height="100" alt="{{$product->name}}" class="img-responsive" src="{{$product->image}}">
                                                                        </td>

                                                                        <td class="product-name">
                                                                                <?php
                                                                                $total = rounding($item->getPriceSumWithConditions())
                                                                                ?>
                                                                            <a>{{$item->name}}<br><span class="amount"><strong>{{currencyFormat($total,$code = $currency)}}</strong></span></a>
                                                                        </td>

                                                                        <td class="product-actions">
                                                                            <a title="Remove this item" class="remove" href="#" onclick="removeItem('{{$item->id}}');">
                                                                                <!--  @if(Session::has('items'))
                                                                                    {{Session::forget('items')}}
                                                                                @endif -->
                                                                                <i class="fa fa-times"></i>
                                                                            </a>
                                                                        </td>

                                                                    </tr>
                                                                @empty
                                                                    @php
                                                                        $data = \App\Model\Product\ProductGroup::where('hidden','!=', 1)->first();
                                                                    @endphp

                                                                    <tr>
                                                                        <td>


                                                                            @if(Auth::check())
                                                                                <a href="">Choose a Product
                                                                                    @else
                                                                                        <a href="{{url('login')}}">Choose a Product
                                                                                            @endif
                                                                                        </a></td>
                                                                    </tr>


                                                                @endforelse


                                                                @if(!Cart::isEmpty())
                                                                    <tr>
                                                                        <td class="actions" colspan="6">
                                                                            <div class="actions-continue">
                                                                                <a href="{{url('show/cart')}}"><button class="btn btn-default pull-left">View Cart</button></a>


                                                                                @if(count($domain)>0)
                                                                                    <a href="#domain" data-toggle="modal" data-target="#domain"><button class="btn btn-primary pull-right">Proceed to Checkout</button></a>
                                                                                @else
                                                                                    <a href="{{url('checkout')}}"><button class="btn btn-primary pull-right">Proceed to Checkout</button></a>
                                                                                @endif
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </li>&nbsp&nbsp
                                            <?php
                                             $Demo_page = App\Demo_page::first();
                                            ?>

                                             @if($Demo_page->status)
                                                <li class="dropdown">
                                                    <a  class="nav-link highlight" id="demo-req" style="cursor: pointer;">
                                                        REQUEST FOR DEMO
                                                    </a>
                                                </li>&nbsp&nbsp&nbsp
                                                @endif

                                            @if(!Auth::user())
                                                <a class="nav-link" style="font-size: 12px;text-decoration: none;cursor: pointer;background-color: #0088CC;color: white !important;font-weight: bold;" href="{{url('login')}} ">
                                                        SIGNUP FOR FREE
                                                    </a>





                                            @else
                                                <li class="dropdown">
                                                    <a class="dropdown-item dropdown-toggle" href="{{url('login')}}">
                                                        My Account
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        @if(Auth::user()->role == 'admin')
                                                            <li><a class="dropdown-item" href="{{url('/')}}">Go to Admin Panel</a></li>
                                                        @endif
                                                        <li><a class="dropdown-item" href="{{url('my-orders')}}">My Orders</a></li>
                                                        <li><a class="dropdown-item" href="{{url('my-invoices')}}">My Invoices</a></li>
                                                        <li><a class="dropdown-item" href="{{url('my-profile')}}">My Profile</a></li>
                                                        <li><a class="dropdown-item" href="{{url('auth/logout')}}">Logout</a></li>
                                                    </ul>
                                                </li>
                                            @endif


                                        </ul>
                                    </nav>
                                </div>

                                <ul class="header-social-icons social-icons d-none d-sm-block">
                                    @php
                                        $social = App\Model\Common\SocialMedia::get();
                                    @endphp
                                    @foreach($social as $media)
                                        <li class="social-icons-{{lcfirst($media->name)}}"><a href="{{$media->link}}" target="_blank" title="{{ucfirst($media->name)}}"><i class="fa fa-{{lcfirst($media->name)}}"></i></a></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div role="main" class=@yield('main-class')>

        <section class="page-header page-header-modern bg-color-light-scale-1 page-header-md">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 align-self-center p-static order-2 text-center">
                        <h1 class="text-dark text-dark">
                            <strong>
                                @yield('page-heading')
                            </strong>
                        </h1>
                    </div>
                    <div class="col-md-12 align-self-center order-1">
                        <ul class="breadcrumb d-block text-center" style="font-weight: initial;">
                            @yield('breadcrumb')
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <div class="container">
            @if(Session::has('warning'))

                <div class="alert alert-warning alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{Session::get('warning')}}
                </div>
            @endif

            @if(Session::has('success'))

            <div class="container">

                  {!!Session::get('success')!!}
            </div>

            @endif

            <!--fail message -->
            @if(Session::has('fails') )

                <div class="alert alert-danger alert-dismissable" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    {{Session::get('fails')}}
                </div>

            @endif
            @if (count($errors) > 0)

                <div class="alert alert-danger alert-dismissable" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>

                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{!! $error !!}</li>
                        @endforeach
                    </ul>
                </div>

            @endif

            @include('themes.default1.front.domain')
            @yield('content')

        </div>

    </div>



    @auth


        <div class="modal fade" id="tenant" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    {!! Form::open() !!}
                    <div class="modal-header">
                        <h4 class="modal-title">{{trans('message.cloud_heading')}}</h4>
                    </div>

                    <div class="modal-body">
                        <div id="cloudsuccess">
                        </div>
                        <div id="clouderror">
                        </div>
                        <!-- Form  -->

                        <div class="container">
                            <form action="" method="post" style="width:500px; margin: auto auto;" class="card card-body">
                                <div class="form-group">
                                    <label><b>{{trans('message.cloud_field_label')}}</b></label>
                                    <div class="row" style="margin-left: 2px; margin-right: 2px;">

                                      <input type="text" name="domain" autocomplete="off" id="userdomain" class="form-control col col-7 rounded-0" placeholder="Domain" required>
                                      <input type="text" class="form-control col col-5 rounded-0" value=".fratergroup.in" disabled="true" style="background-color: #4081B5; color:white; border-color: #0088CC">
                                      <p id="validationMessage"></p>
                                    </div>

                                </div>
                                <label style="margin-top: 2px;"><b>Choose your desired cloud product</b></label>
                                <div class="row">
                                    <div class="col col-6">
                                        <div class="radio-option">
                                            <input type="radio" name="option" class="product" value="ServiceDesk" checked>
                                            <label style="margin-left: 2px;">Faveo ServiceDesk</label>
                                        </div>
                                    </div>
                                    <div class="col col-6">
                                        <div class="radio-option">
                                            <input type="radio" name="option" class="product" value="Helpdesk">
                                            <label style="margin-left: 2px;">Faveo HelpDesk</label>
                                        </div>
                                    </div>
                                </div>
                                <hr class="custom-line">
                                <div class="text-center">
                                <div class="row data-center">
                                    <div class="col col-12">
                                        <p>Your data center location is <b data-nearest-center="">United States </b><!--<a role="button" href="javascript:void(0)" data-center-link="" aria-labelledby="data-center-text-label-dataCenter119678097062480"><b>Change</b></a>--></p>
                                    </div>
                                </div>
                                </div>
                            </form>
                        </div>
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
                                    console.error('Error:', error);
                                }
                            });
                        });
                    </script>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left closebutton" id="closebutton" data-dismiss="modal"><i class="fa fa-times">&nbsp;&nbsp;</i>Close</button>
                        <button type="submit"  class="btn btn-primary createTenant" id="createTenant" onclick="firstlogin({{Auth::user()->id}})"><i class="fa fa-check">&nbsp;&nbsp;</i>Submit</button>

                        {!! Form::close()  !!}
                    </div>
                    <!-- /Form -->
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog-->
        </div><!-- /.modal -->

        <div class="modal fade" id="credit" tabindex="-1" role="dialog" aria-labelledby="creditLabel" aria-hidden="true">

            <div class="modal-dialog credit-dialog">
                <div class="modal-content credit-content">
                    <div class="modal-header credit-header">
                        <h4 class="modal-title credit-title">Credit Balance: {!! $formattedValue !!}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body credit-body">
                        <ul class="list-group">
                            <h6 class="modal-title">Credit Balance History</h6>
                            <br>
                            @php
                                $payment_id = \DB::table('payments')->where('user_id',\Auth::user()->id)->where('payment_method','Credit Balance')->where('payment_status','success')->value('id');
                                $payment_activity=\DB::table('credit_activity')->where('payment_id',$payment_id)->where('role','user')->orderBy('created_at', 'desc')->get();
                            @endphp
                            @if(!$payment_activity->isEmpty())
                                @foreach($payment_activity as $activity)
                                    <li class="list-group-item">
                                        {!! getDateHtml($activity->created_at) !!}
                                        <br>
                                        {!! $activity->text !!}
                                        <br>
                                    </li>
                                @endforeach
                            @else
                                <li class="list-group-item" style="text-align: center">No activity has been recorded for this credit so far.</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endauth


    <footer id="footer">

        <div class="container" >

            <div class="footer-ribbon"><span>Get in Touch</span></div>
            <div id="mailchimp-message"></div>
            <div class="row py-5 my-4" style = "margin-top: 50%;" >
                <?php
                $count  = \App\Model\Front\Widgets::where('publish', 1)->count();
                switch ($count) {
                    case '1':
                        $class = 12;
                        break;
                    case '2':
                        $class = 6;
                        break;
                    case '3':
                        $class = 4;
                        break;
                    case '4':
                        $class = 3;
                        break;
                    default:
                        $class = 12;
                        break;
                }
                $widgets = \App\Model\Front\Widgets::where('publish', 1)->where('type', 'footer1')->select('name','content','allow_tweets','allow_mailchimp','allow_social_media')->first();
                if ($widgets) {
                    $tweetDetails = $widgets->allow_tweets ==1 ?  '<div id="tweets" class="twitter" >
                            </div>' : '';
                }
                $mailchimpKey = \App\Model\Common\Mailchimp\MailchimpSetting::value('api_key');
                ?>
                @if($widgets != null)
                    @component('mini_views.footer_widget', ['title'=> $widgets->name, 'colClass'=>"col-md-6 col-lg-$class mb-$class mb-lg-0"])
                        <p class="pr-1"> {!! $widgets->content !!}</p>
                        {!! $tweetDetails !!}




                        <div class="alert alert-danger d-none" id="newsletterError"></div>
                        @if($mailchimpKey != null && $widgets->allow_mailchimp ==1)
                            <div class="input-group input-group-rounded">
                                <input class="form-control form-control-sm" placeholder="Email Address" name="email" id="newsletterEmail" type="text">
                                <span class="input-group-append">
                                    <button class="btn btn-light text-color-dark" id="mailchimp-subscription" type="submit"><strong>Go!</strong></button>
                                </span>
                            </div>
                        @endif
                        @if($widgets->allow_social_media)
                            <ul class="social-icons">
                                @foreach($social as $media)
                                    <li class="social-icons-{{lcfirst($media->name)}}"><a href="{{$media->link}}" target="_blank" title="{{ucfirst($media->name)}}"><i class="fa fa-{{lcfirst($media->name)}}"></i></a></li>

                                @endforeach
                            </ul>
                        @endif
                    @endcomponent
                @endif
                <?php

                $widgets = \App\Model\Front\Widgets::where('publish', 1)->where('type', 'footer2')->select('name','content','allow_tweets','allow_mailchimp','allow_social_media')->first();
                if ($widgets) {
                    $tweetDetails =  $widgets->allow_tweets ==1 ?  '<div id="tweets" class="twitter" >
                            </div>' : '';
                }
                ?>
                @if($widgets != null)
                    @component('mini_views.footer_widget', ['title'=> $widgets->name,'colClass'=>"col-md-6 col-lg-$class mb-$class mb-lg-0"])
                        <p class="pr-1"> {!! $widgets->content !!}</p>
                        {!! $tweetDetails !!}
                        @if($mailchimpKey != null && $widgets->allow_mailchimp ==1)
                            <div class="input-group input-group-rounded">
                                <input class="form-control form-control-sm" placeholder="Email Address" name="email" id="newsletterEmail" type="text">
                                <span class="input-group-append">
                                    <button class="btn btn-light text-color-dark" id="mailchimp-subscription" type="submit"><strong>Go!</strong></button>
                                </span>
                            </div>
                        @endif
                        <br>
                        @if($widgets->allow_social_media)
                            <ul class="social-icons">
                                @foreach($social as $media)

                                    <li class="social-icons-{{lcfirst($media->name)}}"><a href="{{$media->link}}" target="_blank" title="{{ucfirst($media->name)}}"><i class="fa fa-{{lcfirst($media->name)}}"></i></a></li>

                                    heelo
                                @endforeach
                            </ul>
                        @endif
                    @endcomponent
                @endif
                <?php
                $widgets = \App\Model\Front\Widgets::where('publish', 1)->where('type', 'footer3')->select('name','content','allow_tweets','allow_mailchimp','allow_social_media')->first();
                if ($widgets) {
                    $tweetDetails = $widgets->allow_tweets   ==1 ?  '<div id="tweets" class="twitter" >
                            </div>' : '';
                }


                ?>
                @if($widgets != null)
                    @component('mini_views.footer_widget', ['title'=> $widgets->name,'colClass'=>"col-md-6 col-lg-$class mb-$class mb-lg-0"])
                        <p class="pr-1"> {!! $widgets->content !!}</p>
                        {!! $tweetDetails !!}
                        @if($mailchimpKey != null && $widgets->allow_mailchimp ==1)
                            <div class="input-group input-group-rounded">
                                <input class="form-control form-control-sm" placeholder="Email Address" name="email" id="newsletterEmail" type="text">
                                <span class="input-group-append">
                                    <button class="btn btn-light text-color-dark" id="mailchimp-subscription" type="submit"><strong>Go!</strong></button>
                                </span>
                            </div>
                        @endif
                        <br>
                        @if($widgets->allow_social_media)
                            <ul class="social-icons">
                                @foreach($social as $media)

                                    <li class="social-icons-{{lcfirst($media->name)}}"><a href="{{$media->link}}" target="_blank" title="{{ucfirst($media->name)}}"><i class="fa fa-{{lcfirst($media->name)}}"></i></a></li>

                                @endforeach
                            </ul>
                        @endif
                        <!-- @if($set->company_email != NULL)
                            <li class="mb-1">
                                <i class="fas fa-envelope"></i>
                                <p class="m-0">
                                    <a style="color: inherit" href="mailto:{{$set->company_email}}">{{$set->company_email}}</a>
                                         </p>
                                     </li>
                                 @endif
                        @if($set->phone != NULL)
                            <li class="mb-1">
                                <i class="fas fa-phone"></i>
                                <p class="m-0">
                                    <a style="color: inherit" href="tel:{{$set->phone}}">{{$set->phone}}</a>
                                         </p>
                                     </li>
                                 @endif
                        @if($set->address != NULL)
                            <li class="mb-1">
                                <i class="fa fa-map-marker"></i>
                                <p class="m-0">{{$set->address}}</p>
                                     </li>
                                 @endif -->
                    @endcomponent
                @endif

                <?php

                $widgets = \App\Model\Front\Widgets::where('publish', 1)->where('type', 'footer4')->select('name','content','allow_tweets','allow_mailchimp','allow_social_media')->first();
                if ($widgets) {
                    $tweetDetails = $widgets->allow_tweets   ==1 ?  '<div id="tweets" class="twitter" >
                            </div>' : '';

                }
                ?>

                @if($widgets != null)
                    @component('mini_views.footer_widget', ['title'=> $widgets->name,'colClass'=>"col-md-6 col-lg-$class mb-$class mb-lg-0"])
                        <p class="pr-1"> {!! $widgets->content !!}</p>
                        {!! $tweetDetails !!}
                        @if($mailchimpKey != null && $widgets->allow_mailchimp ==1)
                            <div class="input-group input-group-rounded">
                                <input class="form-control form-control-sm" placeholder="Email Address" name="email" id="newsletterEmail" type="text">
                                <span class="input-group-append">
                                    <button class="btn btn-light text-color-dark" id="mailchimp-subscription" type="submit"><strong>Go!</strong></button>
                                </span>
                            </div>
                        @endif
                        <br>
                        @if($widgets->allow_social_media)
                            <ul class="social-icons">
                                @foreach($social as $media)
                                    <li class="social-icons-{{lcfirst($media->name)}}"><a href="{{$media->link}}" target="_blank" title="{{ucfirst($media->name)}}"><i class="fa fa-{{lcfirst($media->name)}}"></i></a></li>

                                @endforeach
                            </ul>
                        @endif
                    @endcomponent
                @endif

            </div>
        </div>

        <div class="footer-copyright">
            <div class="container py-2">
                <div class="row py-4">
                    <div class="col-md-12 align-items-center justify-content-center justify-content-lg-start mb-2 mb-lg-0">
                        <p>Copyright ©<?php echo date('Y') ?> . <a href="{{$set->website}}" target="_blank">{{$set->company}}</a>. All Rights Reserved.Powered by
                            <a href="https://www.ladybirdweb.com/" target="_blank"><img src="{{asset('storage/common/images/Ladybird1.png')}}" alt="Ladybird"></a></p>
                    </div>

                </div>
            </div>
        </div>
    </footer>
</div>


<!-- Vendor -->

<script src="{{asset('client/js/jquery.min.js')}}"></script>
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

<script src="{{asset('client/js/bootstrap.min.js')}}"></script>
<script src="{{asset('client/js/common.min.js')}}"></script>
<script src="{{asset('client/js/jquery.validation.min.js')}}"></script>
<script src="{{asset('client/js/jquery.easy-pie-chart.min.js')}}"></script>
<script src="{{asset('client/js/jquery.gmap.min.js')}}"></script>
<script src="{{asset('client/js/jquery.lazyload.min.js')}}"></script>
<script src="{{asset('client/js/jquery.isotope.min.js')}}"></script>
<script src="{{asset('client/js/owl.carousel.min.js')}}"></script>
<script src="{{asset('client/js/jquery.magnific-popup.min.js')}}"></script>
<script src="{{asset('client/js/vide.min.js')}}"></script>

<!-- Theme Base, Components and Settings -->
<script src="{{asset('client/porto/js-1/theme.js')}}"></script>

<!-- any custom js/effects can be defined in this -->
<script src="{{asset('client/porto/js/custom.js')}}"></script>

<!-- Theme Initialization Files -->
<script src="{{asset('client/porto/js-1/theme.init.js')}}"></script>
<script src="{{asset('common/js/intlTelInput.js')}}"></script>

<script type="text/javascript">
    var csrfToken = $('[name="csrf_token"]').attr('content');

    setInterval(refreshToken, 360000); // 1 hour

    function refreshToken(){
        $.post('refresh-csrf').done(function(data){
            csrfToken = data; // the new token
        });
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>


<script>

    $('#mailchimp-subscription').click(function(){
        var email = $('#newsletterEmail').val();
        $('#mailchimp-subscription').html("<i class='fa fa-circle-o-notch fa-spin fa-1x fa-fw'></i> Please Wait...");
        $.ajax({
            type: 'POST',
            url: '{{url("mail-chimp/subcribe")}}',
            data: {'email' : email,'_token': "{!! csrf_token() !!}"},
            success: function(data){
                $("#mailchimp-subscription").html("Go");
                $('#mailchimp-message').show();
                var result =  '<br><div class="alert alert-success "><strong><i class="fa fa-check"></i> Success! </strong>'+data.message+'.</div>';
                $('#mailchimp-message').show();
                $('#mailchimp-message').html(result+ ".");
                setInterval(function(){
                    $('#mailchimp-message').slideUp(5000);

                }, 2000);
            },
            error: function(response) {
                if(response.status == 400) {
                    var myJSON = response.responseJSON.message
                    $("#mailchimp-subscription").html("Go");
                    var html =  '<br><div class="alert alert-warning"><strong> Whoops! </strong>'+myJSON+'.</div>';
                    $('#mailchimp-message').show();
                    document.getElementById('mailchimp-message').innerHTML = html;
                    setInterval(function(){
                        $('#mailchimp-message').slideUp(5000);

                    }, 2000);
                } else {
                    var myJSON = response.responseJSON.errors;
                    $("#mailchimp-subscription").html("Go");
                    var html = '<br><div class="alert alert-danger"><strong>Whoops! </strong>Something went wrong<ul>';
                    for (var key in myJSON)
                    {
                        html += '<li>' + myJSON[key][0] + '</li>'
                    }
                    html += '</ul></div>';

                    $('#mailchimp-message').show();
                    document.getElementById('mailchimp-message').innerHTML = html;
                    setInterval(function(){
                        $('#mailchimp-message').slideUp(5000);

                    }, 1000);
                }

            }
        })
    })

    $.ajax({
        type: 'GET',
        url: "{{route('twitter')}}",
        dataType: "html",
        success: function (returnHTML) {
            $('.twitter').html(returnHTML);

        }
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
        $("#createTenant").html("<i class='fas fa-circle-notch fa-spin'></i> Please Wait...");
        var domain = $('#userdomain').val();
        var password = $('#password').val();
        var product = $('input[name="option"]:checked').val();

        $.ajax({
            type: 'POST',
            data: {'id':id,'password': password,'domain' : domain,'product':product},
            url: "{{url('first-login')}}",
            success: function (data) {
                $('#createTenant').attr('disabled',false)
                $("#createTenant").html("<i class='fa fa-check'>&nbsp;&nbsp;</i>Submit");
                if(data.status == 'validationFailure') {

                    var html = '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Whoops! </strong>Something went wrong<ul>';
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
                    var result =  '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Whoops! </strong>Something went wrong!!<br><ul><li>'+data.message+'</li></ul></div>';
                    $('#clouderror').html(result);
                } else if(data.status == 'success_with_warning') {
                    console.log('here');
                    $('#clouderror').show();
                    $('#cloudsuccess').hide();
                    var result =  '<div class="alert alert-warning alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Whoops! </strong><br><ul><li>'+data.message+'</li></ul></div>';
                    $('#clouderror').html(result);
                } else {
                    $('#clouderror').hide();
                    $('#cloudsuccess').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Success! </strong>'+data.message+'!</div>';
                    $('#cloudsuccess').html(result);
                }
            },error: function (response) {
                $('#createTenant').attr('disabled',false)
                $("#createTenant").html("<i class='fa fa-check'>&nbsp;&nbsp;</i>Submit");
                $("#generate").html("<i class='fa fa-check'>&nbsp;&nbsp;</i>Submit");
                if(response.status == 422) {

                    var html = '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Whoops! </strong>Something went wrong<ul>';
                    for (var key in response.responseJSON.errors)
                    {
                        html += '<li>' + response.responseJSON.errors[key][0] + '</li>'
                    }

                } else {
                    var html = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Whoops! </strong>Something went wrong<ul>';
                    html += '<li>' + response.responseJSON.message + '</li>'
                }

                html += '</ul></div>';
                $('#clouderror').show();
                $('#cloudsuccess').hide();
                document.getElementById('error').innerHTML = html;

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
    validationMessage.textContent = "Domain must be 28 characters or less.";
    validationMessage.style.color = "red";
  } else {
    validationMessage.textContent = "";
    validationMessage.style.color = "";
  }
});


</script>
@yield('script')

<!--Start of Tawk.to Script-->
<!--Start of Tawk.to Script-->

<script type="text/javascript">
    {!! html_entity_decode($everyPageScript) !!}


</script>
@if(request()->path() !== 'my-profile' && request()->path() !== 'verify')
       
        <script type="text/javascript">
    
    
            var demotelInput = $('#mobilenumdemo'),
            errorMsgdemo = document.querySelector("#error-msgdemo"),
            validMsgdemo = document.querySelector("#valid-msgdemo"),
            addressDropdowndemo = $("#country");
        var errorMapdemo = [ "Invalid number", "Invalid country code", "Number Too short", "Number Too long", "Invalid number"];

        demotelInput.intlTelInput({
            geoIpLookup: function (callback) {
                $.get("https://ipinfo.io", function () {}, "jsonp").always(function (resp) {
                    var countryCodedemo = (resp && resp.country) ? resp.country : "";
                    callback(countryCodedemo);
                });
            },
            initialCountry: "auto",
            separateDialCode: true,
            utilsScript: "{{asset('js/intl/js/utils.js')}}"
        });
        var resetdemo = function() {
            errorMsgdemo.innerHTML = "";
            errorMsgdemo.classList.add("hide");
            validMsgdemo.classList.add("hide");
        };

        $('.intl-tel-input').css('width', '100%');

        demotelInput.on('blur', function () {
            resetdemo();
            if ($.trim(demotelInput.val())) {
                if (demotelInput.intlTelInput("isValidNumber")) {
                    $('#mobilenumdemo').css("border-color","");
                    $("#error-msgdemo").html('');
                    errorMsgdemo.classList.add("hide");
                    $('#register').attr('disabled',false);
                } else {
                    var errorCodedemo = demotelInput.intlTelInput("getValidationError");
                    errorMsgdemo.innerHTML = errorMapdemo[errorCodedemo];
                    $('#mobile_codecheckdemo').html("");

                    $('#mobilenumdemo').css("border-color","red");
                    $('#error-msgdemo').css({"color":"red","margin-top":"5px"});
                    errorMsgdemo.classList.remove("hide");
                    $('#register').attr('disabled',true);
                }
            }
        });
        $('input').on('focus', function () {
            $(this).parent().removeClass('has-error');
        });
        addressDropdowndemo.change(function() {
            demotelInput.intlTelInput("setCountry", $(this).val());
            if ($.trim(demotelInput.val())) {
                if (demotelInput.intlTelInput("isValidNumber")) {
                    $('#mobilenumdemo').css("border-color","");
                    $("#error-msgdemo").html('');
                    errorMsgdemo.classList.add("hide");
                    $('#register').attr('disabled',false);
                } else {
                    var errorCodedemo = demotelInput.intlTelInput("getValidationError");
                    errorMsgdemo.innerHTML = errorMapdemo[errorCodedemo];
                    $('#mobile_codecheckdemo').html("");

                    $('#mobilenumdemo').css("border-color","red");
                    $('#error-msgdemo').css({"color":"red","margin-top":"5px"});
                    errorMsgdemo.classList.remove("hide");
                    $('#register').attr('disabled',true);
                }
            }
        });

        $('form').on('submit', function (e) {
            $('input[name=country_code]').attr('value', $('.selected-dial-code').text());
        });




</script>
@endif
        <style>
            .custom-line {
                border: none;
                border-top: 1px solid #ccc;
                margin: 10px 0;
            }
            #validationMessage {
              position: absolute;
              top: 80px; /* Adjust this value to align the error message properly */
              margin-left:32px;
              left: 0;
              font-size: 12px;
              color: red;
}


                 /* Custom styles */
             .btn-credit {
                 font-size: 14px;
                 padding: 5px 10px;
             }
            .credit-dialog {
                max-width: 600px;
            }
            .credit-content {
                border: none;
                border-radius: 10px;
            }
            .credit-header {
                border-bottom: none;
            }
            .credit-title {
                font-size: 18px;
            }
            .credit-body {
                max-height: 400px;
                overflow-y: auto;
            }
            .list-group-item {
                border: none;
                padding: 8px 15px;
                margin-bottom: 5px; /* Add margin between list items */
                background-color: #f8f9fa; /* Light gray background */
                border-radius: 5px; /* Rounded corners */
                box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            }

        </style>
</body>
</html>
@yield('end')
