<?php
$status =  App\Model\Common\StatusSetting::select('recaptcha_status','v3_recaptcha_status', 'msg91_status', 'emailverification_status', 'terms')->first();
?>

    <div class="modal fade" id="demo-req" tabindex="-1" role="dialog" aria-labelledby="demoModalLabel" aria-hidden="true">

        <div class="modal-dialog">
            <?php
                $apiKeys = \App\ApiKey::select('nocaptcha_sitekey', 'captcha_secretCheck', 'msg91_auth_key', 'terms_url')->first();
                ?>
    
            <form  id="demoForm" method="post">
            <div class="modal-content">

                <div class="modal-header">

                    <h4 class="modal-title" id="demoModalLabel">{{ __('message.book_a_demo')}}</h4>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div id="alert-container-demo"></div>

                <div class="modal-body">

                    <div class="row">

                        <div class="col">
   
                                <div class="contact-form-success alert alert-success d-none mt-4">

                                    <strong>{{ __('message.success')}}!</strong> {{ __('message.message_sent')}}
                                </div>

                                <div class="contact-form-error alert alert-danger d-none mt-4">

                                    <strong>{{ __('message.error')}}</strong> {{ __('message.error_sending_message')}}

                                    <span class="mail-error-message text-1 d-block"></span>
                                </div>

                                <div class="row">

                                    <div class="form-group col-lg-6">

                                        <label class="form-label mb-1 text-2">{{ __('message.name_page')}} <span class="text-danger"> *</span> </label>

                                        <input type="text" value="" data-msg-required="{{ __('message.contact_error_name')}}" maxlength="100" class="form-control text-3 h-auto py-2" name="demoname" id="demoname" required>
                                    </div>

                                    <div class="form-group col-lg-6">

                                        <label class="form-label mb-1 text-2">{{ __('message.email_address')}} <span class="text-danger"> *</span></label>

                                        <input type="email" value="" data-msg-required="{{ __('message.error_email_address')}}" data-msg-email="{{ __('message.error_email_address')}}" maxlength="100" class="form-control text-3 h-auto py-2" name="demoemail" id="demoemail" required>
                                    </div>
                                </div>

                                <div class="row">

                                    <div class="form-group col">

                                        <label class="form-label mb-1 text-2">{{ __('message.mobile')}} <span class="text-danger"> *</span></label>

                                         {!! Form::hidden('mobile',null,['id'=>'mobile_code_hiddenDemo','name'=>'country_code']) !!}
                                        <input class="form-control input-lg" id="mobilenumdemo" name="Mobile" type="tel" required>
                                         {!! Form::hidden('mobile_code',null,['class'=>'form-control input-lg','disabled','id'=>'mobile_codeDemo']) !!}
                                        <span id="valid-msgdemo" class="hide"></span>
                                        <span id="error-msgdemo" class="hide"></span>
                                        <span id="mobile_codecheckdemo"></span>
                                    </div>
                                </div>

                                <div class="row">

                                    <div class="form-group col">

                                        <label class="form-label mb-1 text-2">{{ __('message.contact_message')}} <span class="text-danger"> *</span></label>

                                   <textarea maxlength="5000" data-msg-required="{{ __('message.contact_error_message')}}" rows="3" class="form-control" name="demomessage" id="demomessage" required></textarea>
                                    </div>
                                </div>
                                
                                  <!-- Honeypot fields (hidden) -->
                                <div style="display: none;">
                                    <label>{{ __('message.leave_this_field_empty')}}</label>
                                    <input type="text" name="honeypot_field" value="">
                                </div>

                            @if (Auth::check())
                                {{-- Authenticated user, no reCAPTCHA required --}}
                            @else
                                @if ($status->recaptcha_status === 1)
                                    <div id="demo_recaptcha"></div>
                                    <span id="democaptchacheck"></span>
                                @elseif($status->v3_recaptcha_status === 1)
                                    <input type="hidden" id="g-recaptcha-demo" class="g-recaptcha-token" name="g-recaptcha-response">
                                @endif
                            @endif
                            <br>
                            
                        </div>
                    </div>
                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('message.close')}}</button>&nbsp;&nbsp;&nbsp;

                    <button type="submit" class="btn btn-primary" name="demoregister" id="demoregister">{{ __('message.book_a_demo')}}</button>
                </div>
            </div>
            </form>
        </div>
    </div>

        <script>
            let demo_recaptcha_id;
            let recaptchaTokenDemo;

    // Only execute for non-authenticated users
    @if (!Auth::check())
    // Initialize reCAPTCHA for v2 if enabled
    @if($status->recaptcha_status === 1)
    recaptchaFunctionToExecute.push(() => {
        demo_recaptcha_id = grecaptcha.render('demo_recaptcha', {
            'sitekey': siteKey
        });
    });
    @endif
    @endif
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
            $('#alert-container-demo').html(html);
            clearTimeout(alertTimeout); // Clear the previous timeout if it exists

            // Display alert
            window.scrollTo(0, 0);

            // Auto-dismiss after 5 seconds
            alertTimeout = setTimeout(function() {
                $('#alert-container-demo .alert').slideUp(3000, function() {
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
            const message = response.message || response || '{{ __('message.error_occurred') }}';
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
        }, "{{ __('message.error_valid_number') }}");

        $.validator.addMethod("recaptchaRequired", function(value, element) {
            try {
                if(!recaptchaEnabled) {
                    return false;
                }
            }catch (ex){
                return false
            }
            return value.trim() !== "";
        }, "{{ __('message.recaptcha_required') }}");

        $.validator.addMethod("regex", function(value, element, regexp) {
            var re = new RegExp(regexp);
            return this.optional(element) || re.test(value);
        }, "{{ __('message.invalid_format') }}");

        $('#demoForm').validate({
            ignore: ":hidden:not(.g-recaptcha-response)",
            rules: {
                demoname: {
                    required: true
                },
                demoemail: {
                    required: true,
                    regex: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
                },
                Mobile: {
                    required: true,
                    validPhone: true
                },
                demomessage: {
                    required: true
                },
                "g-recaptcha-response": {
                    recaptchaRequired: true
                }
            },
            messages: {
                demoname: {
                    required: "{{ __('message.contact_error_name') }}"
                },
                demoemail: {
                    required: "{{ __('message.enter_your_email') }}",
                    regex: "{{ __('message.contact_error_email') }}"
                },
                Mobile: {
                    required: "{{ __('message.error_mobile') }}",
                    validPhone: "{{ __('message.enter_your_mobile') }}"
                },
                demomessage: {
                    required: "{{ __('message.contact_error_message') }}"
                },
                "g-recaptcha-response": {
                    recaptchaRequired: "{{ __('message.recaptcha_required') }}"
                }
            },
            unhighlight: function(element) {
                $(element).removeClass("is-valid");
            },
            errorPlacement: function(error, element) {
                var errorMapping = {
                    "Mobile": "#mobile_codecheckdemo",
                    "g-recaptcha-response": "#democaptchacheck"
                };

                placeErrorMessage(error, element, errorMapping);
            },
            submitHandler: function(form) {
                $('#mobile_code_hiddenDemo').val('+' + $('#mobilenumdemo').attr('data-dial-code'));
                $('#mobilenumdemo').val($('#mobilenumdemo').val().replace(/\D/g, ''));
                var formData = $(form).serialize();
                var submitButton = $('#demoregister');
                $.ajax({
                    url: 'demo-request',
                    type: 'POST',
                    data: formData,
                    beforeSend: function() {
                        submitButton.prop('disabled', true).html(submitButton.data('loading-text'));
                    },
                    success: function(response) {
                        form.reset();
                        showAlert('success', response.message);
                        setTimeout(function() {
                            window.location.reload();
                        }, 3500);
                    },
                    error: function(data) {
                        var response = data.responseJSON ? data.responseJSON : JSON.parse(data.responseText);
                        showAlert('error', response);
                    },
                    complete: function() {
                        submitButton.prop('disabled', false).html(submitButton.data('original-text'));
                    }
                });
            }
        });
    });
</script>

     



 
