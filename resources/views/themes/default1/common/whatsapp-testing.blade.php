<!-- SDK loading -->
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>

<script>
    // SDK initialization
    window.fbAsyncInit = function() {
        FB.init({
            appId: '1434685455331701', // your app ID goes here
            autoLogAppEvents: true,
            xfbml: true,
            version: 'v23.0' // Graph API version goes here
        });
    };

    // Session logging message event listener
    window.addEventListener('message', (event) => {
        if (!event.origin.endsWith('facebook.com')) return;
        try {
            const data = JSON.parse(event.data);
            if (data.type === 'WA_EMBEDDED_SIGNUP') {
                console.log('message event: ', data); // remove after testing
                // your code goes here
            }
        } catch {
            console.log('message event: ', event.data); // remove after testing
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
            config_id: '1321634892946880', // your configuration ID goes here
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
