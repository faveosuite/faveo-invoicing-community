@extends('themes.default1.layouts.master')
@section('title')
  Edit Plan
@stop
@section('content-header')
  <div class="col-sm-6">
        <h1>Edit Plan</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('plans')}}"><i class="fa fa-dashboard"></i> All Plans</a></li>
            <li class="breadcrumb-item active">Edit Plan</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
  <div class="card card-secondary card-outline">




    {!! html()->modelForm($plan, 'PATCH', url('plans/' . $plan->id))
    ->id('plan-form')
    ->class('form-class') !!}
    <div class="card-body">


      <div class="row">

        <div class="col-md-12">

          <div class="row">
            <div class="col-md-4 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
              <!-- first name -->
              {!! html()->label(Lang::get('message.name'), 'name')->class('required') !!}
              {!! html()->text('name')->class('form-control') !!}

            </div>
            <div class="col-md-4 form-group {{ $errors->has('product') ? 'has-error' : '' }}">
              <!-- first name -->
              {!! html()->label(Lang::get('message.product'), 'product')->class('required') !!}
              <select name="product" id="planproduct" class="form-control" onchange="myProduct()">
                <option value="">Choose</option>

                @foreach($products as $key=>$product)
                  <option value="{{$key}}"  <?php  if(in_array($product, $selectedProduct) ) { echo "selected";} ?>>{{$product}}</option>

                @endforeach
              </select>



            </div>
            <div class="col-md-4 form-group plandays {{ $errors->has('days') ? 'has-error' : '' }}">
              <!-- last name -->
              {!! html()->label('Periods', 'days')->class('required') !!}
              <select name="days" id="plan" class="form-control">
                <option value="">Choose</option>

                @foreach($periods as $key=>$period)
                  <option value="{{$key}}" <?php  if(in_array($period, $selectedPeriods) ) { echo "selected";} ?>>{{$period}}</option>

                @endforeach
              </select>

            </div>

            <div class="col-md-12">


                <table class="table table-responsive table-bordered table-hover" id="dynamic_table">
                  <thead>
                    <tr>
                      <th class="col-sm-6" style="width:10%">{{ Lang::get('message.country') }} <span class="text-red">*</span> </th>
                      <th class="col-sm-6" style="width:10%">{{ Lang::get('message.currency') }} <span class="text-red">*</span> </th>
                      <th class="col-sm-6" style="width:10%">{{ Lang::get('message.regular-price') }} <span class="text-red">*</span> </th>
                      <th class="col-sm-3" style="width:10%">
                        {{ Lang::get('Offer Price') }} <span class="text-bold">(%)</span>
                      </th>
                      <th class="col-sm-6" style="width:10%">
                        {{ Lang::get('message.renew-price') }} <span class="text-red">*</span>
                      </th>
                    </tr>
                  </thead>

                  <tbody>
                    @foreach($planPrices as $row)
                      <tr id="row{{$loop->iteration}}" class="form-group {{ $errors->has('add_price.'.$key) ? 'has-error' : '' }}">

                        <td>
                          <select name="country_id[{{ $row['id'] }}]" class="form-control" >
                            @if (0 === $row['country_id'])
                                <option value="0" selected>Default</option>
                            @endif
                            @if (0 !== $row['country_id'])
                              @foreach ($countries as $country)
                                <option value="{{$country['country_id']}}"  @if ($country['country_id'] === $row['country_id'])
                                  {{ 'selected' }}
                                        @endif>
                                  {{ $country['country_name'] }}
                                </option>
                              @endforeach
                            @endif
                          </select>
                        </td>

                        <td>
                          <select name="currency[{{ $row['id'] }}]" class="form-control">
                            <option value="">
                                Choose
                              </option>
                            @foreach ($currency as $code => $name)
                              <option value="{{ $code }}" @if ($code === $row['currency'])
                                  {{ 'selected' }}
                              @endif>
                                {{ $name }}
                              </option>
                            @endforeach
                          </select>
                        </td>

                        <td>
                          <input type="text" class="form-control" name="add_price[{{ $row['id'] }}]" value="{{ $row['add_price'] }}">

                          <td>
                          <input type="text" class="form-control" name="offer_price[{{ $row['id'] }}]" value="{{ $row['offer_price'] }}">
                        </td>
                        </td>

                        <td>
                          <div class="{{ ($row['country_id'] != 0) ? 'input-group' : '' }}">
                            <input type="text" class="form-control" name="renew_price[{{ $row['id'] }}]" value="{{ $row['renew_price'] }}"> &nbsp;&nbsp;
                            @if($row['country_id'] != 0)
                              <span class="input-group-text btn_remove" id="{{$loop->iteration}}"><i class="fa fa-minus"></i></span>
                            @endif
                          </div>
                        </td>

                      </tr>
                    @endforeach
                  </tbody>
                </table>
            </div>

            <div class="col-sm-12" style="margin-bottom: 10px;">
              <button class="btn btn-sm btn-default add-more"><i class="fa fa-plus"></i>&nbsp;{{ trans('message.add_price_for_country') }}</button>
            </div>


            <div class="col-md-4 form-group">
              {!! html()->label('Price Description', 'description') !!}
              {!! html()->text('price_description', $priceDescription)
                  ->class('form-control')
                  ->placeholder('Enter Price Description to be Shown on Pricing Page. eg: Yearly,Monthly,One-Time') !!}
              <h6 id="dayscheck"></h6>
            </div>

            <div class="col-md-4 form-group">
              {!! html()->label('Product Quantity', 'product_quantity')->class('required') !!}
              {!! html()->number('product_quantity', $productQuantity)
                  ->class('form-control')
                  ->id('prodquant')
                  ->attribute('disabled', true)
                  ->placeholder('Pricing for No. of Products') !!}
            </div>

            <div class="col-md-4 form-group">
              <i class="fa fa-info-circle" style="cursor: help; font-size: small; color: rgb(60, 141, 188);">
                <label data-toggle="tooltip" style="font-weight:500;" data-placement="top"
                       title="If '0' Agents Selected, Plan will be for Unlimited Agents."></label>
              </i>
              {!! html()->label('No. of Agents', 'agents')->class('required') !!}
              {!! html()->number('no_of_agents', $agentQuantity)
                  ->class('form-control')
                  ->id('agentquant')
                  ->attribute('disabled', true)
                  ->placeholder('Pricing for No. of Agents') !!}
            </div>

          </div>

        </div>


      </div>
      <div class="box-footer">
      <button type="submit" class="btn btn-primary pull-left"><i class="fas fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button>

    </div>

    </div>

    

  </div>



  {!! html()->closeModelForm() !!}

  <script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip({
            container : 'body'
        });
    });

     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'plan';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'plan';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');


    $( document ).ready(function() {
      var product = document.getElementById('planproduct').value;
      $.ajax({
        type: 'get',
        url : "{{url('get-period')}}",
        data: {'product_id':product},
        success: function (data){
          if(data.subscription != 1 ){
            $('.plandays').hide();
          }
          else{
            $('.plandays').show();
          }
          if(data.agentEnable != 1) {//Check if Product quantity to be sh`own or No. of Agents
            document.getElementById("prodquant").disabled = false;
            document.getElementById("agentquant").disabled = true;

          } else if(data.agentEnable == 1){
            document.getElementById("agentquant").disabled = false;
            document.getElementById("prodquant").disabled = true;
          }
        }
      });

      var i = 1000;
      $(".add-more").click(function (e) {
        e.preventDefault();
        i++;
        $('#dynamic_table tr:last').after(`
        <tr id="row` + i + `">
          <td>
            <select name="country_id[]" class="form-control selectpicker" >
              <option value="" selected disabled>Choose Country</option>
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
              Choose
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
              <input type="text" class="form-control" name="renew_price[]">&nbsp;&nbsp;
              <span id="` + i + `" class="input-group-text btn_remove"><i class="fa fa-minus"></i></span>
            </div>
          </td>

        </tr>`)
      });

      $(document).on('click', '.btn_remove', function () {
        var button_id = $(this).attr("id");
        $('#row' + button_id + '').remove();
      });

    });



    function myProduct(){
      var product = document.getElementById('planproduct').value;
      // console.log(product)
      $.ajax({
        type: 'get',
        url : "{{url('get-period')}}",
        data: {'product_id':product},
        success: function (data){
          console.log(data.subscription);

          if(data.subscription != 1 ){
            $('.plandays').hide();
          }
          else{
            $('.plandays').show();
          }
          if(data.agentEnable != 1) {//Check if Product quantity to be shown or No. of Agents
            document.getElementById("prodquant").disabled = false;
            document.getElementById("agentquant").disabled = true;

          } else if(data.agentEnable == 1){
            document.getElementById("agentquant").disabled = false;
            document.getElementById("prodquant").disabled = true;
          }

          var sub = data['subscription'];

        }
      });
    }
  </script>

@stop