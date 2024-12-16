@extends('themes.default1.layouts.front.master')
@section('title')
Two-factory recovery
@stop
@section('page-heading')
Two-factory recovery
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
     <li class="active text-dark">Two-factory recovery</li>
@stop 
@section('main-class') 
main
@stop
@section('content')
        <div class="container py-4">

            <div class="row justify-content-center">

                <div class="col-md-6 col-lg-6 mb-5 mb-lg-0 pe-5">

                    {!! html()->form('POST', route('verify-recovery-code'))->id('recovery_form')->open() !!}


                    <div class="row">

                            <div class="form-group col">

                                <label class="form-label text-color-dark text-3">Enter recovery code <span class="text-color-danger">*</span></label>

                                <input type="text" name="rec_code"  value="" class="form-control form-control-lg text-4">
                            </div>
                            <h6 id="codecheck"></h6>
                        </div>

                        <p class="text-2">You recovery code can be used only once. Make sure to generate new recovery code from My Profile section once you successfully sign in using your current recovery code.</p>

                        <div class="row">

                            <div class="form-group">
                               

                                   <a href="{{'verify-2fa'}}" >Login using Authenticator Passcode</a>
                               
                            </div>

                        </div>

                        <div class="row">

                            <div class="form-group col">

                                <button type="submit" class="btn btn-dark btn-modern w-100 text-uppercase font-weight-bold text-3 py-3" data-loading-text="Loading...">Verify</button>
                            </div>
                        </div>
                    {!! html()->form()->close() !!}
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
                $('#recovery_form').validate({
                    rules: {
                        rec_code: {
                            required: true
                        },
                    },
                    messages: {
                        rec_code: {
                            required: "Please enter the recovery code",
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
