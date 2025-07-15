@extends('themes.default1.layouts.master')
@section('title')
    Create Configurable Groups
@stop
@section('content-header')
    <div class="col-sm-6 md-6">
        <h1>Create Configurable Groups</h1>
    </div>
    <div class="col-sm-6 md-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('configurable-groups')}}"><i class="fa fa-dashboard"></i> Configurable Groups</a></li>
            <li class="breadcrumb-item active">Create Configurable Groups</li>
        </ol>
    </div>
@stop

@section('content')
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <div id="alertMessage12"></div>
            <div id="error"></div>
                <h5>edit Configurable Groups</h5>
        </div>

        <div class="card-body flex">
            <div class="row mb-3">
                <div class="col-sm-6">
                    <div class="form-inline">
                        <label for="display_option" class="mr-2">Group Name:</label>
                        <input type="text" name="display_option" id="display_option" value="{{$group->config_group_name}}" class="form-control w-100"/>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-6">
                    <div class="form-inline">
                        <label for="product_id" class="mr-2">Select Plugin:</label>
                        <select class="form-control w-100 {{$errors->has('disk') ? ' is-invalid' : ''}}" name="product_id" value="{{$group_id}}" id="product_id">
                            <option value="">{{ __('message.choose') }}</option>
                        @foreach($product as $pro)
                                <option value="{{ $pro->id }}">{{ $pro->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-6">
                    <div class="form-inline">
                        <label for="description" class="mr-2">Description:</label>
                        <input type="text" name="description" id="description" class="form-control w-100"/>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-6">
                    <div class="form-inline">
                        <label for="prodcuts" class="mr-2">Assign Products:</label>
                        <select class="form-control w-100 {{$errors->has('disk') ? ' is-invalid' : ''}}" name="products" id="products">
                            <option value="">{{ __('message.choose') }}</option>
                            @foreach($product as $pro)
                                <option value="{{ $pro->id }}">{{ $pro->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <label class="mr-5">Configurable Options:</label>
            <div class="row mb-3">
                <div class="col-sm-6">
                    <a class="btn btn-default btn-sm" data-toggle="modal" data-target="#myModal1"><i class="fa fa-plus"></i> Add New Option</a>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">

                        <table id="custom-table" class="table table-striped table-bordered display">
                            <thead>
                            <tr>
                                <th>Option Name</th>
                                <th>Fields</th>
                                <th>Visibility</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="modal fade" id="myModal1" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Configurable Options</h4>

                </div>
                <div class="modal-body">
                    <div id="alertMessage"></div>

                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        {!! html()->label('Option:')->class('required') !!}
                        <div class="input-group">
                            {!! html()->text('config_option_name')->class('form-control')->id('config_option_name') !!}
                        </div>
                    </div>

                    <div class="form-group {{$errors->has('name')?'has-error':''}}">
                        {!! html()->label('Components:')->class('required') !!}
                        <div id="components-wrapper">
                        <div class="input-group mb-3">
                            {!! html()->text('component_name[]')->class('form-control mr-2')->id('component_name')->placeholder('Select option') !!}
                            {!! html()->text('component_value[]')->class('form-control mr-2')->id('component_value')->placeholder('Enter Value') !!}
                            <button class="btn btn-dark"><i class='fa fa-trash' style='color:white;'> </i></button>
                        </div>
                        </div>
                    </div>
                    <button class="btn btn-default mb-3 add-button1" id="add-button1"><i class="fa fa-plus"></i> Add</button>
                    <table class="table table-responsive table-bordered table-hover" id="dynamic_table">
                        <thead>
                        <tr>
                            <th class="col-sm-3" style="width:18%">{{ Lang::get('message.country') }} <span class="text-red">*</span></th>
                            <th class="col-sm-3" style="width:20%">{{ Lang::get('message.currency') }} <span class="text-red">*</span></th>
                            <th class="col-sm-3" style="width:20%">{{ Lang::get('message.price') }} <span class="text-red">*</span></th>
                            <th class="col-sm-3" style="width:20%">
                                {{ Lang::get('message.offer_price') }} <span class="text-bold">(%)</span>
                            </th>
                            <th class="col-sm-3" style="width:20%">
                                {{ Lang::get('message.renew-price') }} <span class="text-red">*</span>
                            </th>

                        </tr>
                        </thead>

                        <tbody>
                        <tr>
                            <td>
                                <select name="country_id[]" class="form-control {{$errors->has('country_id[]') ? ' is-invalid' : ''}}" id="country">
                                    <option value="0">{{ __('message.default') }}</option>
                                    <div class="input-group-append">
                                    </div>
                                </select>
                            </td>
                            <td>
                                <select name="currency[]" class="form-control {{$errors->has('currency') ? ' is-invalid' : ''}}" id="currency">
                                    <option value="">
                                        {{ __('message.choose') }}
                                    </option>
                                    @foreach ($currency as $code => $name)
                                        @if (Request::old('currency') && in_array($code, Request::old('currency')))
                                            <option value={{$code}} selected>{{$name}}</option>
                                        @else
                                            <option value="{{ $code }}">
                                                {{ $name }}
                                            </option>
                                        @endif

                                    @endforeach
                                </select>

                            </td>
                            <td>
                                <input type="number" class="form-control" name="add_price[]" class="{{ $errors->has('add_price') ? 'is-invalid' : '' }}" value="{{old('add_price.0')}}" id="regular_prices">

                            </td>

                            <td>
                                <input type="number" class="form-control" value="{{old('offer_price.0')}}" name="offer_price[]">

                            </td>

                            <td>
                                <input type="number" class="form-control" value="{{old('renew_price.1')}}" name="renew_price[]" id="renew_prices">

                            </td>




                        </tr>

                        </tbody>
                    </table>
                    <div class="col-sm-12" style="margin-bottom: 10px;">
                        <button class="btn btn-sm btn-default add-more"><i class="fa fa-plus"></i>&nbsp;{{ trans('message.add_price_for_country') }}</button>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
                    <button type="submit" class="form-group btn btn-primary"  onclick="submitAllInfo()" id="submit"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $("#add-button1").click(function (e) {
            e.preventDefault();
            let newRow = `
        <div class="input-group mb-3">
            <input type="text" name="component_name[]" class="form-control mr-2" placeholder="Select option">
            <input type="text" name="component_value[]" class="form-control mr-2" placeholder="Enter Value">
            <button type="button" class="btn btn-dark remove-component">
                <i class="fa fa-trash"></i>
            </button>
        </div>
    `;
            $('#components-wrapper').append(newRow);
        });


        var i = 1;
        $(".add-more").click(function (e) {
            e.preventDefault();
            i++;
            $('#dynamic_table tr:last').after(`
        <tr id="row` + i + `">
          <td>
            <select name="country_id[]" class="form-control" >
              <option value="" selected disabled>{{ __('message.choose_country') }}</option>
              @foreach ($countries as $country)
            <option value="{{$country['country_id']}}">
                  {{ $country['country_name'] }}
            </option>
@endforeach
            </select>
          </td>

          <td>
            <select name="currency[]" class="form-control">
            <option value="">
                  {{ __('message.choose') }}
            </option>
@foreach ($currency as $code => $name)
            <option value="{{ $code }}">
                  {{ $name }}
            </option>
@endforeach
            </select>
          </td>

          <td>
            <input type="text" class="form-control" name="add_price[]">
          </td>
           <td>
            <input type="text" class="form-control" name="offer_price[]">
          </td>

          <td>
            <div class="input-group">
              <input type="text" class="form-control" style="width:25%" name="renew_price[]">&nbsp;&nbsp;
              <span id="` + i + `" class="input-group-text btn_remove"><i class="fa fa-minus"></i></span>
            </div>
          </td>

        </tr>`)
        });



        // remove row
        $(document).on('click', '.remove-component', function() {
            $(this).closest('.input-group').remove();
        });


        $(document).on('click', '.btn_remove', function () {
            var button_id = $(this).attr("id");
            $('#row' + button_id + '').remove();
        });

        function submitAllInfo(){
            var config_option_name=$('#config_option_name').val();
            let components = [];

            let nameInputs = document.querySelectorAll("input[name='component_name[]']");
            let valueInputs = document.querySelectorAll("input[name='component_value[]']");

            nameInputs.forEach((el, index) => {
                components.push({
                    name: el.value,
                    value: valueInputs[index].value
                });
            });


            let plans=[];
            let country =document.querySelectorAll("select[name='country_id[]']");
            let currency=document.querySelectorAll("select[name='currency[]']");
            let add_price=document.querySelectorAll("input[name='add_price[]']");
            let offer_price=document.querySelectorAll("input[name='offer_price[]']");
            let renew_price=document.querySelectorAll("input[name='renew_price[]']");
            country.forEach((el,index)=>{
                plans.push({
                    country:el.value,
                    currency:currency[index].value,
                    add_price:add_price[index].value,
                    offer_price:offer_price[index].value,
                    renew_price:renew_price[index].value
                });
            });

            $.ajax({
                url: "{!! url('configurable-option-creation') !!}",
                method: "post",
                data: { 'config_option_name': config_option_name,
                        'components':components,
                        'plans':plans},
                success: function (data) {
                    if (data.success === true) {
                        var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-check"></i>{{ __('message.success') }}! </strong>' + data.message + '!</div>';
                        $('#alertMessage12').show();
                        $('#error').hide();
                        $('#alertMessage12').html(result);
                        setInterval(function () {
                            $('#alertMessage12').slideUp(5000);
                            location.reload();
                        }, 3000);
                    } else if (data.success === false) {
                        $('#alertMessage12').hide();
                        $('#error').show();
                        var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-ban"></i>{{ __('message.whoops') }} </strong> {{ __('message.something_wrong') }}<br>' + data.message + '!</div>';
                        $('#error').html(result);
                        setInterval(function () {
                            $('#error').slideUp(5000);
                            location.reload();
                        }, 10000);
                    }
                },
                error: function (data) {
                    $('#successmsg').hide();
                    $('#error').show();
                    var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-ban"></i>{{ __('message.whoops') }} </strong> {{ __('message.something_wrong') }}<br>' + data.responseJSON.message + '!</div>';
                    $('#error').html(result);
                    setInterval(function () {
                        $('#error').slideUp(5000);
                        location.reload();
                    }, 10000);
                },

            });

        }

    </script>
@stop