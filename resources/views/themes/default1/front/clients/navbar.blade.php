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
{{--            <li class="nav-item">--}}
{{--                <a class="nav-link {{ isActiveRoute('client-dashboard') }}" href="#dashboard" data-bs-toggle="tab" data-hash data-hash-offset="0" data-hash-offset-lg="120" data-hash-delay="500"--}}
{{--                   onclick="{{ redirectOnClick('client-dashboard') }}">Dashboard</a>--}}
{{--            </li>--}}
{{--            <li class="nav-item">--}}
{{--                <a class="nav-link {{ isActiveRoute('my-orders') }}" href="#orders" data-bs-toggle="tab" data-hash data-hash-offset="0" data-hash-offset-lg="120" data-hash-delay="500"--}}
{{--                   onclick="{{ redirectOnClick('my-orders') }}">My Orders</a>--}}
{{--            </li>--}}
{{--            <li class="nav-item">--}}
{{--                <a class="nav-link {{ isActiveRoute('my-invoices') }}" href="#invoices" id="invoices-tab"  data-bs-toggle="tab" data-hash data-hash-offset="0" data-hash-offset-lg="120" data-hash-delay="500"--}}
{{--                   onclick="{{ redirectOnClick('my-invoices') }}">My Invoices</a>--}}
{{--            </li>--}}
{{--            <li class="nav-item">--}}
{{--                <a class="nav-link {{ isActiveRoute('my-profile') }}" href="#profile" data-bs-toggle="tab" data-hash data-hash-offset="0" data-hash-offset-lg="120" data-hash-delay="500"--}}
{{--                   onclick="{{ redirectOnClick('my-profile') }}">My Profile</a>--}}
{{--            </li>--}}
{{--            <li class="nav-item">--}}
{{--                <a class="nav-link" href="#logout" onclick="{{ redirectOnClick('auth/logout') }}">Logout</a>--}}
{{--            </li>--}}


            <li class="nav-item">
                <a class="nav-link" href="{{url('client-dashboard')}}">
                    Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{url('my-orders')}}">
                    My Orders</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{url('my-invoices')}}" id="invoices-tab">
                    My Invoices</a>
            </li>
            <li class="nav-item">
                <a class="nav-link " href="{{url('my-profile')}}">
                    My Profile</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{url('auth/logout')}}" >
                    Logout</a>
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
