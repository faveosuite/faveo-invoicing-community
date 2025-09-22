<div class="modal fade" id="create-tax-option">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="defaultModalLabel">{{Lang::get('message.create-tax-class')}}</h4>
                 <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button> 
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

                <!-- Form  -->
                        {!! html()->form('POST', url('taxes/class'))->id('taxClass')->open() !!}

                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    <!-- Tax Type -->
                    {!! html()->label(Lang::get('message.tax-type'))->for('name')->class('required') !!}
                    <select name="name" id="gst" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}">
                        <option value="Others">{{ __('message.others') }}</option>
                        <option value="Intra State GST">Intra State GST (Same Indian State)</option>
                        <option value="Inter State GST">Inter State GST (Other Indian State)</option>
                        <option value="Union Territory GST">Union Territory GST (Indian Union Territory)</option>
                    </select>
                    @error('name')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group {{ $errors->has('tax-name') ? 'has-error' : '' }}">
                    <!-- Tax Name -->
                    {{ html()->label(Lang::get('message.tax_name'))->class('required')->for('tax-name') }}
                    {{ html()->text('tax-name')->class('form-control' . ($errors->has('tax-name') ? ' is-invalid' : ''))->id('taxname') }}
                    @error('tax-name')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                    <h6 id="namecheck"></h6>
                </div>

                <div class="form-group">
                    {{ html()->label(Lang::get('message.status'))->for('status') }}
                </div>

                <div class="row">
                    <div class="col-md-4 form-group {{ $errors->has('active') ? 'has-error' : '' }}">
                        <!-- Active -->
                        {{ html()->label(Lang::get('message.active'))->for('active') }}
                        {{ html()->radio('active', 1, true) }}
                    </div>

                    <div class="col-md-4 form-group {{ $errors->has('active') ? 'has-error' : '' }}">
                        <!-- Inactive -->
                        {{ html()->label(Lang::get('message.inactive'))->for('inactive') }}
                        {{ html()->radio('active', 0) }}
                    </div>
                    @error('active')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group {{ $errors->has('country') ? 'has-error' : '' }}">
                    <!-- Country -->
                    {{ html()->label(Lang::get('message.country'))->for('countryvisible') }}
                    <br>
                    {{ html()->select('country', ['' =>  __('message.all_countries')] + $countries)
                        ->class('form-control select2' . ($errors->has('country') ? ' is-invalid' : ''))
                        ->style('width:460px')
                        ->id('countryvisible')
                        ->attribute('onChange', 'getState(this.value);') }}
                    @error('country')
                    <span class="error-message">{{ $message }}</span>
                    @enderror

                    <input type='text' name="country1" id="countrynotvisible" class="form-control hide" value="India" readonly>
                </div>

                <div class="form-group showwhengst {{ $errors->has('state') ? 'has-error' : '' }}" style="display:block">
                    <!-- State -->
                    {{ html()->label(Lang::get('message.state'))->for('state') }}
                    {{ html()->select('state', ['' => __('message.all_states')])
                        ->class('form-control' . ($errors->has('state') ? ' is-invalid' : ''))
                        ->id('statess') }}
                    @error('state')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group showwhengst {{ $errors->has('rate') ? 'has-error' : '' }}" style="display:block">
                    <!-- Tax Rate -->
                    {{ html()->label(Lang::get('message.rate') . ' (%)')->for('rate')->class('required') }}
                    {{ html()->number('rate')->class('form-control' . ($errors->has('rate') ? ' is-invalid' : ''))->id('rate') }}
                    @error('rate')
                    <span class="error-message">{{ $message }}</span>
                    @enderror
                    <h6 id="ratecheck"></h6>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal" id="closeTax">
                        <i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}
                    </button>
                    <button type="submit" id="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>&nbsp;{{ __('message.save') }}
                    </button>
                </div>

                {!! html()->form()->close() !!}
                <!-- /Form -->
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@if (count($errors) > 0)
    <script type="text/javascript">
        $( document ).ready(function() {
             $('#create-tax-option').modal('show');
        });
    </script>
  @endif
<script>


$("#closeTax").click(function() {
   location.reload();
});
    function getState(val) {
      $.ajax({
            type: "GET",
            url: "{{url('get-state')}}/" + val,
            success: function (data) {
              $("#statess").html(data);
                
            }
        });
    }

   $(document).ready(function(){
       if($('#gst').val() != 'Others') {
           $('#countryvisible').hide();
           $('#countrynotvisible').show();
       } else {
           $('#countryvisible').show();
          $('#countrynotvisible').hide();
       }
    $('#gst').on('change', function() {
      if ( this.value != 'Others')
      {
        $('#taxname').attr('readonly',true);
        if(this.value == 'Intra State GST') {
          $('#taxname').val('CGST+SGST')
        } else if(this.value == 'Inter State GST') {
          $('#taxname').val('IGST')
        } else {
            $('#taxname').val('CGST+UTGST')
        }

         $(document).find('.showwhengst').hide();
         $(document).find('.select2').hide();
          $('#countrynotvisible').show();


      }
      else{
          $('#taxname').attr('readonly',false);
          $('#taxname').val('')
             $(document).find('.showwhengst').show();
            $('.select2').show();
            $('#countrynotvisible').hide();
        }
    });
});
</script>

<script>
     $(document).ready(function(){
      $(function () {
                //Initialize Select2 Elements
                $('.select2').select2()
            });
        $('#namecheck').hide();

      $('#taxClas').submit(function(){
        function tax_nameCheck()
        {
            var tax_name = $('#taxname').val();
            if (tax_name.length == ''){
                   $('#namecheck').show();
                   $('#namecheck').html('{{ __('message.field_required') }}');
                   $('#namecheck').focus();
                   $('#taxname').css("border-color","red");
                   $('#namecheck').css({"color":"red","margin-top":"5px"});
                $('#ratecheck').show();
                $('#ratecheck').html('{{ __('message.field_required') }}');
                $('#ratecheck').focus();
                $('#rate').css("border-color","red");
                $('#ratecheck').css({"color":"red","margin-top":"5px"});
            }
            else{
                 $('#namecheck').hide();
                 $('#taxname').css("border-color","");
                 return true;
            }
        }


        tax_nameCheck();


        if(tax_nameCheck()){
                return true;
             }
            else{
            return false;
          }
      });

    });
</script>

<script>
    $(document).ready(function() {
        function Check_error() {
            const userRequiredFields = {
                taxname:@json(trans('message.tax_details.tax_name')),
                rate:@json(trans('message.tax_details.rate')),


            };


            $('#submit').on('click', function (e) {
                if($('#gst').val() == 'Others') {
                    const userFields = {
                        taxname: $('#taxname'),
                        rate: $('#rate'),
                    };


                    // Clear previous errors
                    Object.values(userFields).forEach(field => {
                        field.removeClass('is-invalid');
                        field.next().next('.error').remove();

                    });

                    let isValid = true;

                    const showError = (field, message) => {
                        field.addClass('is-invalid');
                        field.next().after(`<span class='error invalid-feedback'>${message}</span>`);
                    };

                    // Validate required fields
                    Object.keys(userFields).forEach(field => {
                        if (!userFields[field].val()) {
                            showError(userFields[field], userRequiredFields[field]);
                            isValid = false;
                        }
                    });


                    // If validation fails, prevent form submission
                    if (!isValid) {
                        e.preventDefault();
                    }
                }

            });
            // Function to remove error when input'id' => 'changePasswordForm'ng data
            const removeErrorMessage = (field) => {
                field.classList.remove('is-invalid');
                const error = field.nextElementSibling;
                if (error && error.classList.contains('error')) {
                    error.remove();
                }
            };

            // Add input event listeners for all fields
            ['taxname','rate'].forEach(id => {

                document.getElementById(id).addEventListener('input', function () {
                    removeErrorMessage(this);

                });
            });
        }


       const tax_value=$('#gst').val();
        if (tax_value == 'Others') {
            Check_error();
        }
    });

</script>
{!! html()->form()->close() !!}