@extends('themes.default1.layouts.master')
@section('title')
Payment
@stop
@section('content-header')
<h1>
    {{ __('message.generate_payment') }}
</h1>
  <ol class="breadcrumb">
        <li><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
        <li><a href="{{url('clients')}}">{{ __('message.all-users') }}</a></li>
        <li><a href="{{url('clients/'.$userid)}}">{{ __('message.view_user') }}</a></li>
        <li class="active">{{ __('message.payment') }}</li>
      </ol>
@stop
@section('content')

<div class="box box-primary">

    <div class="box-header">
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
                     <i class="fa fa-check"></i>
                     <b>{{Lang::get('message.success')}}!</b> {{Lang::get('message.success')}}.
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
        {!! Form::open(['url'=>'payment/receive/'.$invoice_id,'method'=>'post']) !!}

        <h4>{{Lang::get('message.payment')}}  ({{ __('message.invoice_number') }}: {{$invoice->number}})	 <button type="submit" class="form-group btn btn-primary pull-right" id="submit"><i class="fa fa-floppy-o">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button></h4>

    </div>

    <div class="box-body">

        <div class="row">

            <div class="col-md-12">

                

                <div class="row">

                    <div class="col-md-4 form-group {{ $errors->has('invoice_status') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('payment_date',Lang::get('message.date-of-payment'),['class'=>'required']) !!}
                        {!! Form::text('payment_date',null,['class' => 'form-control']) !!}

                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('payment_method') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('payment_method',Lang::get('message.payment-method')) !!}
                        {!! Form::select('payment_method',[''=>'Choose','cash'=>'Cash','check'=>'Check','online payment'=>'Online Payment','razorpay'=>'Razorpay'],$payment_method,['class' => 'form-control']) !!}

                    </div>

                    
                    <div class="col-md-4 form-group {{ $errors->has('amount') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('amount',Lang::get('message.amount')) !!}
                        {!! Form::text('amount',null,['class' => 'form-control']) !!}

                    </div>

                    


                </div>


            </div>

        </div>

    </div>

</div>


{!! Form::close() !!}
@stop
@section('datepicker')
<script type="text/javascript">
$(function () {
    $('#payment_date').datetimepicker({
        format: 'YYYY-MM-DD'
    });
});
</script>
@stop