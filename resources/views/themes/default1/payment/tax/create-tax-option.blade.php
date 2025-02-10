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
                {!! Form::open(['url'=>'taxes/class','id'=>'taxClass', 'method'=>'post']) !!}

                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    <!-- name -->
                    {!! Form::label('name',Lang::get('message.tax-type'),['class'=>'required']) !!}

                    <!-- {!! Form::text('name',null,['class' => 'form-control']) !!} -->
                      <select name="name" id="gst" class="form-control">
                      <option value="Others">{{ __('message.others') }}</option>
                       @if($options->tax_enable)
                      <option value="Intra State GST">Intra State GST (Same Indian State)</option>
                      <option value="Inter State GST">Inter State GST (Other Indian  State)</option>
                      <option value="Union Territory GST">Union Territory GST (Indian Union Territory)</option>
                        @endif
                      </select>
                </div>
                
                {!! Form::open(['url'=>'tax']) !!}
                 <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    <!-- name -->
                    
                    {!! Form::label('tax-name',Lang::get('Tax Name'),['class'=>'required']) !!}
                    {!! Form::text('tax-name',null,['class' => 'form-control','id'=>'taxname']) !!}
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
                </div>
                  <div class="form-group {{ $errors->has('country') ? 'has-error' : '' }}">
                    <!-- name -->
                    {!! Form::label('country',Lang::get('message.country')) !!}
                    <br>
                      {!! Form::select('country',[''=>'All Countries','Choose'=>$countries],null,['class' => 'form-control select2','style'=>'width:460px','onChange'=>'getState(this.value);','id'=>'countryvisible']) !!}

                     
                     <input type='text' name="country1" id= "countrynotvisible" class="form-control hide" value="India" readonly>
                   

                </div>
                  <div class="form-group showwhengst {{ $errors->has('state') ? 'has-error' : '' }}" style="display:block">
                    <!-- name -->
                    {!! Form::label('state',Lang::get('message.state')) !!}
                 

                    <select name="state"  class="form-control" id="statess">
                        <option name="state" value=''>{{ __('message.all_states') }}</option>
                    </select>

                </div>
                 <div class="form-group showwhengst{{ $errors->has('rate') ? 'has-error' : '' }}" style="display:block" >
                    <!-- name -->
                    {!! Form::label('rate',Lang::get('message.rate').' (%)',['class'=>'required']) !!}
                    {!! Form::number('rate',null,['class' => 'form-control','id'=>'rate']) !!}
                  

                </div>
                  
                


            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default " data-dismiss="modal" id="closeTax"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
                <button type="submit"  class="btn btn-primary"><i class="fas fa-save"></i>&nbsp;{{ __('message.save') }}</button>

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
      
      $('#taxClass').submit(function(){
        function tax_nameCheck()
        {
            var tax_name = $('#taxname').val();
            if (tax_name.length == ''){
                   $('#namecheck').show(); 
                   $('#namecheck').html('{{ __('message.field_required') }}');
                   $('#namecheck').focus();
                   $('#taxname').css("border-color","red");
                   $('#namecheck').css({"color":"red","margin-top":"5px"});
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
{!! Form::close()  !!}