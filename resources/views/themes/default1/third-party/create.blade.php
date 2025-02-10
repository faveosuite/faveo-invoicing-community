 <div class="modal fade" id="create-third-party-app" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('message.create_third_party_app') }}</h4>
                
            </div>
          {!! Form::open(['url'=>'third-party-keys','id'=>'appForm']) !!}
            <div class="modal-body">
                
                <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! Form::label('name', __('message.app_name'),['class'=>'required']) !!}
                    {!! Form::text('app_name',null,['class' => 'form-control app-name'.($errors->has('app_name') ? ' is-invalid' : ''), 'id'=>'app-name']) !!}
                    @error('app_name')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                    <span class="appnamecheck"></span>
                </div>
                
      
                 <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! Form::label('name',__('message.app_key'),['class'=>'required']) !!}
                    <div class="row">
                     <div class="col-md-8">
                    {!! Form::text('app_key',null,['class' => 'form-control app-key'.($errors->has('app_key') ? ' is-invalid' : ''), 'id'=>'app-key', 'readonly'=>'readonly']) !!}
                         @error('app_key')
                         <span class="error-message"> {{$message}}</span>
                         @enderror
                    <span class="appkeycheck"></span>
                   </div>
                   <div class="col-md-4">
                        <a href="#" class="btn btn-primary get-app-key"><i class="fas fa-sync-alt"></i>&nbsp;{{ __('message.generate_key') }}</a>
                   </div>
                 </div>
                    </div>
                    <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                      {!! Form::label('name', __('message.app_secret'),['class'=>'required']) !!}
                    <div class="row">
                     <div class="col-md-12">
                    {!! Form::text('app_secret',null,['class' => 'form-control app-secret'.($errors->has('app_secret') ? ' is-invalid' : ''), 'id'=>'app-secret']) !!}
                         @error('app_secret')
                         <span class="error-message"> {{$message}}</span>
                         @enderror
                    <span class="appkeycheck"></span>
                   </div>
                   
                    </div>
                  </div>
                </div>
            
            <div class="modal-footer justify-content-between">
                 <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
                <button type="submit" class="btn btn-primary submit " id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> {{ __('message.saving') }}"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button>
            </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

