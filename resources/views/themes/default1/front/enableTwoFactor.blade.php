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
        <li><a class="text-primary" href="{{url('my-invoices')}}">{{ __('message.home')}}</a></li>
    @else
         <li><a class="text-primary" href="{{url('login')}}">{{ __('message.home')}}</a></li>
    @endif
     <li class="active text-dark">{{ __('message.two_factor')}}</li>
@stop 
@section('main-class') 
main
@stop
@section('content')
        <div class="container py-4">

            <div class="row justify-content-center">

                <div class="col-md-6 col-lg-6 mb-5 mb-lg-0 pe-5">

                    {!!  Form::open(['route'=>'2fa/loginValidate', 'method'=>'get', 'id' => '2fa_form']) !!}


                        <div class="row">

                            <div class="form-group col">

                                <label class="form-label text-color-dark text-3">{{ __('message.enter_auth_code')}} <span class="text-color-danger">*</span></label>

                                <input type="text" name="totp"  id="2fa_code" value="" class="form-control form-control-lg text-4">
                            </div>
                            <h6 id="codecheck"></h6>
                        </div>

                        <p class="text-2">{{ __('message.open_two_factor')}}</p>

                        <div class="row">

                            <div class="form-group">
                                @if(!Session::has('reset_token'))

                                <div class="custom-control custom-checkbox">

                                    <label style="position: absolute;left: 0px;">{{ __('message.having_problem')}} <a href="{{'recovery-code'}}" >{{ __('message.login_recovery_code')}}</a></label>
                                </div>
                                  @endif
                            </div>

                        </div>

                        <div class="row">

                            <div class="form-group col">

                                <button type="submit" class="btn btn-dark btn-modern w-100 text-uppercase font-weight-bold text-3 py-3" data-loading-text="Loading...">{{ __('message.verify')}}</button>
                            </div>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>

        </div>
    <script>
        $(document).ready(function() {
            function placeErrorMessage(error, element, errorMapping = null) {
                if (errorMapping !== null && errorMapping[element.attr("name")]) {
                    $(errorMapping[element.attr("name")]).html(error);
                } else {
                    error.insertAfter(element);
                }
            }
            $('#2fa_form').validate({
                rules: {
                    totp: {
                        required: true
                    },
                },
                messages: {
                    totp: {
                        required: "Please enter the authentication code"
                    },
                },
                unhighlight: function (element) {
                    $(element).removeClass("is-valid");
                },
                errorPlacement: function (error, element) {
                    placeErrorMessage(error, element);
                },
                submitHandler: function (form) {
                    form.submit();
                }
            });
        });
    </script>
@stop 
