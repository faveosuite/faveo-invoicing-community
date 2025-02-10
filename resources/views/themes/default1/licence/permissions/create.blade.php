<div class="modal fade" id="all-permissions">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{Lang::get('message.add-permissions')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

            </div>

            <div class="modal-body">              
                <div id="permissionresponse"></div>
                <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    

                  @foreach($allPermissions as $permission)
                  <input type="checkbox" name="checkme" class="permission_checkbox minimal" value="{{$permission->id}}" id="permissioncheck">&nbsp;
                  {{$permission->permissions}}
                   <br/><br/>
                  @endforeach
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                 <button type="button" id="close" class="btn btn-default pull-left" data-dismiss="modal"><i class="fas fa-times">&nbsp;</i>&nbsp;{{ __('message.close') }}</button>
                <button type="submit" class="btn btn-primary btn-sm" id="permissionssubmit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> {{ __('message.saving') }}"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button>
            </div>
            <script>

            </script>
        </div>
    </div>
</div>
