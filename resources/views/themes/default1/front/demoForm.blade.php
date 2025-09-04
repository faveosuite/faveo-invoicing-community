<div class="modal fade" id="demo-req" tabindex="-1" role="dialog" aria-labelledby="demoModalLabel" aria-hidden="true">

        <div class="modal-dialog">
            <?php
                $apiKeys = \App\ApiKey::select('nocaptcha_sitekey', 'captcha_secretCheck', 'msg91_auth_key', 'terms_url')->first();
                ?>
    
            <form  id="demoForm">
            <div class="modal-content">

                <div class="modal-header">

                    <h4 class="modal-title" id="demoModalLabel">{{ __('message.book_a_demo')}}</h4>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>


                <div class="modal-body">
                    <div id="alert-container-demo"></div>
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

                                        {!! html()->hidden('mobile', null)->id('mobile_code_hiddenDemo')->name('country_code') !!}

                                        {!! html()->input('tel', 'Mobile', null)
                                            ->class('form-control input-lg')
                                            ->id('mobilenumdemo')
                                            ->required() !!}

                                        {!! html()->hidden('mobile_code', null)
                                            ->class('form-control input-lg')
                                            ->disabled()
                                            ->id('mobile_codeDemo') !!}
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
                                {!! honeypotField('demo') !!}

                            <div id="row">
                                <div id="demo_recaptcha"></div>
                            </div>
                            <br>
                            
                        </div>
                    </div>
                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="fa fa-times">&nbsp;&nbsp;</i>{{ __('message.close')}}</button>&nbsp;&nbsp;&nbsp;

                    <button type="submit" class="btn btn-primary" name="demoregister" id="demoregister"><i class="fa fa-book">&nbsp;&nbsp;</i>{{ __('message.book_a_demo')}}</button>
                </div>
            </div>
            </form>
        </div>
    </div>

<script>
    let demoRecaptcha;

    (async () => {
        const demoRecaptchaContainer = document.getElementById('demo_recaptcha');

        demoRecaptcha = await RecaptchaManager.init(demoRecaptchaContainer, {
            action: 'demo',
        });

        // Make them globally available
        window.demoRecaptcha = demoRecaptcha;

    })();
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
            const message = response.message || response || 'An error occurred. Please try again.';
            const errors = response.errors || null;

            // Build base HTML
            let html = `<div class="alert ${alertClass} alert-dismissible">` +
                `<i class="fa ${iconClass}"></i> ` +
                `${message}` +
                '<button type="button" class="btn-close" data-dismiss="alert" aria-hidden="true" ></button>';

            html += '</div>';

            return html;
        }
        $.validator.addMethod("validPhone", function(value, element) {
            return validatePhoneNumber(element);
        }, "{{ __('message.error_valid_number') }}");

        $.validator.addMethod("regex", function(value, element, regexp) {
            var re = new RegExp(regexp);
            return this.optional(element) || re.test(value);
        }, "{{ __('message.invalid_format') }}");

        $('#demoForm').validate({
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
                }
            },
            unhighlight: function(element) {
                $(element).removeClass("is-valid");
            },
            errorPlacement: function(error, element) {
                var errorMapping = {
                    "Mobile": "#mobile_codecheckdemo",
                };

                placeErrorMessage(error, element, errorMapping);
            }
        });

        $('#demoForm').on('submit', async function(event) {
            event.preventDefault();

            const $form = $(this);

            const $submitButton = $('#demoregister');

            if (!$form.valid()) {
                return;
            }

            try {
                // Validate reCAPTCHA
                let recaptchaToken = await window.demoRecaptcha.tokenValidation(demoRecaptcha, "demo");
                if (!recaptchaToken) return;

                $('#mobile_code_hiddenDemo').val('+' + $('#mobilenumdemo').attr('data-dial-code'));
                $('#mobilenumdemo').val($('#mobilenumdemo').val().replace(/\D/g, ''));

                // Collect form data
                let formData = $form.serializeArray();

                if (!window.demoRecaptcha.isDisabled() && recaptchaToken) {
                    formData.push({ name: "g-recaptcha-response", value: recaptchaToken });
                    formData.push({ name: "page_id", value: window.pageId });
                }

                // Submit form
                $.ajax({
                    url: "{{ url('demo-request') }}",
                    method: "POST",
                    data: $.param(formData),
                    beforeSend: function () {
                        $submitButton.prop("disabled", true).html($submitButton.data("loading-text"));
                    },
                    success: function (response) {
                        $form[0].reset();
                        showAlert('success', response.message);
                    },
                    error: async function (xhr) {
                        let response = xhr.responseJSON || JSON.parse(xhr.responseText || "{}");

                        // Handle reCAPTCHA fallback
                        if (response.data?.show_v2_recaptcha) {
                            await window.demoRecaptcha.useFallback(true);
                            showAlert("error", response.message || "An unexpected error occurred.");
                            return;
                        }

                        if (response.errors) {
                            $.each(response.errors, function (field, messages) {
                                if (["demo"].includes(field)) {
                                    showAlert("error", messages[0]);
                                    return;
                                }
                                validator.showErrors({ [field]: messages[0] });
                            });
                        } else {
                            showAlert("error", response.message || "An unexpected error occurred.");
                        }
                    },
                    complete: function () {
                        $submitButton.prop("disabled", false).html($submitButton.data("original-text"));
                        window.demoRecaptcha.reset();
                    }
                });
            } catch (err) {
                console.error("Form submit error:", err);
                showAlert("error", "Something went wrong. Please try again.");
            }
        });
    });
</script>

     



 
