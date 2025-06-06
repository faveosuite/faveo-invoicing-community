<?php
use App\ApiKey;
use App\Model\Common\StatusSetting;

$statusSetting = StatusSetting::find(1);
$apiKeys = ApiKey::find(1);
?>

@if(isCaptchaRequired()['status'])
    <script>
        const siteKey = "{{ config('services.recaptcha.sitekey')  ?? $apiKeys->nocaptcha_sitekey }}";
        let recaptchaFunctionToExecute = [];
        var recaptchaEnabled = {{ $statusSetting->recaptcha_status === 1 ? 'true' : 'false' }};
        var recaptchaV3Enabled = {{ $statusSetting->v3_recaptcha_status === 1 ? 'true' : 'false' }};

        @if($statusSetting->recaptcha_status === 1)

        // Include reCAPTCHA v2
        (function () {
            const script = document.createElement('script');
            script.src = 'https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit';
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
        })();

        var onloadCallback = () => {
            if (Array.isArray(recaptchaFunctionToExecute) && recaptchaFunctionToExecute.length > 0) {
                recaptchaFunctionToExecute.forEach((fn) => {
                    if (typeof fn === 'function') {
                        fn();
                    } else {
                        console.error("Invalid item in functionToExecute, not a function.");
                    }
                });
            } else {
                console.error("functionToExecute is empty or not an array.");
            }
        };

        function getRecaptchaTokenFromId(id){
            return grecaptcha.getResponse(id);
        }

        @elseif($statusSetting->v3_recaptcha_status === 1)
        // Include reCAPTCHA v3
        const loadRecaptchaScript = () => {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = `https://www.google.com/recaptcha/api.js?render=${siteKey}`;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        };

        const generateRecaptchaToken = (action = 'default') => {
            return new Promise((resolve, reject) => {
                if (!grecaptcha) {
                    return reject('reCAPTCHA not loaded');
                }
                grecaptcha.ready(() => {
                    grecaptcha.execute(siteKey, { action })
                        .then(resolve)
                        .catch(reject);
                });
            });
        };

        document.addEventListener('DOMContentLoaded', async () => {
            try {
                await loadRecaptchaScript(); // Ensure the reCAPTCHA script is loaded
                const recaptchaInputs = document.querySelectorAll('.g-recaptcha-token');
                for (const input of recaptchaInputs) {
                    const action = input.dataset.recaptchaAction || 'default';
                    const token = await generateRecaptchaToken(action);
                    input.value = token; // Assign the generated token to the input value
                }
            } catch (error) {
                console.error('reCAPTCHA error:', error);
            }
        });

        const updateRecaptchaTokens = async () => {
            try {
                const recaptchaInputs = document.querySelectorAll('.g-recaptcha-token');
                for (const input of recaptchaInputs) {
                    const action = input.dataset.recaptchaAction || 'default';
                    const token = await generateRecaptchaToken(action);
                    input.value = token; // Update the input field with the new token
                }
            } catch (error) {
                console.error('Error generating reCAPTCHA token:', error);
            }
        };

        setInterval(updateRecaptchaTokens, 120000);

        @endif
    </script>
@endif
