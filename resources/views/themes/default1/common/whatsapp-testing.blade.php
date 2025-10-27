<!-- SDK loading -->
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>

<script>
    // SDK initialization
    window.fbAsyncInit = function() {
        FB.init({
            appId: '802421802601961',
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
                // your code goes here
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
            console.log('response1: ', code); // remove after testing

            // your code goes here
        } else {
            console.log('response2: ', response); // remove after testing
            // your code goes here
        }
    }

    // Launch method and callback registration
    const launchWhatsAppSignup = () => {
        FB.login(fbLoginCallback, {
            config_id: '1613294093413891',
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
