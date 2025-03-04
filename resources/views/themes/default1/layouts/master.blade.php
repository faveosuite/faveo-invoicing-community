<!DOCTYPE html>
  <?php
    $set = new \App\Model\Common\Setting();
    $set = $set->findOrFail(1);
    $page_count = DB::table('frontend_pages')->count();
    ?>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <meta name="csrf-token" content="{{ csrf_token() }}" />
        @if($set->fav_icon)
        <link rel="shortcut icon" href='{{ $set->fav_icon }}' type="image/x-icon" />
        @endif
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="x-ua-compatible" content="ie=edge">

        <title>@yield('title') | {{$set->favicon_title}}</title>
        <link rel="stylesheet" href="{{asset('admin/css-1/select2.min.css')}}">

        <link rel="stylesheet" href="{{asset('admin/css-1/all.min.css')}}">
        <!-- Ionicons -->
        <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
        <!-- Tempusdominus Bbootstrap 4 -->
        <link rel="stylesheet" href="{{asset('admin/css-1/tempusdominus-bootstrap-4.min.css')}}">
        <!-- iCheck -->
        <link rel="stylesheet" href="{{asset('admin/css-1/icheck-bootstrap.min.css')}}">
        <!-- JQVMap -->
        <link rel="stylesheet" href="{{asset('admin/css-1/jqvmap.min.css')}}">
        <!-- Theme style -->
        <link rel="stylesheet" href="{{asset('admin/css-1/adminlte.min.css')}}">
        <!-- overlayScrollbars -->
        <link rel="stylesheet" href="{{asset('admin/css-1/OverlayScrollbars.min.css')}}">
        <!-- Daterange picker -->
        <link rel="stylesheet" href="{{asset('admin/css-1/daterangepicker.css')}}">
        <!-- summernote -->
        <link rel="stylesheet" href="{{asset('admin/css-1/summernote-bs4.css')}}">
        <!-- Google Font: Source Sans Pro -->
        <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

        <link rel="stylesheet" href="{{asset('admin/css/icheck-bootstrap.min.css')}}">

        <!-- Custom css/js -->
        <link rel="stylesheet" href="{{ asset('common/intl-tel-input/css/intlTelInput.css') }}">

        <script src="{{asset('https://code.jquery.com/ui/1.12.1/jquery-ui.min.js')}}"></script>
        <script src="{{asset('https://code.jquery.com/jquery-3.5.1.min.js')}}"></script>
        <script src="{{ asset('js/admin/jquery.validate.js') }}"></script>
        <script>
        $(function () {
          $("input[data-bootstrap-switch]").each(function(){
              $(this).bootstrapSwitch('state', $(this).prop('checked'));
            });
        })
        </script>
        <script type="text/javascript">
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


        </script>
        <!-- AdminLTE Skins. Choose a skin from the css/skins
             folder instead of downloading all of them to reduce the load. -->


    </head>
    <style>
        .required:after {
            content:'*';
            color:red;
            padding-left:5px;
        }
        [type=search] {
        outline-offset: 0px;
        -webkit-appearance: none;
    }
    
   
    .table.dataTable thead th
    {
        padding: 8px 10px ;
    }

        /*This is added because of the eye icon is automatically added in edge browser*/
        input[type="password"]::-ms-reveal {
            display: none !important;
        }

        .dropdown-menu-arrow:before {
            content: ""!important;
            position: absolute!important;
            top: -10px!important;
            left: 88%;
            transform: translate(-50%);
            border-width: 3px 7px 8px;
            border-style: solid;
            border-color: transparent transparent #3e4d5d
        }

        .model-box {
            margin-top: 8px !important;
            margin-right: 20px !important;
            padding-top: 9px !important;
            width: 170px !important;
            height: 82px !important;
            background-color: #4f5962;
        }
        .dp-data {
            background-color: #4f5962;
            color: #c2c7d0 !important;
        }
        .dp-data:hover {
            background-color: rgba(0,0,0,0.2);
            color: #c2c7d0;
        }

        .dropdown-profile{
            right: 0;
            left: auto !important;
        }

    </style>
    <?php
    $set = new \App\Model\Common\Setting();
    $set = $set->findOrFail(1);
    ?>


    <!-- ADD THE CLASS fixed TO GET A FIXED HEADER AND SIDEBAR LAYOUT -->
    <!-- the fixed layout is not compatible with sidebar-mini -->
    <body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{url('client-dashboard')}}" class="nav-link">Go to Client Panel</a>
                </li>

            </ul>



            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Messages Dropdown Menu -->
                <li class="nav-item dropdown user-menu">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">
                        <?php
                        $user = \DB::table('users')->find(\Auth::user()->id);
                        ?>
                        <span class="d-none d-md-inline me-2">{{ucfirst(Auth::user()->first_name)}} {{ucfirst(Auth::user()->last_name)}}</span>
                        <img src="{{ Auth::user()->profile_pic }}" style="width:30px;height: 30px;" class="user-image img-circle shadow d-none d-md-inline" alt="User Image" />
                    </a>

                    <ul class="dropdown-menu dropdown-menu-sm dropdown-profile dropdown-menu-end rounded model-box text-white dropdown-menu-arrow mt-2" >
                        <li>
                            <a href="{{url('profile')}}" class="dropdown-item dp-data">
                                <i class="fa fa-user pr-2"></i>Profile</a>
                        </li>
                        <li>
                            <a href="{{url('auth/logout')}}" class="dropdown-item dp-data mb-4 mt-1">
                                <i class="fas fa-sign-out-alt pr-2"></i>Sign Out</a>
                        </li>
                    </ul>
                </li>

            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            
                @if ($set->admin_logo == '')
                    <!-- Brand Logo -->
                        <a href="{{url('/')}}" class="brand-link">

                            <span style="margin-left: 50px;" class="brand-text font-weight-light"><b>{{$set->title}}</b></span>
                        </a>
                @else
                        <a href="{{url('/')}}" class="brand-link">
                <span style="margin-left: 20px;" class="brand-text font-weight-light"><img style="width:54px; height: 50px;margin-left: 5.5rem;" src='{{ $set->admin_logo }}' alt="Admin-Logo" class="brand-image"
                     style="opacity: .8;"></span>
                        </a>
                @endif

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
               <!--  <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <a href="{{url('/clients/'.Auth::user()->id)}}"><img src="{{Auth::user()->profile_pic}}" class="img-circle elevation-2" alt="User Image"></a>
                    </div>
                    <div class="info">
                        <a href="{{url('/clients/'.Auth::user()->id)}}" class="d-block">{{ucfirst(Auth::user()->first_name)}} {{ucfirst(Auth::user()->last_name)}}</a>
                    </div>
                </div>
 -->
                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <!-- Add icons to the links using the .nav-icon class
                             with font-awesome or any other icon font library -->
                            
                        <li class="nav-item has-treeview">
                            <a href="{{url('/')}}" class="nav-link" id="dashboard">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>
                                    {{Lang::get('message.dashboard')}}
                                </p>
                            </a>

                        </li>
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-user"></i>
                                <p>
                                    Users
                                    <i class="fas fa-angle-left right"></i>
                                 </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{url('clients')}}" class="nav-link" id="all_user">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>All Users</p>
                                    </a>
                                </li>
                                
                                <li class="nav-item">
                                    <a href="{{url('clients/create')}}" class="nav-link" id="add_new_user">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Add New</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{url('deleted-users')}}" class="nav-link" id="soft_delete_user">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Suspended Users</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-chart-pie"></i>
                                <p>
                                    Orders
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{url('orders')}}" class="nav-link" id="all_order">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{Lang::get('message.all-orders')}}</p>
                                    </a>
                                </li>
                               <!--  <li class="nav-item">
                                    <a href="{{url('invoice/generate')}}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{Lang::get('message.add-new')}}</p>
                                    </a>
                                </li> -->
                            </ul>
                        </li>
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-paperclip"></i>
                                <p>
                                    {{Lang::get('message.invoices')}}
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{url('invoices')}}" class="nav-link" id="all_invoice">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{Lang::get('message.all-invoices')}}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{url('invoice/generate')}}" class="nav-link" id="add_invoice">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{Lang::get('message.add-new')}}</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa fa-fw fa-sticky-note"></i>
                                <p>
                                    {{Lang::get('message.pages')}}
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{url('pages')}}" class="nav-link" id="all_page">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{Lang::get('message.all-pages')}}</p>
                                    </a>
                                </li>
                                @if($page_count <= 2)
                                <li class="nav-item">
                                    <a href="{{url('pages/create')}}" class="nav-link" id="all_new_page">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{Lang::get('message.add-new')}}</p>
                                    </a>
                                </li>
                                @endif

                                 <li class="nav-item">
                                    <a href="{{url('demo/page')}}" class="nav-link" id="demo_page">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{Lang::get('message.add-demo')}}</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa fa-fw fa-briefcase"></i>
                                <p>
                                    {{Lang::get('message.products')}}
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{url('products')}}" class="nav-link" id="all_product">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{Lang::get('message.all-products')}}</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{url('products/create')}}" class="nav-link" id="add_product">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{Lang::get('message.add-products')}}</p>
                                    </a>
                                </li>
                                
                                <li class="nav-item">
                                    <a href="{{url('plans')}}" class="nav-link" id="plan">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Plans</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{url('promotions')}}" class="nav-link" id="coupon">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{Lang::get('message.coupons')}}</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{url('groups')}}" class="nav-link" id="group">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{Lang::get('message.groups')}}</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-chart-line"></i>
                                <p>
                                    {{Lang::get('Reports')}}
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{url('reports/view')}}" class="nav-link" id="all_reports">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{Lang::get('All Reports')}}</p>
                                    </a>
                                </li>
                            </ul>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{url('records/column')}}" class="nav-link" id="add_col">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{Lang::get('Report settings')}}</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item has-treeview">
                            <a href="{{url('settings')}}" class="nav-link" id="setting">
                                <i class="nav-icon fa fa-fw fa-cogs"></i>
                                <p>
                                    {{Lang::get('message.settings')}}
                                </p>
                            </a>
                        </li>
                       



                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        @yield('content-header')
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    @if(Route::current()->getName() !== 'plans.index' && Route::current()->getName() !== 'tax.index' )
                        @if(isset($errors) && $errors->any())

                        @if (is_countable($errors) && count($errors) > 0)

                        <div class="alert alert-danger alert-dismissable" id="fail">
                            <strong>Whoops!</strong> There were some problems with your input.
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>


@endif
                    @endif
                    @endif

                    @if(Session::has('success'))
                        <div class="alert alert-success alert-dismissable" id="success">
                            <i class="fa fa-check"></i>
                            <b>{{Lang::get('message.success')}}!</b>
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            {{Session::get('success')}}
                        </div>
                    @endif

                    @if(Session::has('warning'))
                        <div class="alert alert-warning alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            {{Session::get('warning')}}
                        </div>
                    @endif
                <!-- fail message -->
                    @if(Session::has('fails'))
                        <div class="alert alert-danger alert-dismissable">
                            <i class="fa fa-ban"></i>
                            <b>{{Lang::get('message.alert')}}!</b> {{Lang::get('message.failed')}}.
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            {{Session::get('fails')}}
                        </div>
                    @endif


                    @yield('content')
                </div><!-- /.container-fluid -->
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
        <footer class="main-footer">
            <strong>Copyright &copy; {{date('Y')}} <a href="{{$set->website}}">{{$set->company}}</a>.</strong>
            All rights reserved. Powered by <a href="{{$set->website}}" class="text-color-grey text-color-hover-primary font-weight-bold" target="_blank">Faveo</a>
            <div class="float-right d-none d-sm-inline-block">
                <b>{{Lang::get('message.version')}}</b> {{Config::get('app.version')}}
            </div>
        </footer>


        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->


    <!-- Bootstrap 3.3.2 JS -->
    <script src="{{asset('admin/plugins/iCheck/icheck.min.js')}}" type="text/javascript"></script>


    <!-- jQuery -->
    <!-- jQuery UI 1.11.4 -->
    <script src="{{asset('admin/js-1/jquery-ui.min.js')}}"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <!-- Bootstrap 4 -->
    <script src="{{asset('admin/js-1/bootstrap.bundle.min.js')}}"></script>
    <!-- ChartJS -->
    <script src="{{asset('admin/plugins-1/Chart.min.js')}}"></script>
    <!-- Sparkline -->
    <script src="{{asset('admin/plugins-1/sparkline.js')}}"></script>
    <!-- JQVMap -->
    <script src="{{asset('admin/plugins-1/jquery.vmap.min.js')}}"></script>
    <script src="{{asset('admin/plugins-1/jquery.vmap.usa.js')}}"></script>

    <!-- jQuery Knob Chart -->
    <script src="{{asset('admin/plugins-1/jquery.knob.min.js')}}"></script>
    <!-- daterangepicker -->
    <script src="{{asset('admin/plugins-1/moment.min.js')}}"></script>
    <script src="{{asset('admin/plugins-1/daterangepicker.js')}}"></script>
    <!-- Tempusdominus Bootstrap 4 -->
    <script src="{{asset('admin/plugins-1/tempusdominus-bootstrap-4.min.js')}}"></script>
    <!-- Summernote -->
    <script src="{{asset('admin/plugins-1/summernote-bs4.min.js')}}"></script>
    <!-- overlayScrollbars -->
    <script src="{{asset('admin/plugins-1/jquery.overlayScrollbars.min.js')}}"></script>
    <!-- AdminLTE App -->
    <script src="{{asset('admin/js-1/adminlte.js')}}"></script>
    <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
    <script src="{{asset('admin/js-1/dashboard.js')}}"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="{{asset('admin/js-1/demo.js')}}"></script>
    <script src="{{asset('admin/plugins-1/select2.full.min.js')}}"></script>



{{-------------------------------------Custom---------------------------------------------------------}}
    <script src="{{ asset('common/intl-tel-input/js/intlTelInputWithUtils.js') }}"></script>

    <script src="{{asset('admin/plugins/jquery-file-upload/vendor/jquery.ui.widget.js')}}"></script>

    <script src="{{asset('admin/plugins/jquery-file-upload/jquery.fileupload.js')}}"></script>
    <!-- <script src="{{asset('plugins/jquery-file-upload/main.js')}}"></script> -->
    <script src="{{asset('admin/plugins/jquery-file-upload/jquery.iframe-transport.js')}}"></script>
    <script src="{{asset('admin/plugins/jquery-file-upload/resumable.js')}}"></script>
    <script src="{{asset('admin/plugins-1/bootstrap-switch.min.js')}}"></script>

    <script>

    // for sidebar menu entirely but not cover treeview
    
    </script>
    @extends('mini_views.intl_tel_input')
    
    @yield('icheck')
    @yield('datepicker')
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
            });


            // Always allow email type only in lowercase
            document.addEventListener("input", function (event) {
                if (event.target.matches('input[type="email"]')) {
                    event.target.value = event.target.value.toLowerCase();
                }
            });



$("document").ready(function(){
    setTimeout(function(){
        $("#success").remove();
    }, 3000 );
});


      $('#tip-search').click(function() {
            var advance = $('#advance-search');
            
            if (advance.css('display') == 'none') {
                this.setAttribute('title', 'Collapse');
                $('#search-icon').removeClass('fas fa-plus').addClass('fas fa-minus');
        
                advance.show();
            }else {
                this.setAttribute('title', 'Expand');
                 $('#search-icon').removeClass('fas fa-minus').addClass('fas fa-plus');
                advance.hide();
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



        </div><!-- ./wrapper -->
       
        <!-- Bootstrap 3.3.2 JS -->
       


    </body>
</html>
