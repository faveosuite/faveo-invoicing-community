<div class="modal fade" id="domainModal" tabindex="-1" role="dialog" aria-labelledby="reissueModalLabel" aria-hidden="true">

            <div class="modal-dialog">

                <div class="modal-content">

                    <div class="modal-header">

                        <h4 class="modal-title" id="reissueModalLabel">{{ __('message.are_you_sure')}}</h4>

                    </div>

                    <div class="modal-body">
                    	<div id="response1"></div>
			              <input type="hidden" name="orderId" id="orderId">

                        <p>{{ __('message.reissuing_license')}}</p>
                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('message.close')}}</button>

                        <button type="submit" id="domainSave" class="btn btn-primary">{{ __('message.proceed')}}</button>
                    </div>
                </div>
            </div>
        </div>
@section('script')

@stop
