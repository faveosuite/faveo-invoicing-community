<div class="modal fade" id="licenseEndsModal" tabindex="-1" role="dialog" aria-labelledby="defaultModalLabel" aria-hidden="true">
<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<h4 class="modal-title" id="defaultModalLabel">{{ __('message.enter_license_expiry') }}</h4>
			<!-- <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button> -->
		</div>

		<div class="modal-body">
      <div id="error1"></div>
			<div id="response3"></div>
			   <input type="hidden" name="orderId" value="" id="order2">
		        <div class="form-group">
                    <!-- name -->
                    {!! Form::label('license',Lang::get('message.license_end'),['class'=>'required']) !!}
                   

                         </div>
                          <div id="response3"></div>

                    <div class="input-group date" id="licenseEnds" data-target-input="nearest">
                        <input type="text" name="ends_at" id="newDate2" class="form-control datetimepicker-input" autocomplete="off"  data-target="#licenseEnds"/>
                        <div class="input-group-append" data-target="#licenseEnds" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>

                        </div>


                    </div>

        
		</div>
		
		  <div class="modal-footer justify-content-between">
                <button type="button" id="close" class="btn btn-default " data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
                <button type="submit" id="licenseExpSave" class="btn btn-primary"><i class="fas fa-save"></i>&nbsp;{{ __('message.save') }}</button>
            </div>
	</div>
</div>
</div>
@section('script')

@stop