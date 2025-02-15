@extends('themes.default1.layouts.master')
@section('title')
Edit Order
@stop
@section('content')
<div class="box box-primary">

    <div class="content-header">
        @if (count($errors) > 0)
        <div class="alert alert-danger">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(Session::has('success'))
        <div class="alert alert-success alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{Session::get('success')}}
        </div>
        @endif
        <!-- fail message -->
        @if(Session::has('fails'))
        <div class="alert alert-danger alert-dismissable">
            <i class="fa fa-ban"></i>
            <b>{{Lang::get('message.alert')}}!</b> {{Lang::get('message.failed')}}.
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{Session::get('fails')}}
        </div>
        @endif

            {!! html()->model($order)->method('patch')->action('orders/'.$order->id) !!}
            <h4>{{Lang::get('message.orders')}}	{!! html()->submit(Lang::get('message.save'))->class('form-group btn btn-primary pull-right') !!}</h4>

    </div>

    <div class="box-body">

        <div class="row">

            <div class="col-md-12">


                <div class="row">

                    <div class="col-md-3 form-group {!! $errors->has('client') ? 'has-error' : '' !!}">
                        {!! html()->label(Lang::get('message.client'))->class('required')->for('client') !!}
                        {!! html()->select('client', ['' => 'Select', 'Clients' => $clients])
                            ->class('form-control') !!}
                    </div>

                    <div class="col-md-3 form-group {!! $errors->has('payment_method') ? 'has-error' : '' !!}">
                        {!! html()->label(Lang::get('message.payment-method'))->for('payment_method') !!}
                        {!! html()->select('payment_method', ['paypal' => 'payapal'])
                            ->class('form-control') !!}
                    </div>

                    <div class="col-md-3 form-group {!! $errors->has('promotion_code') ? 'has-error' : '' !!}">
                        {!! html()->label(Lang::get('message.promotion-code'))->for('promotion_code') !!}
                        {!! html()->select('promotion_code', ['' => 'Select', 'Promotions' => $promotion])
                            ->class('form-control') !!}
                    </div>

                    <div class="col-md-3 form-group {!! $errors->has('order_status') ? 'has-error' : '' !!}">
                        {!! html()->label(Lang::get('message.order-status'))->for('order_status') !!}
                        {!! html()->select('order_status', ['Pending' => 'pending', 'Active' => 'active'])
                            ->class('form-control') !!}
                    </div>


                </div>

                <div class="row">

                    <div class="col-md-4 form-group">
                        <p>
                            {!! html()->checkbox('confirmation', true) !!}
                            {{ Lang::get('message.order-confirmation') }}
                        </p>
                    </div>

                    <div class="col-md-4 form-group {!! $errors->has('invoice') ? 'has-error' : '' !!}">
                        <p>
                            {!! html()->checkbox('invoice', true) !!}
                            {{ Lang::get('message.generate-invoice') }}
                        </p>
                    </div>

                    <div class="col-md-4 form-group {!! $errors->has('email') ? 'has-error' : '' !!}">
                        <p>
                            {!! html()->checkbox('email', true) !!}
                            {{ Lang::get('message.send-email') }}
                        </p>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6 form-group {!! $errors->has('product') ? 'has-error' : '' !!}">
                        {!! html()->label(Lang::get('message.product'), 'product')->class('required') !!}
                        {!! html()->select('product', ['' => 'Select', 'Product' => $product])->class('form-control') !!}
                    </div>

                    <div class="col-md-6 form-group {!! $errors->has('domain') ? 'has-error' : '' !!}">
                        {!! html()->label(Lang::get('message.domain'), 'domain') !!}
                        {!! html()->text('domain')->class('form-control') !!}
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-4 form-group {!! $errors->has('subscription') ? 'has-error' : '' !!}">
                        {!! html()->label(Lang::get('message.subscription'), 'subscription') !!}
                        {!! html()->select('subscription', ['' => 'Select', 'Subscription' => $subscription])->class('form-control') !!}
                    </div>

                    <div class="col-md-4 form-group {!! $errors->has('price_override') ? 'has-error' : '' !!}">
                        {!! html()->label(Lang::get('message.price-override'), 'price_override') !!}
                        {!! html()->text('price_override')->class('form-control') !!}
                    </div>

                    <div class="col-md-4 form-group {!! $errors->has('qty') ? 'has-error' : '' !!}">
                        {!! html()->label(Lang::get('message.quantity'), 'qty') !!}
                        {!! html()->text('qty')->class('form-control') !!}
                    </div>


                </div>

            </div>

        </div>

    </div>

</div>


{!! html()->form()->close() !!}
@stop