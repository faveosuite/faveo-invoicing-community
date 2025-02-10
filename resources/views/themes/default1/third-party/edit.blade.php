{!! html()->form('PATCH', null)->id('app-edit-form')->open() !!}
<div class="modal fade" id="edit-app" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('message.edit_third_party_app') }}</h4>
              
            </div>

            <div class="modal-body">
                
                <div class= "form-group">
                    {!! html()->label(Lang::get('message.app_name'))->class('required') !!}
                    <input type="text" name="app_name" id="name" class="form-control app-name {{$errors->has('app_name') ? ' is-invalid' : ''}}">
                    @error('name')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                    <span class="appnamecheck"></span>
                </div>
                 <div class= "form-group">
                     {!! html()->label(Lang::get('message.app_key'))->for('key')->class('required') !!}
                     <div class="row">
                     <div class="col-md-8">
                    
                    <input type="text" name="app_key" id="key" class="form-control app-key {{$errors->has('app_key') ? ' is-invalid' : ''}}" readonly>
                         @error('app_key')
                         <span class="error-message"> {{$message}}</span>
                         @enderror
                    <span class="appkeycheck"></span>
                   </div>
                   <div class="col-md-4">
                        <a href="#" class="btn btn-primary get-app-key" id="get-app-key"><i class="fas fa-sync-alt"></i>&nbsp;{{ __('message.generate_key') }}</a>
                   </div>
                    </div>
                </div>
                 <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                     {!! html()->label(Lang::get('message.app_secret'))->for('name')->class('required') !!}
                     <div class="row">
                     <div class="col-md-12">
                    <input type="text" name="app_secret" id="secret" class="form-control {{$errors->has('app_secret') ? ' is-invalid' : ''}}">
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
                <button type="submit" class="btn btn-primary" id="submit" data-loading-text="<i class='fa fa-save'>&nbsp;</i> {{ __('message.saving') }}"><i class="fa fa-sync-alt">&nbsp;</i>{!!Lang::get('message.update')!!}</button>
            </div>
        </div>
    </div>
</div>
{!! html()->form()->close() !!}
