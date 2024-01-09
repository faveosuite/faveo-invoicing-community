@extends('themes.default1.layouts.front.master')
@section('title')
Two-factor authentication
@stop
@section('page-heading')
Two-factor authentication
@stop
@section('page-header')
Forgot Password
@stop
@section('breadcrumb')
    @if(Auth::check())
        <li><a class="text-primary" href="{{url('my-invoices')}}">Home</a></li>
    @else
         <li><a class="text-primary" href="{{url('login')}}">Home</a></li>
    @endif
     <li class="active text-dark">Two-Factor Authentication</li>
@stop 
@section('main-class') 
main
@stop
@section('content')
        <div class="container py-4">

            <div class="row justify-content-center">

                <div class="col-md-6 col-lg-6 mb-5 mb-lg-0 pe-5">

                    {!!  Form::open(['route'=>'2fa/loginValidate', 'method'=>'get']) !!}


                        <div class="row">

                            <div class="form-group col">

                                <label class="form-label text-color-dark text-3">Enter Authentication Code <span class="text-color-danger">*</span></label>

                                <input type="text" name="totp"  id="2fa_code" value="" class="form-control form-control-lg text-4" required>
                            </div>
                            <h6 id="codecheck"></h6>
                        </div>

                        <p class="text-2">Open the two-factor authentication app on your device to view your authentication code and verify your identity.</p>

                        <div class="row">

                            <div class="form-group">
                                @if(!Session::has('reset_token'))

                                <div class="custom-control custom-checkbox">

                                    <label style="position: absolute;left: 0px;">Having problems? <a href="{{'recovery-code'}}" >Login using recovery code</a></label>
                                </div>
                                  @endif
                            </div>

                        </div>

                        <div class="row">

                            <div class="form-group col">

                                <button type="submit" class="btn btn-dark btn-modern w-100 text-uppercase font-weight-bold text-3 py-3" data-loading-text="Loading...">Verify</button>
                            </div>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>

        </div>
@stop 
