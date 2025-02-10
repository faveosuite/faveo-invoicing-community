<div class="modal fade" id="limitModel" tabindex="-1" role="dialog" aria-labelledby="defaultModalLabel" aria-hidden="true">
<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<h4 class="modal-title" id="defaultModalLabel">{{ __('message.enter_installation') }}</h4>
			<!-- <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button> -->
		</div>

		<div class="modal-body">
      <div id="error5"></div>
			<div id="response5"></div>
			   <input type="hidden" name="orderId" value="" id="order5">
		        <div class="form-group">
                    <!-- name -->
                    {!! Form::label('license',Lang::get('message.installation_limit'),['class'=>'required']) !!}
                    <div id="response3"></div>
                     <input name="install-limit" type="number" value="" class="form-control" id="limitnumber">
                        
              </div>
        
		</div>
		
		  <div class="modal-footer justify-content-between">
                <button type="button" id="installLimitClose" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
			  <button type="submit" id="installLimitSave" class="btn btn-primary" ><i class="fas fa-save"></i>&nbsp;{{ __('message.save') }}</button>
            </div>
	</div>
</div>
</div>
@section('script')

@stop