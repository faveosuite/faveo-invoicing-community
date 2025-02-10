<div class="modal fade" id="create-plan-option" style="overflow-y: auto !important">
  <div class="modal-dialog">
    <div class="modal-content" style="width:700px;">

      <div class="modal-header">
      <h4 class="modal-title">{{ __('message.create_plans') }}</h4>
    </div>
      <div class="modal-body">

        @if (count($errors) > 0)

                        <div class="alert alert-danger alert-dismissable">
                            <strong>{{ __('message.whoops') }}</strong> {{ __('message.input_problem') }}
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>



                    @endif



        {!! Form::open(['url'=>'plans','method'=>'post','id'=> 'plan']) !!}


        <div class="box-body">

          <div class="row">

              <div class="col-md-12">

              <div class="row">

                <div
                  class="col-md-4 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                  <!-- first name -->
                  {!! Form::label('name',Lang::get('message.name'),['class'=>'required']) !!}
                  {!! Form::text('name',null,['class' => 'form-control','id'=>'planname']) !!}
                  <h6 id="plannamecheck"> </h6>

                </div>
                <div
                  class="col-md-4 form-group {{ $errors->has('product') ? 'has-error' : '' }}">
                  <!-- first name -->
                  {!! Form::label('product',Lang::get('message.product'),['class'=>'required']) !!}
                  <select name="product" value="Choose" class="form-control" id="planproduct" onchange="myProduct()">
                    <option value="">{{ __('message.choose') }}</option>
                    @foreach($products as $key=>$product)
                     @if (Request::old('product') == $key)
                     <option value={{$key}} selected>{{$product}}</option>
                     @else
                     <option value={{$key}}>{{$product}}</option>
                     @endif
                    @endforeach
                  </select>
                  <!--  {!! Form::select('product',[''=>'Select','Products'=>$products],null,['class' => 'form-control','id'=>'planproduct']) !!} -->
                  <h6 id="productcheck"></h6>


                </div>
                <div
                  class="col-md-4 form-group plandays {{ $errors->has('days') ? 'has-error' : '' }}">
                  <!-- last name -->
                  {!! Form::label('days','Periods',['class'=>'required']) !!}
                  <div class="input-group">
                    <select name="days" value="Choose" class="form-control" id="plandays">
                      <option value="">{{ __('message.choose') }}</option>
                      @foreach($periods as $key=>$period)
                       @if (Request::old('days') == $key)
                     <option value={{$key}} selected>{{$period}}</option>
                     @else
                     <option value={{$key}}>{{$period}}</option>
                     @endif
                      @endforeach
                    </select>&nbsp;
                    <span class="input-group-text" id="period"><i class="fa fa-plus"></i></span>
                  </div>
                  <h6 id="dayscheck"></h6>

                  <!-- {!! Form::select('days',[''=>'Select','Periods'=>$periods],null,['class' => 'form-control','id'=>'plandays']) !!} -->
                </div>
                <div class="col-md-12">

                  <table class="table table-responsive table-bordered table-hover" id="dynamic_table">
                    <thead>
                    <tr>
                      <th class="col-sm-3" style="width:18%">{{ Lang::get('message.country') }} <span class="text-red">*</span></th>
                      <th class="col-sm-3" style="width:20%">{{ Lang::get('message.currency') }} <span class="text-red">*</span></th>
                      <th class="col-sm-3" style="width:20%">{{ Lang::get('message.regular-price') }} <span class="text-red">*</span></th>
                      <th class="col-sm-3" style="width:20%">
                        {{ Lang::get('Offer Price') }} <span class="text-bold">(%)</span>
                      </th>
                      <th class="col-sm-3" style="width:20%">
                        {{ Lang::get('message.renew-price') }} <span class="text-red">*</span>
                      </th>
                      
                    </tr>
                    </thead>

                    <tbody>
                      <tr>
                        <td>
                          <select name="country_id[]" class="form-control" >
                            <option value="0">{{ __('message.default') }}</option>

                          </select>
                        </td>
                        <td>
                          <select name="currency[]" class="form-control">
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
                          <input type="text" class="form-control" name="add_price[]" class="{{ $errors->has('add_prices') ? 'has-error' : '' }}" value="{{old('add_price.0')}}">
                        </td>

                         <td>
                            <input type="text" class="form-control" value="{{old('offer_price.0')}}" name="offer_price[]">

                        </td>

                        <td>
                            <input type="text" class="form-control" value="{{old('renew_price.1')}}" name="renew_price[]">
                        </td>

                       


                      </tr>

                    </tbody>
                  </table>
                </div>
                <div class="col-sm-12" style="margin-bottom: 10px;">
                  <button class="btn btn-sm btn-default add-more"><i class="fa fa-plus"></i>&nbsp;{{ trans('message.add_price_for_country') }}</button>
                </div>


                <div class="col-md-12 form-group">
                  <!-- last name -->
                  {!! Form::label('description','Price Description') !!}
                    {!! Form::text("price_description", null, ['class' => 'form-control', 'placeholder' => __('message.price_description')]) !!}
                  <h6 id="dayscheck"></h6>

                  <!-- {!! Form::select('days',[''=>'Select','Periods'=>$periods],null,['class' => 'form-control','id'=>'plandays']) !!} -->
                </div>

                <div class="col-md-6 form-group">
                  <!-- last name -->
                  {!! Form::label('product_quantity','Product Quantity',['class'=>'required'])!!}
                  {!! Form::number("product_quantity",null,['class' =>
                  'form-control','disabled'=>'disabled','id'=>'prodquant','placeholder' => __('message.price_products')])
                  !!}

                </div>

                <div class="col-md-6 form-group">
                  <!-- last name -->
                  <i class='fa fa-info-circle' style='cursor: help; font-size: small; color: rgb(60, 141, 188)'<label data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="{{ __('message.agents_selected') }}">
                        </label></i>

                    {!! Form::label('agents', __('message.agent'), ['class' => 'required']) !!}
                  {!! Form::number("no_of_agents",null,['class' => 'form-control'
                  ,'disabled'=>'disabled','id'=>'agentquant', 'placeholder' => __('message.price_agents')]) !!}

                </div>
              </div>
            </div>

          </div>

        </div>
         <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default " data-dismiss="modal" id="close-plan"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
                <button type="submit"  class="btn btn-primary"><i class="fas fa-save"></i>&nbsp;{{ __('message.save') }}</button>

            </div>
       


      </div>



      {!! Form::close() !!}

    </div>
  </div>
</div>


<script>

$("#close-plan").click(function() {
   location.reload();
});
      
  $(document).ready(function(){
    myProduct();
  })
  /**
   * Add Periods In the same Modal as Create Plan
   *
   * @author Ashutosh Pathak <ashutosh.pathak@ladybirdweb.com>
   *
   * @date   2019-01-08T10:35:38+0530
   *
   */

  $(function () {
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

    $(document).on('click', '.btn_remove', function () {
      var button_id = $(this).attr("id");
      $('#row' + button_id + '').remove();
    });


    $("#period").on('click', function () {
      $("#period-modal-show").modal();
    })
    $('.save-periods').on('click', function () {
      $("#submit1").html("<i class='fa fa-circle-o-notch fa-spin fa-1x fa-fw'></i>{{ __('message.please_wait') }}");
      $.ajax({
        type: 'POST',
        url: "{{ url('postInsertPeriod') }}",
        data: {
          "name": $('#new-period').val(),
          "days": $('#new-days').val(),
          'select-period': $('#select-period').val(),
        },
        success: function (data) {
          $('#plandays').append($("<option/>", {
            value: data.id,
            text: data.name,
          }))
          $('#new-period').val("");
          $('#new-days').val("");
          $('#select-period').val("");
          var result =
            '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>{{ __('message.period_added_successfully') }}</div>';
          $('#error').hide();
          $('#alertMessage').show();
          $('#alertMessage').html(result + ".");
          $("#submit1").html("<i class='fa fa-floppy-o'>&nbsp;&nbsp;</i>Save");
        },
        error: function (error) {
          var html =
            '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>{{ __('message.whoops') }} </strong>{{ __('message.something_wrong') }}<br><br><ul>';
          for (key in error.responseJSON.errors) {
            html += '<li>' + error.responseJSON.errors[key][0] + '</li>'

          }
          html += '</ul></div>';
          $('#alertMessage').hide();
          $('#error').show();
          document.getElementById('error').innerHTML = html;
          $("#submit1").html("<i class='fa fa-floppy-o'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
        }

      })
    })
  })
</script>
<script>
  function myProduct() {
    var product = document.getElementById('planproduct').value;
    $.ajax({
      type: 'get',
      url: "{{ url('get-period') }}",
      data: {
        'product_id': product
      },
      success: function (data) {
        if (data.subscription != 1) { //Check if Periods to be shown or nor
          $('.plandays').hide();
        } else {
          $('.plandays').show();
        }
        if (data.agentEnable != 1) { //Check if Product quantity to be shown or No. of Agents
          document.getElementById("prodquant").disabled = false;
          document.getElementById("agentquant").disabled = true;

        } else if (data.agentEnable == 1) {
          document.getElementById("agentquant").disabled = false;
          document.getElementById("prodquant").disabled = true;
        }
      }
    });
  }
</script>