 <div class="modal fade" id="create-category">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{Lang::get('message.create-category')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {!! html()->form('POST', url('category'))->open() !!}
            <div class="modal-body">
                
                <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('message.category-name'), 'name')->class('required') !!}
                    <input type="text" name="category_name" class="form-control" required="required">
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                 <button type="button" id="close" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;Close</button>
                <button type="submit" class="btn btn-primary " id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('Save')!!}</button>
            </div>
            {!! html()->form()->close() !!}
        </div>
    </div>
</div>
