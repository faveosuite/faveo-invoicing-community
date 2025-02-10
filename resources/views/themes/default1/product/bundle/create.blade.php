@extends('themes.default1.layouts.master')
@section('content')
<div class="box box-primary">

    <div class="content-header">
        {!! Form::open(['url'=>'bundles','method'=>'post']) !!}
        <h4>{{Lang::get('message.bundles')}}	{!! Form::submit(Lang::get('message.save'),['class'=>'form-group btn btn-primary pull-right'])!!}</h4>

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

                    <div class="col-md-4 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('name',Lang::get('message.name'),['class'=>'required']) !!}
                        {!! Form::text('name',null,['class' => 'form-control']) !!}

                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('valid_from') ? 'has-error' : '' }}">
                        <!-- last name -->
                        {!! Form::label('valid_from',Lang::get('message.valid-from')) !!}
                        {!! Form::date('valid_from',\Carbon\Carbon::now(),['class' => 'form-control']) !!}

                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('valid_till') ? 'has-error' : '' }}">
                        <!-- first name -->
                        {!! Form::label('valid_till',Lang::get('message.valid-till')) !!}
                        {!! Form::date('valid_till',null,['class' => 'form-control']) !!}

                    </div>


                </div>

                <div class="row">

                    <div class="col-md-6 form-group {{ $errors->has('items.0') ? 'has-error' : '' }}">

                        {!! Form::label('items',Lang::get('message.bundle-items'),['class'=>'required']) !!}
                        {!! Form::select('items[]',[''=>'Select','Products'=>$products],null,['class'=>'form-control','multiple'=>true]) !!}

                    </div>

                    <div class="col-md-6">

                        <ul class="list-unstyled">

                            <li>
                                <div class="form-group {{ $errors->has('allow_promotion') ? 'has-error' : '' }}">
                                    {!! Form::label('allow_promotion',Lang::get('message.allow-promotion')) !!}
                                    <p>{!! Form::checkbox('allow_promotion',1) !!}  {{Lang::get('message.tick-to-allow-promotion-codes-to-be-used-in-conjunction-with-this-bundle')}}</p>
                                </div>
                            </li>

                            <li>
                                <div class="row">
                                    <div class="col-md-6 form-group {{ $errors->has('uses') ? 'has-error' : '' }}">
                                        {!! Form::label('uses',Lang::get('message.uses')) !!}
                                        {!! Form::text('uses',null,['class'=>'form-control']) !!}
                                    </div>
                                    <div class="col-md-6 form-group {{ $errors->has('maximum_use') ? 'has-error' : '' }}">
                                        {!! Form::label('maximum_uses',Lang::get('message.maximum-use')) !!}
                                        {!! Form::text('maximum_uses',null,['class'=>'form-control']) !!}
                                    </div>
                                </div>
                            </li>

                        </ul>

                    </div>



                </div>

            </div>

        </div>

    </div>

</div>


{!! Form::close() !!}
@stop