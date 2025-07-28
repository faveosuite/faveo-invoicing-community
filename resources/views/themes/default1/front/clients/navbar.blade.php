{{--@php--}}
{{--function isActiveRoute($route) {--}}
{{--    return request()->is($route) ? 'active' : '';--}}
{{--}--}}

{{--function redirectOnClick($route) {--}}
{{--    return "window.location.href = '" . url($route) . "'";--}}
{{--}--}}
{{--@endphp--}}

<div class="col-lg-2 mt-4 mt-lg-0">
    <aside class="sidebar mt-2 mb-5">
        <ul class="nav nav-list flex-column">
            <li class="nav-item">
                <a class="nav-link" href="{{url('client-dashboard')}}">
                    {{ __('message.dashboard')}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{url('my-orders')}}">
                    {{ __('message.my_orders')}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{url('my-invoices')}}" id="invoices-tab">
                    {{ __('message.my_invoices')}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link " href="{{url('my-profile')}}">
                    {{ __('message.my_profile')}}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{url('auth/logout')}}" >
                    {{ __('message.logout')}}</a>
            </li>
        </ul>
    </aside>
</div>


{{--<script>--}}
{{--    $(document).ready(function() {--}}
{{--        $('.nav-list').on('click', '.nav-link', function(event) {--}}
{{--            event.preventDefault(); --}}
{{--            $('.nav-link').removeClass('active');--}}
{{--            $(this).addClass('active');--}}
{{--            eval($(this).attr('onclick'));--}}
{{--        });--}}
{{--    });--}}
{{--</script>--}}
