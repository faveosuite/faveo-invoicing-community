{!! html()->form('POST', url('third-party-keys'))->id('appForm')->open() !!}
<div class="modal fade" id="create-third-party-app" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Create third-party-app</h4>

            </div>
            <div class="modal-body">

                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('App name'), 'app_name')->class('required') !!}
                    {!! html()->text('app_name')->class('form-control app-name'.($errors->has('app_name') ? ' is-invalid' : ''))->id('app-name') !!}
                    @error('app_name')
                    <span class="error-message"> {{$message}}</span>
                    @enderror
                    <span class="appnamecheck"></span>
                </div>

                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('App key'), 'app_key')->class('required') !!}
                    <div class="row">
                        <div class="col-md-8">
                            {!! html()->text('app_key')->class('form-control app-key'.($errors->has('app_key') ? ' is-invalid' : ''))->id('app-key')->attribute('readonly', true) !!}
                            @error('app_key')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                            <span class="appkeycheck"></span>
                        </div>
                        <div class="col-md-4">
                            <a href="#" class="btn btn-primary get-app-key"><i class="fas fa-sync-alt"></i>&nbsp;Generate key</a>
                        </div>
                    </div>
                </div>

                <div class="form-group {{ $errors->has('app_secret') ? 'has-error' : '' }}">
                    {!! html()->label(Lang::get('App Secret'), 'app_secret')->class('required') !!}
                    <div class="row">
                        <div class="col-md-12">
                            {!! html()->text('app_secret')->class('form-control app-secret'.($errors->has('app_secret') ? ' is-invalid' : ''))->id('app-secret') !!}
                            @error('app_secret')
                            <span class="error-message"> {{$message}}</span>
                            @enderror
                            <span class="appkeycheck"></span>
                        </div>

                    </div>
                </div>
            </div>

            <div class="modal-footer justify-content-between">
                <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i
                            class="fa fa-times"></i>&nbsp;Close
                </button>
                <button type="submit" class="btn btn-primary submit " id="submit"
                        data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i
                            class="fa fa-save">&nbsp;</i>{!!Lang::get('Save')!!}</button>
            </div>
        </div>
    </div>
</div>
{!! html()->form()->close() !!}
