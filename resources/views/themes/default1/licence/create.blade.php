 <div class="modal fade" id="create-type">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{Lang::get('message.create-license-type')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
          {!! Form::open(['url'=>'license-type','id'=>'licenseForm']) !!}
            <div class="modal-body">
                
                <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! Form::label('name',Lang::get('message.license-type'),['class'=>'required']) !!}
                    <input type="text" name="name" class="form-control" id="name">
                    <div class="input-group-append">
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                 <button type="button" id="close" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;Close</button>
                <button type="submit" class="btn btn-primary " id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-save">&nbsp;</i>{!!Lang::get('Save')!!}</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>


