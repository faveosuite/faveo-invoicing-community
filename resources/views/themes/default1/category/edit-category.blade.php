 <div class="modal fade" id="edit-category">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{Lang::get('message.edit-category')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

           {!! Form::open(['method' => 'patch', 'id' =>  'category-edit-form'])!!}
            <div class="modal-body">
                
                <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! Form::label('name',Lang::get('message.category-name'),['class'=>'required']) !!}
                    <input type="text" name="category_name" id="cname" class="form-control" required="required">
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                 <button type="button" id="close" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close')}}</button>
                <button type="submit" class="btn btn-primary " id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
