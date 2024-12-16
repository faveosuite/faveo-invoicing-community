<div class="modal fade" id="edit<?php echo $model->id; ?>">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Edit Currency</h4>
            </div>
            <div class="modal-body">
                <!-- Form  -->
                {!! html()->form('POST', url('currenc'))->open() !!}

                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('message.name'), 'name')->class('required') !!}
                    {!! html()->text('name')->class('form-control') !!}
                </div>

                <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('message.code'), 'code')->class('required') !!}
                    {!! html()->text('code')->class('form-control') !!}
                </div>

                <div class="form-group {{ $errors->has('symbol') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('message.symbol'), 'symbol') !!}
                    {!! html()->text('symbol')->class('form-control') !!}
                </div>

                <div class="form-group {{ $errors->has('base_conversion') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('message.base_conversion_rate'), 'base_conversion')->class('required') !!}
                    {!! html()->text('base_conversion')->class('form-control') !!}
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" id="close" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                <input type="submit" class="btn btn-primary" value="{{Lang::get('message.save')}}">
            </div>
            {!! html()->form()->close() !!}
            <!-- /Form -->
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->  