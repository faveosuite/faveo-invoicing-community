<nav class="navbar navbar-inverse navbar-fixed-top">
     <script src="{{asset('admin/js/bootstrap.min.js')}}" type="text/javascript"></script>
        <!-- SlimScroll -->
         <script src="{{asset('admin/plugins/slimScroll/jquery.slimscroll.min.js')}}" type="text/javascript"></script>
        <!-- FastClick -->

        <!-- icheck -->




      <header class="main-header">
                <!-- Logo -->
                <a href="{{url('/')}}" class="logo">
                    <!-- mini logo for sidebar mini 50x50 pixels -->
                    <!-- <span class="logo-mini">{{$set->title}}</span> -->
                    <!-- logo for regular state and mobile devices -->
                    @if ($set->title != '')
                    <span class="logo-lg"><b>{{$set->title}} </b></span>
                    @else
                    <span class="logo-lg">
                        <img src='{{ asset("storage/admin/images/$set->admin_logo")}}' class="img-rounded" alt="Admin-Logo"  height="45">


                    </span>
                    @endif
                </a>
                <!-- Header Navbar: style can be found in header.less -->
                <nav class="navbar navbar-static-top" role="navigation">
                    <!-- Sidebar toggle button-->
                    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                        <span class="sr-only">{{ __('message.toggle_navigation') }}</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">

                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">

                                    <img src="{{Auth::user()->profile_pic}}" class="user-image" alt="User Image" />

                                    <span class="hidden-xs">{{ucfirst(Auth::user()->first_name)}} {{ucfirst(Auth::user()->last_name)}}</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <!-- User image -->
                                    <li class="user-header">

                                        <img src="{{Auth::user()->profile_pic}}" class="img-circle" alt="User Image" />

                                        <p>
                                            {{ucfirst(Auth::user()->first_name)}} {{ucfirst(Auth::user()->last_name)}}
                                        </p>
                                    </li>
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <a href="{{url('profile')}}" class="btn btn-default btn-flat">{{Lang::get('message.profile')}}</a>
                                        </div>
                                        <div class="pull-right">
                                            <a href="{{url('auth/logout')}}" class="btn btn-default btn-flat">{{Lang::get('message.signout')}}</a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                     <div class="collapse navbar-collapse" id="navbar">
            <ul class="nav navbar-nav">
                <li class="{{ Route::is('log-viewer::dashboard') ? 'active' : '' }}">
                    <a href="{{ route('log-viewer::dashboard') }}">
                        <i class="fa fa-dashboard"></i> {{ __('message.dashboard') }}
                    </a>
                </li>
                <li class="{{ Route::is('log-viewer::logs.list') ? 'active' : '' }}">
                    <a href="{{ route('log-viewer::logs.list') }}">
                        <i class="fa fa-archive"></i> {{ __('message.logs') }}
                    </a>
                </li>
            </ul>
        </div>
                </nav>
            </header>


</nav>
