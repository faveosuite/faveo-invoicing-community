@extends('themes.default1.layouts.master')
@section('title')
Create Order
@stop
@section('content')
<div class="box box-primary">

    <div class="content-header">
        @if (count($errors) > 0)
        <div class="alert alert-danger">
            <strong>{{ __('message.whoops') }}</strong> {{ __('message.input_problem') }}<br><br>
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
        {!! Form::open(['url'=>'orders','method'=>'post']) !!}
        <h4>{{Lang::get('message.orders')}}	{!! Form::submit(Lang::get('message.save'),['class'=>'form-group btn btn-primary pull-right'])!!}</h4>

    </div>

    <div class="box-body">

        <div class="row">

            <div class="col-md-12">



                <div class="row">

                    <div class="col-md-3 form-group {{ $errors->has('client') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('client',Lang::get('message.client'),['class'=>'required']) !!}
                        {!! Form::select('client',[''=>'Select','Clients'=>$clients],null,['class' => 'form-control']) !!}

                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('payment_method') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('payment_method',Lang::get('message.payment-method')) !!}
                        {!! Form::select('payment_method',['paypal'=>'payapal'],null,['class' => 'form-control']) !!}

                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('promotion_code') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('promotion_code',Lang::get('message.promotion-code')) !!}
                        {!! Form::select('promotion_code',[''=>'Select','Promotions'=>$promotion],null,['class' => 'form-control']) !!}

                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('order_status') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('order_status',Lang::get('message.order-status')) !!}
                        {!! Form::select('order_status',['Pending'=>'pending','Active'=>'active'],null,['class' => 'form-control']) !!}

                    </div>


                </div>

                <div class="row">

                    <div class="col-md-4 form-group">

                        <p>{!! Form::checkbox('confirmation',1) !!}  {{Lang::get('message.order-confirmation')}}</p>
                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('invoice') ? 'has-error' : '' }}">

                        <p>{!! Form::checkbox('invoice',1) !!}  {{Lang::get('message.generate-invoice')}}</p>
                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('email') ? 'has-error' : '' }}">

                        <p>{!! Form::checkbox('email',1) !!}  {{Lang::get('message.send-email')}}</p>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6 form-group {{ $errors->has('product') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('product',Lang::get('message.product'),['class'=>'required']) !!}
                        {!! Form::select('product',[''=>'Select','Product'=>$product],null,['class' => 'form-control']) !!}

                    </div>

                    <div class="col-md-6 form-group {{ $errors->has('domain') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('domain',Lang::get('message.domain')) !!}
                        {!! Form::text('domain',null,['class' => 'form-control']) !!}

                    </div>

                </div>

                <div class="row">

                    <div class="col-md-4 form-group {{ $errors->has('subscription') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('subscription',Lang::get('message.subscription')) !!}
                        {!! Form::select('subscription',[''=>'Select','Subscription'=>$subscription],null,['class' => 'form-control']) !!}

                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('price_override') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('price_override',Lang::get('message.price-override')) !!}
                        {!! Form::text('price_override',null,['class' => 'form-control']) !!}

                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('qty') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('qty',Lang::get('message.quantity')) !!}
                        {!! Form::text('qty',null,['class' => 'form-control']) !!}

                    </div>


                </div>

            </div>

        </div>

    </div>

</div>


{!! Form::close() !!}
@stop