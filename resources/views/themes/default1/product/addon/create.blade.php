@extends('themes.default1.layouts.master')
@section('content')
<div class="box box-primary">

    <div class="content-header">
        {!! Form::open(['url'=>'addons','method'=>'post']) !!}
        <h4>{{Lang::get('message.addon')}}	{!! Form::submit(Lang::get('message.save'),['class'=>'form-group btn btn-primary pull-right'])!!}</h4>

    </div>

    <div class="box-body">

        <div class="row">

            <div class="col-md-12">

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
                    <i class="fa fa-ban"></i>
                    <b>{{Lang::get('message.alert')}}!</b> {{Lang::get('message.success')}}.
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

                <div class="row">

                    <div class="col-md-3 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('name',Lang::get('message.name'),['class'=>'required']) !!}
                        {!! Form::text('name',null,['class' => 'form-control']) !!}

                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('subscription') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('subscription',Lang::get('message.subscription'),['class'=>'required']) !!}
                        {!! Form::select('subscription',[''=>'Select','Subscription'=>$subscription],null,['class' => 'form-control']) !!}

                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('regular_price') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('regular_price',Lang::get('message.regular-price'),['class'=>'required']) !!}
                        {!! Form::text('regular_price',null,['class' => 'form-control']) !!}

                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('selling_price') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('selling_price',Lang::get('message.selling-price'),['class'=>'required']) !!}
                        {!! Form::text('selling_price',null,['class' => 'form-control']) !!}

                    </div>


                </div>

                <div class="row">



                    <div class="col-md-3 form-group {{ $errors->has('tax_addon') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('tax_addon',Lang::get('message.tax-addon')) !!}
                        <p>{!! Form::checkbox('tax_addon',1) !!}  {{Lang::get('message.charge-tax-on-this-addon')}}</p>

                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('show_on_order') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('show_on_order',Lang::get('message.show-on-order')) !!}
                        <p>{!! Form::checkbox('show_on_order',1) !!}  {{Lang::get('message.show-addon-during-initial-product-order-process')}}</p>

                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('auto_active_payment') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('auto_active_payment',Lang::get('message.auto-active-payment')) !!}
                        <p>{!! Form::checkbox('auto_active_payment',1) !!}  {{Lang::get('message.auto-activate-on-payment')}}</p>

                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('suspend_parent') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('suspend_parent',Lang::get('message.suspend-parent-product')) !!}
                        <p>{!! Form::checkbox('suspend_parent',1) !!}  {{Lang::get('message.tick-to-suspend-the-parent-product-as-well-when-instances-of-this-addon-are-overdue')}}</p>

                    </div>

                </div>
                <div class="row">

                    <div class="col-md-6 form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('description',Lang::get('message.description')) !!}
                        {!! Form::textarea('description',null,['class' => 'form-control']) !!}

                    </div>

                    <div class="col-md-6 form-group {{ $errors->has('products') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('products',Lang::get('message.applicable-products')) !!}
                        {!! Form::select('products[]',[''=>'Select','Products'=>$product],null,['class' => 'form-control','multiple'=>true]) !!}

                    </div>

                </div>


            </div>

        </div>

    </div>

</div>


{!! Form::close() !!}
@stop