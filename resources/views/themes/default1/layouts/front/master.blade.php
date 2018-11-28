<?php $setting = \App\Model\Common\Setting::where('id', 1)->first();
$script = \App\Model\Common\ChatScript::where('id', 1)->first(); 
if($script){
  $script = $script->script;
}else{
  $script = null;
}
 ?>
<!DOCTYPE html>
<html>


    <head>
  
          <!-- Basic -->
          <meta charset="utf-8">
          <meta http-equiv="X-UA-Compatible" content="IE=edge">  
  
          <title>@yield('title') | {{$setting->favicon_title_client}}</title>  
  
          <meta name="keywords" content="HTML5 Template" />
          <meta name="description" content="Register, signup here to start using Faveo Helpdesk or signin to your existing account">
          <meta name="author" content="okler.net">
  
          <!-- Favicon -->
          <!-- <img src='{{ asset("images/$setting->logo")}}' class="img-rounded" alt="Admin-Logo"  height="45"> -->
          <link rel="shortcut icon" href='{{asset("images/favicon/$setting->fav_icon")}}' type="image/x-icon" />
          <link rel="apple-touch-icon" href="img/apple-touch-icon.png">
  
          <!-- Mobile Metas -->
          <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, shrink-to-fit=no">
  
          <!-- Web Fonts  -->
          <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800%7CShadows+Into+Light" rel="stylesheet" type="text/css">
          
  
          <!-- Vendor CSS -->
          <link rel="stylesheet" href="{{asset('vendor/bootstrap/css/bootstrap.min.css')}}">
          <link rel="stylesheet" href="{{asset('vendor/font-awesome/css/fontawesome-all.min.css')}}">
          <link rel="stylesheet" href="{{asset('cart/vendor/font-awesome/css/font-awesome.min.css')}}">
          <link rel="stylesheet" href="{{asset('vendor/animate/animate.min.css')}}">
          <link rel="stylesheet" href="{{asset('vendor/simple-line-icons/css/simple-line-icons.min.css')}}">
          <link rel="stylesheet" href="{{asset('vendor/owl.carousel/assets/owl.carousel.min.css')}}">
          <link rel="stylesheet" href="{{asset('vendor/owl.carousel/assets/owl.theme.default.min.css')}}">
          <link rel="stylesheet" href="{{asset('vendor/magnific-popup/magnific-popup.min.css')}}">
  
          <!-- Theme CSS -->
          <link rel="stylesheet" href="{{asset('css/theme.css')}}">
          <link rel="stylesheet" href="{{asset('css/theme-elements.css')}}">
          <link rel="stylesheet" href="{{asset('css/theme-blog.css')}}">
          <link rel="stylesheet" href="{{asset('css/theme-shop.css')}}">
          
          <!-- Demo CSS -->
          <link rel="stylesheet" href="{{asset('css/demos/demo-construction.css')}}">
  
          <!-- Skin CSS -->
          <link rel="stylesheet" href="{{asset('css/skins/skin-construction.css')}}"> 
           <link rel="stylesheet" href="{{asset('js/intl/css/intlTelInput.css')}}">
          <link rel="stylesheet" href="{{asset('css/skins/default.css')}}">

  
          <!-- Theme Custom CSS -->
          <link rel="stylesheet" href="{{asset('css/custom.css')}}">
  
          <!-- Head Libs -->
          <script src="{{asset('vendor/modernizr/modernizr.min.js')}}"></script>

             <script type="text/javascript"> //<![CDATA[ 

        var tlJsHost = ((window.location.protocol == "https:") ? "https://secure.comodo.com/" : "https://www.trustlogo.com/");
        document.write(unescape("%3Cscript src='" + tlJsHost + "trustlogo/javascript/trustlogo.js' type='text/javascript'%3E%3C/script%3E"));
        //]]>
       </script>

         
    
  
      </head>

    <body>

        <?php
        $domain = [];
        $set = new \App\Model\Common\Setting();
        $set = $set->findOrFail(1);
        ?>
        <div class="body">
            <header id="header" data-plugin-options="{'stickyEnabled': true, 'stickyEnableOnBoxed': true, 'stickyEnableOnMobile': true, 'stickyStartAt': 55, 'stickySetTop': '-55px', 'stickyChangeLogo': true}">
        <div class="header-body">
                <div class="header-body">
                    <div class="header-container container">
                        <div class="header-row">
                            <div class="header-column">
                                <div class="header-row">
                                <div class="header-logo">
                                    <a href="{{url('home')}}">
                                        <img alt="Porto" width="111" height="54" data-sticky-width="82" data-sticky-height="40" data-sticky-top="33" src="{{asset('images/logo/'.$setting->logo)}}">
                                    </a>
                                </div>
                              </div>
                            </div>
                             <div class="header-column justify-content-end">
                                <div class="header-row pt-3">
                                    <nav class="header-nav-top">
                                          <ul class="nav nav-pills">
                                            @if($set->company_email != NULL)
                                              <li class="nav-item d-none d-sm-block">
                                                  <a class="nav-link" href="mailto:{{$set->company_email}}"><i class="fas fa-envelope"></i> {{$set->company_email}}</a>
                                              </li>
                                              @endif
                                              @if($set->phone != NULL)
                                              <li class="nav-item">
                                                  <span class="ws-nowrap"><i class="fas fa-phone"></i>{{$set->phone}}</span>
                                              </li>
                                              @endif
                                          </ul>
                                      </nav>
                                    <!--                                    <nav class="header-nav-top">
                                                                            <ul class="nav nav-pills">
                                                                                <li class="hidden-xs">
                                                                                    <a href="about-us.html"><i class="fa fa-angle-right"></i> About Us</a>
                                                                                </li>
                                                                                <li class="hidden-xs">
                                                                                    <a href="contact-us.html"><i class="fa fa-angle-right"></i> Contact Us</a>
                                                                                </li>
                                                                                <li>
                                                                                    <span class="ws-nowrap"><i class="fa fa-phone"></i> (123) 456-789</span>
                                                                                </li>
                                                                            </ul>
                                                                        </nav>-->
                                </div>
                                  <div class="header-row">
                                    <div class="header-nav">
                                        <button class="btn header-btn-collapse-nav" data-toggle="collapse" data-target=".header-nav-main">
                                            <i class="fa fa-bars"></i>
                                        </button>
                                      
 
                                       <div class="header-nav-main header-nav-main-effect-1 header-nav-main-sub-effect-1">
                                            <nav class="collapse">
                                                <ul class="nav nav-pills" id="mainNav">
                                                    <li class="dropdown">
                                                        <a  class="nav-link" href="{{url('home')}}">
                                                            pricing
                                                        </a>

                                                    </li>
                                                    <li class="dropdown dropdown-mega">
                                                        <a class="nav-link" href="{{url('contact-us')}}">
                                                            contact us
                                                        </a>

                                                    </li>
 
                                                    <?php $pages = \App\Model\Front\FrontendPage::where('publish', 1)->where('hidden','!=',1)->get(); ?>

                                                    @foreach($pages as $page)
                                                    <li class="dropdown">

                                                        @if($page->parent_page_id==0)
                                                        <?php
                                                        $ifdrop = \App\Model\Front\FrontendPage::where('publish', 1)->where('parent_page_id', $page->id)->count();
                                                        if ($ifdrop > 0) {
                                                            $class = 'dropdown-toggle';
                                                        } else {
                                                            $class = '';
                                                        }
                                                        ?>
                                                        <a class="{{$class}}" href="{{$page->url}}">
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

 

                                                    @if(!Auth::user())
                                                    <li class="dropdown">
                                                        <a  class="nav-link"  href="{{url('auth/login')}}">
                                                            Login
                                                        </a>
                                                    </li>

                                                    @else 
                                                    <li class="dropdown">
                                                        <a class="nav-link" class="dropdown-toggle" href="#">
                                                            {{Auth::user()->first_name}}
                                                            &nbsp;<i class="fas fa-caret-down"></i>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            @if(Auth::user()->role=='admin')
                                                            <li><a class="nav-link" href="{{url('/')}}">My Account</a></li>
                                                            @else 
                                                            <li><a class="nav-link" href="{{url('my-invoices')}}">My Account</a></li>
                                                            @endif
                                                            <li><a class="nav-link" href="{{url('auth/logout')}}">Logout</a></li>
                                                        </ul>
                                                    </li>
                                                    @endif

                                                    <li class="dropdown dropdown-mega dropdown-mega-shop" id="headerShop">
                                                        <a class="dropdown-item dropdown-toggle" href="{{url('show/cart')}}">
                                                            <i class="fa fa-user mr-1"></i> Cart ({{Cart::getTotalQuantity()}})
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <div class="dropdown-mega-content">
                                                                    <table class="cart">
                                                                        <tbody>
                                                                        @forelse(Cart::getContent() as $key=>$item)
                                                                             
                                                                            <?php
                                                                            $product = App\Model\Product\Product::where('id', $item->id)->first();
                                                                            if ($product->require_domain == 1) {
                                                                                $domain[$key] = $item->id;
                                                                            }
                                                                            // dd('sads')
                                                                            $cart_controller = new \App\Http\Controllers\Front\CartController();
                                                                            $currency = $cart_controller->currency();
                                                                            $currency =  $currency['currency'];
                                                                          ?>
                                                                            <tr>

                                                                                <td class="product-thumbnail">
                                                                                    <img width="100" height="100" alt="{{$product->name}}" class="img-responsive" src="{{$product->image}}">
                                                                                </td>

                                                                                <td class="product-name">
                                                                                        
                                                                                    <a>{{$item->name}}<br><span class="amount"><strong><small>{{$currency}}</small> {{App\Http\Controllers\Front\CartController::rounding($item->getPriceSumWithConditions())}}</strong></span></a>
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

                                                                            <tr>
                                                                                <td><a href="{{url('home')}}">Choose a Product</a></td>
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
                                                    </li>


                                                </ul>
                                            </nav>
                                        </div>
                                          <ul class="header-social-icons social-icons hidden-xs">
                                            <?php
                                            $social = App\Model\Common\SocialMedia::get();
                                            ?>
                                            @foreach($social as $media)
                                            <li class="{{$media->class}}"><a href="{{$media->link}}" target="_blank" title="{{ucfirst($media->name)}}"><i class="{{$media->fa_class}}"></i></a></li>
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

                    <section class="page-header page-header-light page-header-more-padding">
                    <div class="container">
                         <div class="row align-items-center">
                            <div class="col-lg-6">
                              
                                    @yield('page-heading')
                                    <!--<li><a href="#">Home</a></li>
                                    <li class="active">Pages</li>-->
                               
                            </div>
                            <div class="col-lg-6">
                                  <ul class="breadcrumb">
                                        @yield('breadcrumb')
                                  </ul>
                              </div>
                        </div>
                       <!--  <div class="row">
                            <div class="col-md-12">
                                <h1>@yield('page-heading')</h1>
                            </div>
                        </div> -->
                    </div>
                </section>

                <div class="container">

                    @if(Session::has('warning'))
                    <div class="alert alert-warning alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        {{Session::get('warning')}}
                    </div>
                    @endif
                    @include('themes.default1.front.domain')
                    @yield('content')

                </div>

            </div>

            <footer id="footer">
                <div class="container">
                    <div class="row">
                        <!-- <div class="footer-ribbon" style="background-color:#E9EFF2 !important">
                            <span>Get in Touch</span>
                        </div> -->
                         <?php $widgets = \App\Model\Front\Widgets::where('publish', 1)->where('type', 'footer1')->first(); ?>
                           @if($widgets != null)
                        <div class="col-md-3">
                           
                          <div class="newsletter">
                                <h4>{{ucfirst($widgets->name)}}</h4>
                                <p> {!! $widgets->content !!}</p>

                                <div class="alert alert-success d-none" id="newsletterSuccess">
                                    <strong>Success!</strong> You've been added to our email list.
                                </div>

                                <div class="alert alert-danger d-none" id="newsletterError"></div>
                                <?php $mailchimpKey = \App\Model\Common\Mailchimp\MailchimpSetting::find(1);?>
                                @if($mailchimpKey != null)
                                {!! Form::open(['url'=>'mail-chimp/subcribe','method'=>'GET']) !!}
                                <div class="input-group">
                                    <input class="form-control" placeholder="Email Address" name="email" id="newsletterEmail" type="text">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="submit">Go!</button>
                                    </span>
                                </div>
                                {!! Form::close() !!}
                                @endif
                            </div>
                        </div>
                        @endif
                          <?php $widgets = \App\Model\Front\Widgets::where('publish', 1)->where('type', 'footer2')->first(); ?>
                           @if($widgets != null)
                        <div class="col-md-3">
                            <h4>{{ucfirst($widgets->name)}}</h4>
                             <p> {!! $widgets->content !!}</p>
                            <div id="tweets" class="twitter" >
                            <p>Please wait...</p>
                            </div>
                        </div>
                        @endif
                        <?php $widgets = \App\Model\Front\Widgets::where('publish', 1)->where('type', 'footer3')->first(); ?>
                       @if($widgets != null)
                        <div class="col-md-3">
                            <div class="contact-details">
                                <h4>{{ucfirst($widgets->name)}}</h4>
                                {!! $widgets->content !!}
                            </div>
                        </div>
                         @endif
                         <?php $widgets = \App\Model\Front\Widgets::where('publish', 1)->where('type', 'footer4')->first(); ?>
                         
                        <div class="col-md-2">
                          @if($widgets != null)
                            <h4>{{ucfirst($widgets->name)}}</h4>
                             <p> {!! $widgets->content !!}</p>
                            <ul class="social-icons">
                                @foreach($social as $media)
                                <li class="{{$media->class}}"><a href="{{$media->link}}" target="_blank" title="{{ucfirst($media->name)}}"><i class="{{$media->fa_class}}"></i></a></li>
                                @endforeach
                            </ul>
                          @endif
                            <br>
                       
                        <a href="https://secure.comodo.com/ttb_searcher/trustlogo?v_querytype=W&v_shortname=CL1&v_search=https://www.billing.faveohelpdesk.com/&x=6&y=5"><img class="img-responsive" alt="" title="" src="https://www.faveohelpdesk.com/wp-content/uploads/2017/07/comodo_secure_seal_113x59_transp.png" /></a>
                        <br/> <br/>
                        <a href="https://monitor203.sucuri.net/m/verify/?r=ce48118f19b0feaecb9d46ac593fd041b2a8e31e15"><img class="img-responsive" alt="" title="" src="https://www.faveohelpdesk.com/wp-content/uploads/2017/07/index.gif" /></a>
                  </div>
                  
                </div>
                <div class="footer-copyright">
                    <div class="container">
                        <div class="row">


                            <div class="col-md-12">
                                <p>Copyright 漏 <?php echo date('Y') ?> 路 <a href="{{$set->website}}" target="_blank">{{$set->company}}</a>. All Rights Reserved.Powered by 
                                    <a href="https://www.ladybirdweb.com/" target="_blank"><img src="{{asset('dist/img/Ladybird1.png')}}" alt="Ladybird"></a></p>
                            </div>


                        </div>
                    </div>
                </div>
            </footer>
        </div>

        <!-- Vendor -->
    </script>
         <script>{!! NoCaptcha::renderJs('fr', true, 'recaptchaCallback') !!}  </script>
        <script src="{{asset('vendor/jquery/jquery.min.js')}}"></script>
          <script src="{{asset('vendor/jquery.appear/jquery.appear.min.js')}}"></script>
          <script src="{{asset('vendor/jquery.easing/jquery.easing.min.js')}}"></script>
          <script src="{{asset('vendor/jquery-cookie/jquery-cookie.min.js')}}"></script>
          <script src="{{asset('vendor/popper/umd/popper.min.js')}}"></script>
          <script src="{{asset('vendor/bootstrap/js/bootstrap.min.js')}}"></script>
          <script src="{{asset('vendor/common/common.min.js')}}"></script>
          <script src="{{asset('vendor/jquery.validation/jquery.validation.min.js')}}"></script>
          <script src="{{asset('vendor/jquery.easy-pie-chart/jquery.easy-pie-chart.min.js')}}"></script>
          <script src="{{asset('vendor/jquery.gmap/jquery.gmap.min.js')}}"></script>
          <script src="{{asset('vendor/jquery.lazyload/jquery.lazyload.min.js')}}"></script>
          <script src="{{asset('vendor/isotope/jquery.isotope.min.js')}}"></script>
          <script src="{{asset('vendor/owl.carousel/owl.carousel.min.js')}}"></script>
          <script src="{{asset('vendor/magnific-popup/jquery.magnific-popup.min.js')}}"></script>
          <script src="{{asset('vendor/vide/vide.min.js')}}"></script>
              <script src="{{asset('css/jquery/jquery.min.js')}}"></script>
          <script src="{{asset('css/bootstrap/js/bootstrap.min.js')}}"></script>
          <!-- Theme Base, Components and Settings -->
          <script src="{{asset('js/theme.js')}}"></script>
          <!-- Theme Custom -->
          <script src="{{asset('js/custom.js')}}"></script>
          
          <!-- Theme Initialization Files -->
          <script src="{{asset('js/theme.init.js')}}"></script>
          <script src="{{asset('js/intl/js/intlTelInput.js')}}"></script>

        <script>
         
    
                         $.ajax({
                          type: 'GET',
                           url: "{{route('twitter')}}",
                           dataType: "html",
                             success: function (returnHTML) {
                                   $('#tweets').html(returnHTML);
                                    
                                }
                          });
                      

        
         function removeItem(id) {

                         $.ajax({
                        type: "GET",
                     data:"id=" + id,
                url: "{{url('cart/remove/')}}",
                        success: function (data) {
                            location.reload();
                                                 }
                                });
                                }


        </script>
        @yield('script')
        <!-- Google Analytics: Change UA-XXXXX-X to be your site's ID. Go to http://www.google.com/analytics/ for more information.
        <script>
                (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
        
                ga('create', 'UA-12345678-1', 'auto');
                ga('send', 'pageview');
        </script>
        -->
<!--Start of Tawk.to Script-->
<!--Start of Tawk.to Script-->
<script type="text/javascript">
 {!! html_entity_decode($script) !!}

</script>

<!--End of Tawk.to Script-->
<!--End of Tawk.to Script-->

<!--  -->

    </body>
</html>
@yield('end')
