@extends('themes.default1.layouts.front.master')
@section('title')
Contact Us
@stop
@section('page-header')
Cart
@stop
@section('page-heading')
Contact us
@stop
@section('breadcrumb')
@if(Auth::check())
        <li><a class="text-primary" href="{{url('my-invoices')}}">Home</a></li>
@else
     <li><a class="text-primary" href="{{url('login')}}">Home</a></li>
@endif
 <li class="active text-dark">Contact us</li>
@stop
@section('main-class') "main shop" @stop
@section('content')   
<style>
    .required:after{ 
        content:'*'; 
        color:red; 
        padding-left:5px;
    }
</style>
<?php 
$set = new \App\Model\Common\Setting();
$set = $set->findOrFail(1);
$address = preg_replace("/^\R+|\R+\z/", '', $set->address);
$state = \DB::table('states_subdivisions')->where('state_subdivision_code',$set->state)->value('state_subdivision_name');
$country = \DB::table('countries')->where('country_code_char2',$set->country)->value('country_name');

?>
<div id="alert-container"></div>

        <div class="container">

            <div class="row py-4">

                <div class="col-lg-6">

                    <p class="mb-4">Feel free to ask for details, don't save any questions!</p>

                     <form id="contactForm" method="post">


                        <div class="row">

                            <div class="form-group col-lg-6">

                                <label class="form-label mb-1 text-2">Name <span class="text-color-danger">*</span></label>

                                <input type="text" value="" data-msg-required="Please enter your name." maxlength="100" class="form-control text-3 h-auto py-2" name="conName" id="conName">
                            </div>

                            <div class="form-group col-lg-6">

                                <label class="form-label mb-1 text-2">E-mail Address <span class="text-color-danger">*</span></label>

                                <input type="email" value="" data-msg-required="Please enter your email address." data-msg-email="Please enter a valid email address." maxlength="100" class="form-control text-3 h-auto py-2" name="email" id="email" >
                            </div>
                        </div>

                        <div class="row">

                            <div class="form-group col">

                                <label class="form-label mb-1 text-2">Mobile <span class="text-color-danger">*</span></label>

                                {!! html()->hidden('mobile', null)->id('mobile_code_hiddenco')->name('country_code') !!}
                                <input class="form-control input-lg" id="mobilenumcon" name="Mobile" type="tel">
                                {!! html()->hidden('mobile_code', null)->class('form-control text-3 h-auto py-2')->id('mobile_codecon')->disabled() !!}
                                <span id="valid-msgcon" class="hide"></span>
                                <span id="error-msgcon" class="hide"></span>
                                <span id="mobile_codecheckcon"></span>
                            </div>
                        </div>

                        <div class="row">

                            <div class="form-group col">

                                <label class="form-label mb-1 text-2">Message <span class="text-color-danger">*</span></label>

                                <textarea maxlength="5000" data-msg-required="Please enter your message." rows="8" class="form-control text-3 h-auto py-2" name="conmessage" id="conmessage"></textarea>
                            </div>
                        </div>
                         <!-- Honeypot fields (hidden) -->
                                <div style="display: none;">
                                    <label>Leave this field empty</label>
                                    <input type="text" name="conatcthoneypot_field" value="">
                                </div>

                         @if ($status->recaptcha_status === 1)
                             <div id="recaptchaContact"></div>
                             <span id="captchacheck"></span>
                         @elseif($status->v3_recaptcha_status === 1)
                             <input type="hidden" id="g-recaptcha-contact" class="g-recaptcha-token" name="g-recaptcha-response">
                         @endif
                                <br>

                        <div class="row">

                            <div class="form-group col">

                                <button type="submit" class="btn btn-dark btn-modern text-3" data-loading-text="Loading..." data-original-text="Send Message" id="contactSubmit">Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>


                <div class="col-lg-6">

                    <div>

                        <h4 class="mt-2 mb-1"><strong>Our Office</strong></h4>

                        <ul class="list list-icons list-icons-style-2 mt-2">

                            <li><i class="fas fa-map-marker-alt top-6"></i> <strong class="text-dark">Address:</strong> {{ $address }}<br>{{ implode(', ', array_filter([$set->city, $state, $country, $set->zip])) }}</li>

                            <li><i class="fas fa-phone top-6"></i> <strong class="text-dark">Phone:</strong> +</b>{{$set->phone_code}} {{$set->phone}}</li>

                            <li><i class="fas fa-envelope top-6"></i> <strong class="text-dark">Email:</strong> <a href="mailto:{{$set->company_email}}">{{$set->company_email}}</a></li>
                        </ul>
                    </div>


                </div>
            </div>
        </div>
@stop
@section('script')
@extends('mini_views.recaptcha')

        <script>
            let contact_recaptcha_id;
            let recaptchaToken;

            recaptchaFunctionToExecute.push(() => {
                contact_recaptcha_id = grecaptcha.render('recaptchaContact', { 'sitekey': siteKey });
            });
    </script>
<script>
$(document).ready(function() {
    function placeErrorMessage(error, element, errorMapping = null) {
        if (errorMapping !== null && errorMapping[element.attr("name")]) {
            $(errorMapping[element.attr("name")]).html(error);
        } else {
            error.insertAfter(element);
        }
    }
    let alertTimeout;

    function showAlert(type, messageOrResponse) {

        // Generate appropriate HTML
        var html = generateAlertHtml(type, messageOrResponse);

        // Clear any existing alerts and remove the timeout
        $('#alert-container').html(html);
        clearTimeout(alertTimeout); // Clear the previous timeout if it exists

        // Display alert
        window.scrollTo(0, 0);

        // Auto-dismiss after 5 seconds
        alertTimeout = setTimeout(function() {
            $('#alert-container .alert').slideUp(3000, function() {
                // Then fade out after slideUp finishes
                $(this).fadeOut('slow');
            });
        }, 5000);
    }


    function generateAlertHtml(type, response) {
        // Determine alert styling based on type
        const isSuccess = type === 'success';
        const iconClass = isSuccess ? 'fa-check-circle' : 'fa-ban';
        const alertClass = isSuccess ? 'alert-success' : 'alert-danger';

        // Extract message and errors
        const message = response.message || response || 'An error occurred. Please try again.';
        const errors = response.errors || null;

        // Build base HTML
        let html = `<div class="alert ${alertClass} alert-dismissible">` +
            `<i class="fa ${iconClass}"></i> ` +
            `${message}` +
            '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';

        html += '</div>';

        return html;
    }
    $.validator.addMethod("validPhone", function(value, element) {
        return validatePhoneNumber(element);
    }, "Please enter a valid phone number.");

    $.validator.addMethod("regex", function(value, element, regexp) {
        var re = new RegExp(regexp);
        return this.optional(element) || re.test(value);
    }, "Invalid format.");

    $.validator.addMethod("recaptchaRequired", function(value, element) {
        try {
            if(!recaptchaEnabled) {
                return false;
            }
        }catch (ex){
            return false
        }
        return value.trim() !== "";
    }, "Please verify that you are not a robot.");
    $('#contactForm').validate({
        ignore: ":hidden:not(.g-recaptcha-response)",
        rules: {
            conName: {
                required: true
            },
            email: {
                required: true,
                regex: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
            },
            country_code: {
                required: true
            },
            Mobile: {
                required: true,
                validPhone: true
            },
            conmessage: {
                required: true
            },
            "g-recaptcha-response": {
                recaptchaRequired: true
            }
        },
        messages: {
            conName: {
                required: "Please enter your name."
            },
            email: {
                required: "Please enter your email.",
                regex: "Please enter a valid email address."
            },
            country_code: {
                required: "Please enter your country code."
            },
            Mobile: {
                required: "Please enter your mobile number.",
                validPhone: "Please enter a valid mobile number."
            },
            conmessage: {
                required: "Please enter your message."
            },
            "g-recaptcha-response": {
                recaptchaRequired: "Please verify that you are not a robot."
            }
        },
        unhighlight: function (element) {
            $(element).removeClass("is-valid");
        },
        errorPlacement: function (error, element) {
            var errorMapping = {
                "Mobile": "#mobile_codecheckcon",
                "g-recaptcha-response": "#captchacheck"
            };

            placeErrorMessage(error, element,errorMapping);
        },
        submitHandler: function (form) {
            $('#mobile_code_hiddenco').val('+' + $('#mobilenumcon').attr('data-dial-code'));
            $('#mobilenumcon').val($('#mobilenumcon').val().replace(/\D/g, ''));

            var formData = $(form).serialize();

            var submitButton = $('#contactSubmit');

            $.ajax({
                type: 'POST',
                url: 'contact-us',
                data: formData,
                dataType: 'json',
                beforeSend: function () {
                    submitButton.prop('disabled', true).html(submitButton.data('loading-text'));
                },
                success: function (response) {
                    form.reset();
                    showAlert('success', response.message);
                },
                error: function (data, status, error) {
                    var response = data.responseJSON ? data.responseJSON : JSON.parse(data.responseText);

                    if (response.errors) {
                        $.each(response.errors, function(field, messages) {
                            var validator = $('#contactForm').validate();

                            var fieldSelector = $(`[name="${field}"]`).attr('name');  // Get the name attribute of the selected field

                            validator.showErrors({
                                [fieldSelector]: messages[0]
                            });
                        });
                    } else {
                        showAlert('error', response);
                    }
                },
                complete: function () {
                    submitButton.prop('disabled', false).html(submitButton.data('original-text'));
                }
            });
        }
    });
});
</script>
@stop