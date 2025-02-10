<div class="modal fade" id="licesnseModal" tabindex="-1" role="dialog" aria-labelledby="defaultModalLabel" aria-hidden="true">
<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<h4 class="modal-title" id="defaultModalLabel">{{ __('message.enter_domain_ip')}}</h4>
			<!-- <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button> -->
		</div>
		<div class="modal-body">
			<div id="response"></div>
			   <input type="hidden" name="orderId" id="orderId">
		  <div class="form-group {{ $errors->has('domain') ? 'has-error' : '' }}">
                    <!-- name -->
                    {!! Form::label('domain',Lang::get('message.domain'),['class'=>'required']) !!}
                    {!! Form::text('domain', null, ['class' => 'form-control domainss', 'id' => 'newDomain', 'required' => 'required', 'placeholder' => __('message.enter_domain_name')]) !!}
                           <h6 id ="domaincheck"></h6>
                </div>
          <div class="form-group">
			<span style="color:red;">*&nbsp {{ __('message.changing_domain')}} </span>
		</div>  
		</div>
		
		  <div class="modal-footer justify-content-between">
                <button type="button" id="close" class="btn btn-default" data-dismiss="modal">{{ __('message.close')}}</button>
              <button type="submit" id="licenseSave"  class="btn btn-primary" value="{{Lang::get('message.save')}}"><i class="fas fa-save"></i></i></button>
            </div>
	</div>
</div>
</div>
@section('script')
 <script type="text/javascript">
 	
 
      $('#domaincheck').hide();
function validdomaincheck(){
            var pattern = new RegExp(/^((?!-))(xn--)?[a-z0-9][a-z0-9-_]{0,61}[a-z0-9]{0,1}\.(xn--)?([a-z0-9\-]{1,61}|[a-z0-9-]{1,30}\.[a-z]{2,})$/);
              if (pattern.test($('#newDomain').val())){
                 $('#domaincheck').hide();
                 $('#newDomain').css("border-color","");
                 return true;
               
              }
              else{
                 $('#domaincheck').show();
                  $('#domaincheck').html("{{ __('message.enter_valid_domain') }}");
                 $('#domaincheck').focus();
                  $('#newDomain').css("border-color","red");
                 $('#domaincheck').css({"color":"red","margin-top":"5px"});
                   domErr = false;
                    return false;
              
      }

    }


</script>
@stop