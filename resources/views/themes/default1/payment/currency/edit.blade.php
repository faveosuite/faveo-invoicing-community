<div class="modal fade" id="edit<?php echo $model->id; ?>">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ __('message.edit_currency') }}</h4>
            </div>
            <div class="modal-body">
                <!-- Form  -->
                {!! Form::open(['url'=>'currenc']) !!}

                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    <!-- name -->
                    {!! Form::label('name',Lang::get('message.name'),['class'=>'required']) !!}
                    {!! Form::text('name',null,['class' => 'form-control']) !!}

                </div>
                <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                    <!-- name -->
                    {!! Form::label('code',Lang::get('message.code'),['class'=>'required']) !!}
                    {!! Form::text('code',null,['class' => 'form-control']) !!}

                </div>
                <div class="form-group {{ $errors->has('symbol') ? 'has-error' : '' }}">
                    <!-- name -->
                    {!! Form::label('symbol',Lang::get('message.symbol')) !!}
                    {!! Form::text('symbol',null,['class' => 'form-control']) !!}

                </div>
                 <div class="form-group {{ $errors->has('base_conversion') ? 'has-error' : '' }}">
                    <!-- name -->
                    {!! Form::label('base_conversion',Lang::get('message.base_conversion_rate'),['class'=>'required']) !!}
                    {!! Form::text('base_conversion',null,['class' => 'form-control']) !!}

                </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" id="close" class="btn btn-default pull-left" data-dismiss="modal">{{ __('message.close') }}</button>
                <input type="submit" class="btn btn-primary" value="{{Lang::get('message.save')}}">
            </div>
            {!! Form::close()  !!}
            <!-- /Form -->
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->  