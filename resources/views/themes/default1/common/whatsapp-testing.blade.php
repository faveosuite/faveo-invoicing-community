<!-- SDK loading -->
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>

<script>
    // SDK initialization
    window.fbAsyncInit = function() {
        FB.init({
            appId: {{$app_id}},
            autoLogAppEvents: true,
            xfbml: true,
            version: 'v24.0'
        });
    };

    // Session logging message event listener
    window.addEventListener('message', (event) => {
        if (!event.origin.endsWith('facebook.com')) return;
        try {
            const data = JSON.parse(event.data);
            if (data.type === 'WA_EMBEDDED_SIGNUP') {
                console.log('message event: ', data);
                $.ajax ({
                    url: '{{url("save-waba-id")}}',
                    type : 'post',
                    data: {
                        "waba_id": data.waba_id,"phone_number_id":data.phone_number_id,"business_id":data.business_id,
                    },
                    success: function (data) {
                        console.log(data);
                    },
                    error:function(data){
                        console.log(data);
                    },
                })
            }
        } catch {
            console.log('message event: ', event.data);
            // your code goes here
        }
    });

    // Response callback
    const fbLoginCallback = (response) => {
        if (response.authResponse) {
            const code = response.authResponse.code;
            console.log('response1: ', code);
            $.ajax ({
                url: '{{url("save-access-token")}}',
                type : 'post',
                data: {
                    "code": code,
                },
                success: function (data) {
                    console.log(data);
                },
                error:function(data){
                    console.log(data);
                },
            })
            // your code goes here
        } else {
            console.log('response2: ', response);
            // your code goes here
        }
    }

    // Launch method and callback registration
    const launchWhatsAppSignup = () => {
        FB.login(fbLoginCallback, {
            config_id: {{$config_id}},
            response_type: 'code',
            override_default_response_type: true,
            extras: {
                setup: {},
            }
        });
    }
</script>

<!-- Launch button  -->
<button onclick="launchWhatsAppSignup()" style="background-color: #1877f2; border: 0; border-radius: 4px; color: #fff; cursor: pointer; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: bold; height: 40px; padding: 0 24px;">Login with Facebook</button>
