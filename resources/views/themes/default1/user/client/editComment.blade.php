 <div class="modal fade" id="edit-comment">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                 <h4 class="modal-title">{{Lang::get('message.edit-comment')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button>
               
            </div>
           {!! Form::open(['method' => 'patch', 'id' =>  'comment-edit-form'])!!}
            <div class="modal-body">
                
                <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! Form::label('name',Lang::get('message.comment'),['class'=>'required']) !!}
                     <textarea name="description" id="desc" class="form-control" required="required" rows="6" cols="50"></textarea>
                     <input type="hidden" id="user-id" name="user_id" > 
                    <input type="hidden" name="updated_by_user_id" id="admin-id" > 
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                 <button type="button" id="close" class="btn btn-default" data-dismiss="modal"><i class="fas fa-times">&nbsp;</i>{{ __('message.close') }}</button>
                <button type="submit" class="btn btn-primary " id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> {{ __('message.saving') }}"><i class="fas fa-refresh">&nbsp;&nbsp;</i>{!!Lang::get('message.update')!!}</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
