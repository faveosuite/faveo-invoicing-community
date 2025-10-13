@extends('themes.default1.layouts.master')


<div class="modal fade" id="emailValidation" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Email Validation Provider</h4>
            </div>
            <div class="modal-body">
                <div id="alertMessage22"></div>
                <div class="form-group" id="emailToDisp">
                    {!! html()->label(Lang::get('message.validation-provider'), 'user')->class('required') !!}
                    <select name="manager" id="provider" class="form-control">
                        <option value="reoon">{{Lang::get('message.reoon')}}</option>
                    </select>
                    <div class="input-group-append"></div>
                </div>
                <div class="form-group" id="emailToRender">
                </div>

            </div>

            <div class="modal-footer justify-content-between">
                <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
                <button type="submit" class="form-group btn btn-primary"  id="submitEmail"><i class="fa fa-save">&nbsp;</i>{!!Lang::get("message.save")!!}</button>
            </div>
        </div>
    </div>
</div>

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
   var fbData=null;
   var fbToken=null;
    // Session logging message event listener
    window.addEventListener('message', (event) => {
        if (!event.origin.endsWith('facebook.com')) return;
        try {
            const data = JSON.parse(event.data);
            if (data.type === 'WA_EMBEDDED_SIGNUP') {
                {{--console.log('message event: ', data);--}}
                {{--$.ajax ({--}}
                {{--    url: '{{url("save-waba-id")}}',--}}
                {{--    type : 'post',--}}
                {{--    data: {--}}
                {{--        "waba_id": data.data.waba_id,"phone_number_id":data.data.phone_number_id,"business_id":data.data.business_id,--}}
                {{--    },--}}
                {{--    success: function (data) {--}}
                {{--        console.log(data);--}}

                {{--    },--}}
                {{--    error:function(data){--}}
                {{--        console.log(data);--}}
                {{--    },--}}
                {{--})--}}
                fbdata=data;
                getAllData();
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
            fbToken=code;
            getAllData();
            // console.log('response1: ', code);
            {{--$.ajax ({--}}
            {{--    url: '{{url("save-access-token")}}',--}}
            {{--    type : 'post',--}}
            {{--    data: {--}}
            {{--        "code": code,--}}
            {{--    },--}}
            {{--    success: function (data) {--}}
            {{--        console.log(data);--}}
            {{--    },--}}
            {{--    error:function(data){--}}
            {{--        console.log(data);--}}
            {{--    },--}}
            {{--})--}}
            // your code goes here
        } else {
            console.log('response2: ', response);
            // your code goes here
        }
    }

    function getAllData(){
        if(fbData && fbToken){
            var data=fbData;
            $.ajax ({
                url: '{{url("save-waba-id")}}',
                type : 'post',
                data: {
                    "waba_id": data.data.waba_id,"phone_number_id":data.data.phone_number_id,"business_id":data.data.business_id,'code':fbToken,
                },
                success: function (data) {
                    console.log(data);

                },
                error:function(data){
                    console.log(data);
                },
            })
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
@section('content')

<!-- Launch button  -->
<button onclick="launchWhatsAppSignup()" style="background-color: #1877f2; border: 0; border-radius: 4px; color: #fff; cursor: pointer; font-family: Helvetica, Arial, sans-serif; font-size: 16px; font-weight: bold; height: 40px; padding: 0 24px;">Login with Facebook</button>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

@stop
