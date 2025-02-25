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
                            <strong>Whoops!</strong> There were some problems with your input.
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>



                    @endif

                <!-- Form  -->
                {!! Form::open(['url'=>'taxes/class','id'=>'taxClass', 'method'=>'post']) !!}

                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    <!-- name -->
                    {!! Form::label('name',Lang::get('Tax Type'),['class'=>'required']) !!}

                    <!-- {!! Form::text('name',null,['class' => 'form-control']) !!} -->
                      <select name="name" id="gst" class="form-control">
                      <option value="Others">Others</option>
                      <option value="Intra State GST">Intra State GST (Same Indian State)</option>
                      <option value="Inter State GST">Inter State GST (Other Indian  State)</option>
                      <option value="Union Territory GST">Union Territory GST (Indian Union Territory)</option>
                      </select>
                    @error('name')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                </div>
                
                {!! Form::open(['url'=>'tax']) !!}
                 <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    <!-- name -->
                    
                    {!! Form::label('tax-name',Lang::get('Tax Name'),['class'=>'required']) !!}
                    {!! Form::text('tax-name',null,['class' => 'form-control','id'=>'taxname']) !!}
                     @error('tax-name')
                     <span class="error-message"> {{$message}}</span>
                     @enderror
                   <h6 id ="namecheck"></h6>
                </div>
           
                 <div class="form-group">
                    <!-- name -->
                    {!! Form::label('status',Lang::get('message.status')) !!}
                    
                </div>

                 <div class="row">
                    <div class="col-md-3 form-group {{ $errors->has('active') ? 'has-error' : '' }}">
                        <!-- name -->
                        {!! Form::label('active',Lang::get('message.active')) !!}
                        {!! Form::radio('active',1,true) !!}

                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('active') ? 'has-error' : '' }}">
                        <!-- name -->
                        {!! Form::label('active',Lang::get('message.inactive')) !!}
                        {!! Form::radio('active',0) !!}

                    </div>
                     @error('active')
                     <span class="error-message"> {{$message}}</span>
                     @enderror
                </div>
                  <div class="form-group {{ $errors->has('country') ? 'has-error' : '' }}">
                    <!-- name -->
                    {!! Form::label('country',Lang::get('message.country')) !!}
                    <br>
                      {!! Form::select('country',[''=>'All Countries','Choose'=>$countries],null,['class' => 'form-control select2','style'=>'width:460px','onChange'=>'getState(this.value);','id'=>'countryvisible']) !!}
                      @error('country')
                      <span class="error-message"> {{$message}}</span>
                      @enderror
                     
                     <input type='text' name="country1" id= "countrynotvisible" class="form-control hide" value="India" readonly>
                   

                </div>
                  <div class="form-group showwhengst {{ $errors->has('state') ? 'has-error' : '' }}" style="display:block">
                    <!-- name -->
                    {!! Form::label('state',Lang::get('message.state')) !!}
                 

                    <select name="state"  class="form-control" id="statess">
                        <option name="state" value=''>All States</option>
                    </select>
                      @error('state')
                      <span class="error-message"> {{$message}}</span>
                      @enderror
                </div>
                 <div class="form-group showwhengst{{ $errors->has('rate') ? 'has-error' : '' }}" style="display:block" >
                    <!-- name -->
                    {!! Form::label('rate',Lang::get('message.rate').' (%)',['class'=>'required']) !!}
                    {!! Form::number('rate',null,['class' => 'form-control','id'=>'rate']) !!}
                     @error('rate')
                     <span class="error-message"> {{$message}}</span>
                     @enderror
                     <h6 id ="ratecheck"></h6>

                </div>
                  
                


            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default " data-dismiss="modal" id="closeTax"><i class="fa fa-times"></i>&nbsp;Close</button>
                <button type="submit" id="submit" class="btn btn-primary"><i class="fas fa-save"></i>&nbsp;Saves</button>

            </div>
            {!! Form::close()  !!}
            <!-- /Form -->
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->  

@if (count($errors) > 0)
    <script type="text/javascript">
        $( document ).ready(function() {
             $('#create-tax-option"').modal('show');
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
                   $('#namecheck').html('This field is required');
                   $('#namecheck').focus();
                   $('#taxname').css("border-color","red");
                   $('#namecheck').css({"color":"red","margin-top":"5px"});
                $('#ratecheck').show();
                $('#ratecheck').html('This field is required');
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
{!! Form::close()  !!}