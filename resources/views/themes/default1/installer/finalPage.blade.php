@php
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Artisan;
 @endphp

@extends('themes.default1.installer.layout.installer')

@section('dbSetup')
    done
@stop

@section('database')
    done
@stop

@section('post-check')
    done
@stop

@section('get-start')
    done
@stop

@section('final')
    active
@stop

@section('content')
    <?php
    if (isset($_GET['flush']) && $_GET['flush'] == 'true') {
        Cache::flush();
        Session::flush();
        \Artisan::call('optimize:clear');
        header('Location: ' . route('login'));
        exit();
    }
    ?>
    <div class="card">
        <div class="card-body">
            <p class="text-center lead text-bold">{{ trans('installer_messages.final_setup') }}!</p>
            <div class="row">
                <div class="col-6">
                    <p class="lead">{{ trans('installer_messages.learn_more') }}</p>
                    <p><i class="fas fa-newspaper"></i>&nbsp;&nbsp;<a href="https://github.com/ladybirdweb/agora-invoicing-community/wiki" target="_blank">{{ trans('installer_messages.knowledge_base') }}</a></p>
                    <p><i class="fas fa-envelope"></i>&nbsp;&nbsp;<a href="mailto:support@ladybirdweb.com">{{ trans('installer_messages.email_support') }}</a></p>
                </div>
                <div class="col-6">
                    <form action="" method="get">
                        @csrf
                        <div class="float-end installer-login-btn">
                            <button type="submit" name="flush" value="true" class="btn btn-primary mt-4">
                                {{ trans('installer_messages.login_button') }} &nbsp;<i class="{{ in_array(app()->getLocale(), ['ar', 'he']) ? 'fas fa-arrow-left' : 'fas fa-arrow-right' }}"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop