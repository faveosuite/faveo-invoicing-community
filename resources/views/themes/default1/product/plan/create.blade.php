<div class="modal fade" id="create-plan-option" style="overflow-y: auto !important">
  <div class="modal-dialog">
    <div class="modal-content" style="width:700px;">

      <div class="modal-header">
      <h4 class="modal-title">{{ __('message.create_plans') }}</h4>
    </div>
      <div class="modal-body">
          <div id="alertMessage"></div>
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

            {!! html()->form('POST', url('plans'))->id('plan_form')->open() !!}


            <div class="box-body">

          <div class="row">

              <div class="col-md-12">

              <div class="row">

                <div
                  class="col-md-4 form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                  <!-- name -->
                    {!! html()->label(__('message.name'), 'name')->class('required') !!}
                    {!! html()->text('name')->class('form-control'.($errors->has('name') ? ' is-invalid' : ''))->id('planname') !!}
                    @error('name')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                  <h6 id="plannamecheck"> </h6>
                    <div class="input-group-append">
                    </div>
                </div>
                <div
                  class="col-md-4 form-group {{ $errors->has('product') ? 'has-error' : '' }}">
                  <!-- value -->
                    {!! html()->label(__('message.product'), 'product')->class('required') !!}
                  <select name="product" value="Choose" class="form-control {{$errors->has('product') ? ' is-invalid' : ''}}" id="planproduct" onchange="myProduct()">
                    <option value="">{{ __('message.choose') }}</option>
                    @foreach($products as $key=>$product)
                     @if (Request::old('product') == $key)
                     <option value={{$key}} selected>{{$product}}</option>
                     @else
                     <option value={{$key}}>{{$product}}</option>
                     @endif
                    @endforeach
                  </select>
                    @error('product')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                    <div class="input-group-append">
                    </div>
                  <h6 id="productcheck"></h6>


                </div>
                <div class="col-md-4 form-group plandays {{ $errors->has('days') ? 'has-error' : '' }}">
                  <!-- days -->
                    {!! html()->label( __('message.periods'), 'days')->class('required') !!}
                  <div class="input-group">
                    <select name="days" value="Choose" class="form-control {{$errors->has('days') ? ' is-invalid' : ''}}" id="plandays">
                      <option value="">{{ __('message.choose') }}</option>
                      @foreach($periods as $key=>$period)
                       @if (Request::old('days') == $key)
                     <option value={{$key}} selected>{{$period}}</option>
                     @else
                     <option value={{$key}}>{{$period}}</option>
                     @endif
                      @endforeach
                    </select>&nbsp;
                      @error('days')
                      <span class="error-message"> {{$message}}</span>
                      @enderror
                      <div class="input-group-append">
                      </div>
                    <span class="input-group-text" id="period"><i class="fa fa-plus"></i></span>
                  </div>
                  <h6 id="dayscheck"></h6>

                </div>
                <div class="col-md-12">

                  <table class="table table-responsive table-bordered table-hover" id="dynamic_table">
                    <thead>
                    <tr>
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
                          <input type="number" min="0" step="any" class="form-control float-number-input {{ $errors->has('add_price') ? 'is-invalid' : '' }}" name="add_price[]" value="{{old('add_price.0')}}" id="regular_prices">

                        </td>

                         <td>
                            <input type="number" min="0" step="any" class="form-control float-number-input" value="{{old('offer_price.0')}}" name="offer_price[]">

                        </td>

                        <td>
                            <input type="number" min="0" step="any" class="form-control float-number-input" value="{{old('renew_price.1')}}" name="renew_price[]" id="renew_prices">

                        </td>




                      </tr>

                    </tbody>
                  </table>
                </div>
                <div class="col-sm-12" style="margin-bottom: 10px;">
                  <button class="btn btn-sm btn-default add-more"><i class="fa fa-plus"></i>&nbsp;{{ trans('message.add_price_for_country') }}</button>
                </div>


                <div class="col-md-12 form-group">
                  <!-- description -->
                    {!! html()->label( __('message.price_description'), 'description') !!}
                    {!! html()->text('price_description')->class('form-control'.($errors->has('price_description') ? ' is-invalid' : ''))->placeholder( __('message.enter_price_description')) !!}
                  <h6 id="dayscheck"></h6>

                      <!-- {!! html()->select('days', ['' => 'Select', 'Periods' => $periods])->class('form-control')->id('plandays') !!} -->
                  </div>

                <div class="col-md-6 form-group">
                  <!-- product_quantity -->
                    {!! html()->label( __('message.product_quantity'), 'product_quantity')->class('required') !!}
                    {!! html()->number('product_quantity')->class('form-control only-numbers'.($errors->has('product_quantity') ? ' is-invalid' : ''))->id('prodquant')->disabled()->placeholder( __('message.price_products')) !!}
                    @error('product_quantity')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                    <div class="input-group-append">
                    </div>
                </div>

                  <div class="col-md-6 form-group">
                      <!-- agents -->
                      <label data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="{{ __('message.agents_selected') }}">
                          <i class="fa fa-info-circle" style="cursor: help; font-size: small; color: rgb(60, 141, 188)"></i>
                      </label>
                      {!! html()->label( __('message.agent'), 'agents')->class('required') !!}
                      {!! html()->number('no_of_agents')
                          ->class('form-control only-numbers'.($errors->has('no_of_agents') ? ' is-invalid' : ''))
                          ->id('agentquant')
                          ->disabled()
                          ->placeholder( __('message.price_agents')) !!}
                      @error('no_of_agents')
                      <span class="error-message"> {{$message}}</span>
                      @enderror
                      <div class="input-group-append"></div>
                  </div>
              </div>
            </div>

          </div>

        </div>
         <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default " data-dismiss="modal" id="close-plan"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
                <button type="submit" id="planButton" class="btn btn-primary"><i class="fas fa-save"></i>&nbsp;{{ __('message.save') }}</button>

            </div>
       


      </div>



      {!! html()->form()->close() !!}

    </div>
  </div>
</div>
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
            const product = $('#planproduct').val();
            $.ajax({
                type: 'GET',
                url: "{{ url('get-period') }}",
                data: { product_id: product },
                success: function (data) {
                    if (data.subscription != 1) {
                        $('.plandays').hide();
                    } else {
                        $('.plandays').show();
                    }
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
        myProduct();

        // ===========================
        // VALIDATION
        // ===========================
        function showError($field, message) {
            $field.addClass('is-invalid');
            const $group = $field.closest('.input-group');
            const errorEl = `<span class="error invalid-feedback${$group.length ? ' d-block' : ''}">${message}</span>`;
            if ($group.length) {
                $group.after(errorEl);
            } else {
                $field.after(errorEl);
            }
        }

        function clearErrors() {
            $('.is-invalid').removeClass('is-invalid');
            $('.error.invalid-feedback').remove();
        }

        function validateForm() {
            let isValid = true;
            clearErrors();

            // Required static fields
            const $planname = $('#planname');
            const $planproduct = $('#planproduct');
            if (!$planname.val()) {
                showError($planname, userRequiredFields.planname);
                isValid = false;
            }
            if (!$planproduct.val()) {
                showError($planproduct, userRequiredFields.planproduct);
                isValid = false;
            }

            // Conditional period validation
            const $days = $('#plandays');
            if ($days.is(':visible') && !$days.val()) {
                showError($days, @json(trans('message.period_is_required')));
                isValid = false;
            }

            // Require the enabled quantity field only
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

            // Dynamic rows validation
            $('#dynamic_table tbody tr').each(function () {
                const $row = $(this);
                const $currency = $row.find('select[name="currency[]"]');
                const $regular = $row.find('input[name="add_price[]"]');
                const $renew = $row.find('input[name="renew_price[]"]');

                if ($currency.length && !$currency.val()) {
                    showError($currency, userRequiredFields.currency);
                    isValid = false;
                }
                if ($regular.length && (!$.trim($regular.val()) || parseFloat($regular.val()) <= 0)) {
                    showError($regular, userRequiredFields.regular_price);
                    isValid = false;
                }
                if ($renew.length && (!$.trim($renew.val()) || parseFloat($renew.val()) <= 0)) {
                    showError($renew, userRequiredFields.renew_price);
                    isValid = false;
                }
            });

            return isValid;
        }

        $(document).on('input change', 'input, select', function () {
            $(this).removeClass('is-invalid');
            $(this).siblings('.error.invalid-feedback').remove();
            $(this).closest('.input-group').next('.error.invalid-feedback').remove();
        });

        $('#plan_form').on('submit', function (e) {
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
                const val = $(this).val();
                if (val) selected.push(val);
            });
            $('select[name^="currency"]').each(function () {
                const currentVal = $(this).val();
                $(this).html(buildCurrencyOptions(selected.filter(v => v !== currentVal)));
                $(this).val(currentVal);
            });
        }

        $('.add-more').on('click', function (e) {
            e.preventDefault();

            const rowCount = $('select[name^="currency"]').length;
            if (rowCount >= countries.length) {
                $(this).prop('disabled', true);
                return;
            }

            i++;
            $('#dynamic_table tbody tr:last').after(`
        <tr id="row${i}">
          <td>
            <select name="currency[]" class="form-control">
              ${buildCurrencyOptions()}
            </select>
          </td>
          <td>
            <input type="number" min="0" step="any" class="form-control" name="add_price[]">
          </td>
          <td>
            <input type="number" min="0" step="any" class="form-control" name="offer_price[]">
          </td>
          <td>
            <div class="input-group">
              <input type="number" min="0" step="any" class="form-control" name="renew_price[]">
              <button id="${i}" type="button" class="input-group-text btn_remove ml-2"><i class="fa fa-minus"></i></button>
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
            const button_id = $(this).attr('id');
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

        // ===========================
        // PERIOD MODAL + SAVE
        // ===========================
        $('#period').on('click', function () {
            $('#period-modal-show').modal();
        });

        $('.save-periods').on('click', function () {
            $('#submit1').html("<i class='fa fa-circle-o-notch fa-spin fa-1x fa-fw'></i>{{ __('message.please_wait') }}");
            $.ajax({
                type: 'POST',
                url: "{{ url('postInsertPeriod') }}",
                data: {
                    name: $('#new-period').val(),
                    days: $('#new-days').val(),
                    'select-period': $('#select-period').val(),
                },
                success: function (data) {
                    $('#plandays').append($('<option/>', { value: data.id, text: data.name }));
                    $('#new-period, #new-days, #select-period').val('');
                    const result = '<div class="alert alert-success alert-dismissable">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                        '<strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>{{ __('message.period_added_successfully') }}</div>';
                    $('#error').hide();
                    $('#alertMessage').show().html(result + '.');
                    $('#submit1').html("<i class='fa fa-floppy-o'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                },
                error: function (error) {
                    let html = '<div class="alert alert-danger">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                        '<strong>{{ __('message.whoops') }} </strong>{{ __('message.something_wrong') }}<br><br><ul>';
                    for (key in error.responseJSON.errors) {
                        html += '<li>' + error.responseJSON.errors[key][0] + '</li>';
                    }
                    html += '</ul></div>';
                    $('#alertMessage').hide();
                    $('#error').show().html(html);
                    $('#submit1').html("<i class='fa fa-floppy-o'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                }
            })
        });

        // ===========================
        // UTILITIES
        // ===========================
        $('#close-plan').on('click', function () {
            location.reload();
        });

        // Numeric input helper for floats
        (function setupFloatInputs(){
            const $inputs = $('.float-number-input');
            $inputs.on('keypress', function (event) {
                const key = event.which;
                const val = this.value;
                const controlKeys = [8, 37, 39, 46, 9];
                if (controlKeys.includes(key) || (key >= 16 && key <= 18)) return true;
                if ((key === 46 && val.includes('.')) || (key < 48 || key > 57)) {
                    event.preventDefault();
                }
            });
            $inputs.on('keyup', function () {
                let val = this.value;
                let pos = this.selectionStart;
                if (val === '' || val === '-') return;
                val = val.replace(/[^0-9.]/g, '');
                const dotCount = (val.match(/\./g) || []).length;
                if (dotCount > 1) {
                    const lastDotIndex = val.lastIndexOf('.');
                    val = val.slice(0, lastDotIndex) + val.slice(lastDotIndex + 1);
                    if (pos > lastDotIndex) pos--;
                }
                if (val.startsWith('.')) {
                    val = '0' + val;
                    pos++;
                }
                if (this.value !== val) {
                    this.value = val;
                    this.setSelectionRange(pos, pos);
                }
            });
        })();

    });
</script>