@extends('themes.default1.layouts.master')
@section('content')
<div class="box box-primary">

    <div class="content-header">
        {!! html()->form('POST', 'addons')->open() !!}
        <h4>{{ Lang::get('message.addon') }} {!! html()->submit(Lang::get('message.save'))->class('form-group btn btn-primary pull-right') !!}</h4>
    </div>

    <div class="box-body">

        <div class="row">

            <div class="col-md-12">

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
                        {!! html()->label(Lang::get('message.name'))->class('required')->for('name') !!}
                        {!! html()->text('name')->class('form-control') !!}
                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('subscription') ? 'has-error' : '' }}">
                        <!-- subscription -->
                        {!! html()->label(Lang::get('message.subscription'))->class('required')->for('subscription') !!}
                        {!! html()->select('subscription', ['' => 'Select', 'Subscription' => $subscription])->class('form-control') !!}
                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('regular_price') ? 'has-error' : '' }}">
                        <!-- regular price -->
                        {!! html()->label(Lang::get('message.regular-price'))->class('required')->for('regular_price') !!}
                        {!! html()->text('regular_price')->class('form-control') !!}
                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('selling_price') ? 'has-error' : '' }}">
                        <!-- selling price -->
                        {!! html()->label(Lang::get('message.selling-price'))->class('required')->for('selling_price') !!}
                        {!! html()->text('selling_price')->class('form-control') !!}
                    </div>


                </div>

                <div class="row">



                    <div class="col-md-3 form-group {{ $errors->has('tax_addon') ? 'has-error' : '' }}">
                        <!-- tax addon -->
                        {!! html()->label(Lang::get('message.tax-addon'))->for('tax_addon') !!}
                        <p>{!! html()->checkbox('tax_addon', 1) !!} {{ Lang::get('message.charge-tax-on-this-addon') }}</p>
                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('show_on_order') ? 'has-error' : '' }}">
                        <!-- show on order -->
                        {!! html()->label(Lang::get('message.show-on-order'))->for('show_on_order') !!}
                        <p>{!! html()->checkbox('show_on_order', 1) !!} {{ Lang::get('message.show-addon-during-initial-product-order-process') }}</p>
                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('auto_active_payment') ? 'has-error' : '' }}">
                        <!-- auto active payment -->
                        {!! html()->label(Lang::get('message.auto-active-payment'))->for('auto_active_payment') !!}
                        <p>{!! html()->checkbox('auto_active_payment', 1) !!} {{ Lang::get('message.auto-activate-on-payment') }}</p>
                    </div>

                    <div class="col-md-3 form-group {{ $errors->has('suspend_parent') ? 'has-error' : '' }}">
                        <!-- suspend parent -->
                        {!! html()->label(Lang::get('message.suspend-parent-product'))->for('suspend_parent') !!}
                        <p>{!! html()->checkbox('suspend_parent', 1) !!} {{ Lang::get('message.tick-to-suspend-the-parent-product-as-well-when-instances-of-this-addon-are-overdue') }}</p>
                    </div>

                </div>
                <div class="row">

                    <div class="col-md-6 form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                        <!-- description -->
                        {!! html()->label(Lang::get('message.description'))->for('description') !!}
                        {!! html()->textarea('description')->class('form-control') !!}
                    </div>

                    <div class="col-md-6 form-group {{ $errors->has('products') ? 'has-error' : '' }}">
                        <!-- applicable products -->
                        {!! html()->label(Lang::get('message.applicable-products'))->for('products') !!}
                        {!! html()->select('products[]', ['' => 'Select', 'Products' => $product])->class('form-control')->multiple() !!}
                    </div>

                </div>


            </div>

        </div>

    </div>

</div>


{!! html()->form()->close() !!}
@stop