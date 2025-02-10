{{ __('message.email_username') }}{{$email}}
<br>
{{ __('message.email_password') }}{{$pass}}
<br>
<br>
{{ __('message.activate_account') }}  <a href='{{ url('activate/'.$token) }}'>{{ __('message.email_click_here') }}</a>