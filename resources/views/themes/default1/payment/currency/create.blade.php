<div class="modal fade" id="create">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Create Currency</h4>
            </div>
            <div class="modal-body">
                <!-- Form  -->
                {!! html()->form('POST', url('currency'))->id('currency')->open() !!}

                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('message.name'), 'name')->class('required') !!}
                    {!! html()->text('name')->class('form-control')->id('name') !!}
                    <h6 id="namecheck"></h6>
                </div>

                <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('message.code'), 'code')->class('required') !!}
                    {!! html()->text('code')->class('form-control')->id('code') !!}
                    <h6 id="codecheck"></h6>
                </div>

                <div class="form-group {{ $errors->has('symbol') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('message.symbol'), 'symbol') !!}
                    {!! html()->text('symbol')->class('form-control')->id('symbol') !!}
                    <h6 id="symbolcheck"></h6>
                </div>

                <div class="form-group {{ $errors->has('base_conversion') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('message.base_conversion_rate'), 'base_conversion')->class('required') !!}
                    {!! html()->text('base_conversion')->class('form-control')->id('conversion') !!}
                    <h6 id="conversioncheck"></h6>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="close" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-floppy-o">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>
            </div>
            {!! html()->form()->close() !!}
            <!-- /Form -->
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->  
<script>
     $(document).ready(function(){
        $('#namecheck').hide();
      $('#codecheck').hide();
      $('#symbolcheck').hide();
      $('#conversioncheck').hide();

      $('#currency').submit(function(){
        //validate name
        function nameCheck()
        {
            var currency_name = $('#name').val();
            if (currency_name.length == ''){
                   $('#namecheck').show(); 
                   $('#namecheck').html('This field is required'); 
                   $('#namecheck').focus();
                   $('#name').css("border-color","red");
                   $('#namecheck').css({"color":"red","margin-top":"5px"});
            }
            else{
                 $('#namecheck').hide();
                 $('#name').css("border-color","");
                 return true;
            }
        }

          //validate code
         function code_check()
        {
            var code_name = $('#code').val();
            if (code_name.length == ''){
                   $('#codecheck').show(); 
                   $('#codecheck').html('This field is required'); 
                   $('#codecheck').focus();
                   $('#code').css("border-color","red");
                   $('#codecheck').css({"color":"red","margin-top":"5px"});
            }
            else{
                 $('#codecheck').hide();
                 $('#code').css("border-color","");
                 return true;
            }
        }
          
          //validate symbol
        function symbol_check()
        {
            var symbol = $('#symbol').val();
            if (symbol.length == ''){
                   $('#symbolcheck').show(); 
                   $('#symbolcheck').html('This field is required'); 
                   $('#symbolcheck').focus();
                   $('#symbol').css("border-color","red");
                   $('#symbolcheck').css({"color":"red","margin-top":"5px"});
            }
            else{
                 $('#symbolcheck').hide();
                 $('#symbol').css("border-color","");
                 return true;
            }
        }
           
           //validate base Conversion
         function conversion_check()
        {
            var conversion = $('#conversion').val();
            if (conversion.length == ''){
                   $('#conversioncheck').show(); 
                   $('#conversioncheck').html('This field is required'); 
                   $('#conversioncheck').focus();
                   $('#conversion').css("border-color","red");
                   $('#conversioncheck').css({"color":"red","margin-top":"5px"});
            }
            else{
                 $('#conversioncheck').hide();
                 $('#conversion').css("border-color","");
                 return true;
            }
        }

     
        nameCheck();
        code_check();
        symbol_check();
        conversion_check();
       
        if(nameCheck() && code_check() && symbol_check() && conversion_check()){
                return true;
             }
            else{
            return false;
          }
      });

    });
</script>