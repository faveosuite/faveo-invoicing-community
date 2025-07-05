@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.edit_plan') }}
@stop
@section('content-header')
  <div class="col-sm-6">
        <h1>{{ __('message.edit_plan') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('plans')}}"><i class="fa fa-dashboard"></i> {{ __('message.all_plans') }}</a></li>
            <li class="breadcrumb-item active">{{ __('message.edit_plan') }}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
  <div class="card card-secondary card-outline">

    {!! html()->modelForm($plan, 'PATCH', url('plans/' . $plan->id))->id('editPlan')->open() !!}
    <div class="card-body">

      <div class="row">

        <div class="col-md-12">

          <div class="row">
            <div class="col-md-4 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
              <!-- name -->
                {!! html()->label(Lang::get('message.name'), 'name')->class('required') !!}
                {!! html()->text('name')->class('form-control'.($errors->has('name') ? ' is-invalid' : ''))->id('planname') !!}
              @error('name')
              <span class="error-message"> {{$message}}</span>
              @enderror
              <div class="input-group-append">
              </div>
            </div>
            <div class="col-md-4 form-group {{ $errors->has('product') ? 'has-error' : '' }}">
              <!-- product -->
                {!! html()->label(Lang::get('message.product'), 'product')->class('required') !!}
              <select name="product" id="planproduct" class="form-control {{$errors->has('product') ? ' is-invalid' : ''}}" onchange="myProduct()">
                <option value="">{{ __('message.choose') }}</option>

                @foreach($products as $key=>$product)
                  <option value="{{$key}}"  <?php  if(in_array($product, $selectedProduct) ) { echo "selected";} ?>>{{$product}}</option>
                @endforeach
              </select>
              @error('product')
              <span class="error-message"> {{$message}}</span>
              @enderror
              <div class="input-group-append">
              </div>

            </div>
            <div class="col-md-4 form-group plandays {{ $errors->has('days') ? 'has-error' : '' }}">
              <!-- days-->
                {!! html()->label( __('message.periods'), 'days')->class('required') !!}
              <select name="days" id="plandays" class="form-control {{$errors->has('days') ? ' is-invalid' : ''}}">
                <option value="">{{ __('message.choose') }}</option>

                @foreach($periods as $key=>$period)
                  <option value="{{$key}}" <?php  if(in_array($period, $selectedPeriods) ) { echo "selected";} ?>>{{$period}}</option>

                @endforeach
              </select>
              @error('days')
              <span class="error-message"> {{$message}}</span>
              @enderror
              <div class="input-group-append">
              </div>
            </div>

            <div class="col-md-12">


                <table class="table table-responsive table-bordered table-hover" id="dynamic_table">
                  <thead>
                    <tr>
                      <th class="col-sm-6" style="width:10%">{{ Lang::get('message.currency') }} <span class="text-red">*</span> </th>
                      <th class="col-sm-6" style="width:10%">{{ Lang::get('message.price') }} <span class="text-red">*</span> </th>
                      <th class="col-sm-3" style="width:10%">
                        {{ Lang::get('message.offer_price') }} <span class="text-bold">(%)</span>
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
                          <select name="currency[{{ $row['id'] }}]" class="form-control currency1 {{$errors->has('currency') ? ' is-invalid' : ''}}" id="currency">
                            <option value="">
                              {{ __('message.choose') }}
                              </option>
                            @foreach ($currency as $code => $name)
                              <option value="{{ $code }}" @if ($code === $row['currency'])
                                  {{ 'selected' }}
                              @endif>
                                {{ $name }}
                              </option>
                            @endforeach
                          </select>
                          @error('currency')
                          <span class="error-message"> {{$message}}</span>
                          @enderror
                          <div class="input-group-append">
                          </div>
                        </td>

                        <td>
                          <input type="number" class="form-control regular_price1 {{$errors->has('add_price') ? ' is-invalid' : ''}}" name="add_price[{{ $row['id'] }}]" value="{{ $row['add_price'] }}" id="regular_price">
                          @error('add_price')
                          <span class="error-message"> {{$message}}</span>
                          @enderror
                          <div class="input-group-append">
                          </div>
                          <td>
                          <input type="number" class="form-control" name="offer_price[{{ $row['id'] }}]" value="{{ $row['offer_price'] }}">
                          @error('offer_price')
                          <span class="error-message"> {{$message}}</span>
                          @enderror
                          <div class="input-group-append">
                          </div>
                        </td>
                        </td>

                        <td>
                          <div class="input-group">
                            <input type="number" class="form-control renew_price1 {{$errors->has('renew_price') ? ' is-invalid' : ''}}" name="renew_price[{{ $row['id'] }}]" value="{{ $row['renew_price'] }}" id="renew_price">
                            &nbsp;&nbsp;
                            @error('renew_price')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                            <div class="input-group-append">
                            </div>
                            @if (! $loop->first)
                              <span class="input-group-text btn_remove" id="{{ $loop->iteration }}">
                                    <i class="fa fa-minus"></i>
                              </span>
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
              <!-- last name -->
                {!! html()->label( __('message.price_description'), 'description') !!}
                {!! html()->text('price_description', $priceDescription)->class('form-control'.($errors->has('product_quantity') ? ' is-invalid' : ''))->placeholder( __('message.enter_price_description')) !!}
              @error('description')
              <span class="error-message"> {{$message}}</span>
              @enderror
              <h6 id="dayscheck"></h6>

            </div>
            <div class="col-md-4 form-group">
              <!-- last name -->
                {!! html()->label( __('message.product_quantity'), 'product_quantity')->class('required') !!}
                {!! html()->number('product_quantity', $productQuantity)
                    ->class('form-control only-numbers'.($errors->has('product_quantity') ? ' is-invalid' : ''))
                    ->id('prodquant')
                    ->attribute('disabled', true)
                    ->placeholder( __('message.price_products')) !!}
              @error('product_quantity')
              <span class="error-message"> {{$message}}</span>
              @enderror
              <div class="input-group-append">
              </div>
            </div>

            <div class="col-md-4 form-group">
              <!-- last name -->
                 <i class='fa fa-info-circle' style='cursor: help; font-size: small; color: rgb(60, 141, 188)'<label data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="{{ __('message.agents_selected') }}">
                        </label></i>
                {!! html()->label( __('message.agent'), 'agents')->class('required') !!}
                {!! html()->number('no_of_agents', $agentQuantity)
                    ->class('form-control only-numbers'.($errors->has('no_of_agents') ? ' is-invalid' : ''))
                    ->id('agentquant')
                    ->attribute('disabled', true)
                    ->placeholder( __('message.price_agents')) !!}
              @error('no_of_agents')
              <span class="error-message"> {{$message}}</span>
              @enderror
              <div class="input-group-append">
              </div>
            </div>

          </div>

        </div>
      </div>
      <div class="box-footer">
      <button type="submit" class="btn btn-primary pull-left" id="planButtons"><i class="fas fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button>

    </div>

    </div>

  </div>



  {!! html()->closeModelForm() !!}

  <script>
    $(document).ready(function () {

      // ===========================
      // INIT
      // ===========================
      const userRequiredFields = {
        planname: @json(trans('message.plan_details.planname')),
        planproduct: @json(trans('message.plan_details.planproduct')),
        productquant: @json(trans('message.plan_details.productquant')),
        agentquant: @json(trans('message.plan_details.agentquant')),
        regular_price: @json(trans('message.plan_details.regular_price')),
        renew_price: @json(trans('message.plan_details.renewal_price')),
        currency: @json(trans('message.plan_details.currency')),
      };

      let countries = [
          @foreach ($currency as $code => $name)
        { code: "{{ $code }}", name: "{{ $name }}" }
        @if (!$loop->last),@endif
        @endforeach
      ];
      let i = 1000;

      // ===========================
      // PRODUCT HANDLING
      // ===========================
      function myProduct() {
        let product = $('#planproduct').val();

        $.ajax({
          type: 'GET',
          url: "{{url('get-period')}}",
          data: { product_id: product },
          success: function (data) {
            // Handle subscription days visibility
            if (data.subscription != 1) {
              $('.plandays').hide();
            } else {
              $('.plandays').show();
            }

            // Enable/disable quantity inputs
            if (data.agentEnable != 1) {
              $('#prodquant').prop('disabled', false);
              $('#agentquant').prop('disabled', true);
            } else {
              $('#agentquant').prop('disabled', false);
              $('#prodquant').prop('disabled', true);
            }
          }
        });
      }

      $('#planproduct').on('change', myProduct);

      // Run once on page load
      myProduct();

      // ===========================
      // VALIDATION
      // ===========================
      function validateForm() {
        let isValid = true;

        $('.is-invalid').removeClass('is-invalid');
        $('.error.invalid-feedback').remove();

        // Static required fields
        ['planname', 'planproduct'].forEach(id => {
          let field = $(`#${id}`);
          if (!field.val()) {
            showError(field, userRequiredFields[id]);
            isValid = false;
          }
        });

          // Conditional required field: period/days
          const $days = $('#plandays');
          if ($days.is(':visible') && !$days.val()) {
              showError($days, @json(trans('message.period_is_required')));
              isValid = false;
          }

        const $agent = $('#agentquant');
        const $prod = $('#prodquant');
        if (!$agent.prop('disabled') && !$agent.val()) {
          showError($agent, userRequiredFields.agentquant);
          isValid = false;
        }
        if (!$prod.prop('disabled') && !$prod.val()) {
          showError($prod, userRequiredFields.productquant);
          isValid = false;
        }

        // Dynamic rows
        $('tr[id^="row"], #dynamic_table tr').each(function () {
          let currency = $(this).find('select[name="currency[]"]');
          let regular = $(this).find('input[name="add_price[]"]');
          let renew = $(this).find('input[name="renew_price[]"]');

          if (currency.length && !currency.val()) {
            showError(currency, userRequiredFields.currency);
            isValid = false;
          }
          if (regular.length && (!regular.val() || parseFloat(regular.val()) <= 0)) {
            showError(regular, userRequiredFields.regular_price);
            isValid = false;
          }
          if (renew.length && (!renew.val() || parseFloat(renew.val()) <= 0)) {
            showError(renew, userRequiredFields.renew_price);
            isValid = false;
          }
        });

        return isValid;
      }

      function showError(field, message) {
        field.addClass('is-invalid');
        if (field.closest('.input-group').length) {
          field.closest('.input-group').after(`<span class="error invalid-feedback d-block">${message}</span>`);
        } else {
          field.after(`<span class="error invalid-feedback">${message}</span>`);
        }
      }

      $(document).on('input change', 'input, select', function () {
        $(this).removeClass('is-invalid');
        $(this).siblings('.error').remove();
        $(this).closest('.input-group').next('.error').remove();
      });

      $('#editPlan').on('submit', function (e) {
        if (!validateForm()) e.preventDefault();
      });

      // ===========================
      // CURRENCY HANDLING
      // ===========================
      function buildCurrencyOptions(exclude = []) {
        let options = `<option value="">${@json(__('message.choose'))}</option>`;
        countries.forEach(c => {
          if (!exclude.includes(c.code)) {
            options += `<option value="${c.code}">${c.name}</option>`;
          }
        });
        return options;
      }

      function refreshCurrencyDropdowns() {
        let selected = [];
        $('select[name^="currency"]').each(function () {
          let val = $(this).val();
          if (val) selected.push(val);
        });

        $('select[name^="currency"]').each(function () {
          let currentVal = $(this).val();
          $(this).html(buildCurrencyOptions(selected.filter(v => v !== currentVal)));
          $(this).val(currentVal);
        });
      }

      $(".add-more").click(function (e) {
        e.preventDefault();
        let rowCount = $('select[name^="currency"]').length;
        if (rowCount >= countries.length) {
          $(this).prop('disabled', true);
          return;
        }
        i++;
        $('#dynamic_table tr:last').after(`
      <tr id="row${i}">
          <td>
              <select name="currency[]" class="form-control currency-dropdown">
                  ${buildCurrencyOptions()}
              </select>
          </td>
          <td>
              <input type="number" class="form-control" name="add_price[]">
          </td>
          <td>
              <input type="number" class="form-control" name="offer_price[]">
          </td>
          <td>
              <div class="input-group">
                  <input type="number" class="form-control" name="renew_price[]">
                  <button id="${i}" class="input-group-text btn_remove ml-2">
                      <i class="fa fa-minus"></i>
                  </button>
              </div>
          </td>
      </tr>
    `);
        refreshCurrencyDropdowns();
        if ($('select[name^="currency"]').length >= countries.length) {
          $(this).prop('disabled', true);
        }
      });

      $(document).on('click', '.btn_remove', function () {
        let button_id = $(this).attr("id");
        $('#row' + button_id).remove();
        if ($('select[name^="currency"]').length < countries.length) {
          $('.add-more').prop('disabled', false);
        }
        refreshCurrencyDropdowns();
      });

      $(document).on('change', 'select[name="currency[]"]', function () {
        refreshCurrencyDropdowns();
      });

      if ($('select[name^="currency"]').length >= countries.length) {
        $('.add-more').prop('disabled', true);
      }

      refreshCurrencyDropdowns();

    });

  </script>

@stop