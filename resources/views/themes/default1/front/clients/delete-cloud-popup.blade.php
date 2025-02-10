
<button class="btn btn-light-scale-2 btn-sm text-dark open-deleteTenantDialog" data-toggle="modal" data-target="#deleteConfirmationModal">
    <i class="fa fa-trash" data-toggle="tooltip" title="{{ __('message.click_cloud')}}"></i>&nbsp;
</button>

<!-- Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
             <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel" style="font-size: large;">{{ __('message.delete_confirm')}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
            <div class="modal-body">
                <p>{{ __('message.delete_cloud')}}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ __('message.cancel')}}</button>
                <a href="{{ url('delete/domain/'.$orderNumber.'/1') }}" class="btn btn-primary">{{ __('message.delete')}}</a>
            </div>
        </div>
    </div>
</div>

