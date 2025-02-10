 <div class="modal fade" id="createComment">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                  <h4 class="modal-title">{{Lang::get('message.add_new_comment')}}</h4>
                <button type="button" class="close" data-dismiss="modal" id="crossclose" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button>
              
            </div>
            {!! Form::open(['url'=>'comment']) !!} 
            <div class="modal-body">
                
                <div class= "form-group">
                    {!! Form::label('name',Lang::get('message.comment'),['class'=>'required']) !!}
                    <textarea name="description" class="form-control" required="required" rows="6" cols="50"></textarea>
                    <input type="hidden" name="user_id" value="{{$client->id}}"> 
                    <input type="hidden" name="updated_by_user_id" value="{{\Auth::user()->id}}"> 
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                 <button type="button" id="commentclose" class="btn btn-default btn-sm" data-dismiss="modal"><i class="fas fa-times">&nbsp;</i>{{ __('message.close') }}</button>
                <button type="submit" class="btn btn-primary btn-sm" id="submit"><i class="fas fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
