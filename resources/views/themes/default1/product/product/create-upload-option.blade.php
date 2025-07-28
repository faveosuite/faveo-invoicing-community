<div class="modal fade" id="create-upload-option">
    <div class="modal-dialog">
        <div class="modal-content" style="width:max-content;">
            <div class="modal-header">
                <h4 class="modal-title">{{ __('message.all_product_details') }}</h4>
                <button type="button" class="close closebutton" data-dismiss="modal" aria-label="{{ __('message.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <!-- Form  -->
                <!--   {!! html()->form('POST', url('upload/save'))->id('addFiles')->acceptsFiles() !!} -->
                <div id="error"></div>
                <div id="alertMessage1"></div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label> {{ __('message.product_name') }} </label>

                        <input type="text" id="productname" name="product" class="form-control" value="{{$product->name}}" readonly>
                    </div>

                    <div class="form-group col-md-6 {{ $errors->has('title') ? 'has-error' : '' }}">

                        {!! html()->label(__('message.title'))->for('title')->class('required') !!}
                        <input type="text" id="producttitle" class="form-control" name="title">
                        <h6 id= "titlecheck"></h6>

                    </div>
                </div>
                <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                    <!-- last name -->
                    {!! html()->label(__('message.description'))->for('description') !!}
                    <textarea class="form-control" id= "textarea3" name="description"></textarea>
                </div>

                <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                    <i class='fa fa-info-circle' style='cursor: help; font-size: small; color: rgb(60, 141, 188);'><label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title="{{ __('message.enter_json_format') }}">
                    </label></i>
                    {!! html()->label(__('message.dependencies'))->for('dependencies')->class('required') !!}
                    {!! html()->textarea('dependencies')->class('form-control')->id('dependencies')->rows(5) !!}
                    <h6 id= "descheck"></h6>
                </div>

                <div class="row">
                    <div class="form-group col-md-6{{ $errors->has('version') ? 'has-error' : '' }}">
                        <!-- name -->
                        {!! html()->label( __('message.version'))->for('Version')->class('required') !!}
                        <input type="text" class="form-control" id="productver" name="version">
                        <h6 id= "vercheck"></h6>
                    </div>

                    <div class="form-group col-md-6{{ $errors->has('file') ? 'has-error' : '' }}">
                        <label for="file" class="required">File Upload</label>
                        <div class="custom-file">
                            <input type="file" id="file-upload" class="custom-file-input cursor-pointer"
                                   data-url="{{ url('chunkupload') }}"
                                   role="button" accept=".zip,.rar,.tar,.gz,.7z">
                            <label class="custom-file-label" for="file-upload" id="file-label">Choose file</label>
                        </div>

                        <!-- Progress Bar -->
                        <div class="progress mt-3 d-none" id="upload-progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                 style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="progress-bar">
                                <span id="progress-text">0%</span>
                            </div>
                        </div>

                        <!-- File info - PROPERLY HIDDEN BY DEFAULT -->
                        <div id="file-info" class="d-none align-items-center p-2 mt-2 border rounded bg-light w-100">
                            <!-- File icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                 class="bi bi-file-earmark-text mr-2 text-secondary" viewBox="0 0 16 16">
                                <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
                                <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
                            </svg>

                            <!-- File name and size -->
                            <div class="flex-grow-1 text-truncate">
                                <div id="file-name" class="font-weight-bold text-dark text-truncate"></div>
                                <div id="file-size" class="text-muted small"></div>
                            </div>

                            <!-- Remove button -->
                            <button type="button" id="remove-file" class="close ml-2" aria-label="Remove file">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <!-- Hidden input for file IDs -->
                        <input type="hidden" id="file_ids" name="file_ids" value="">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-4{{ $errors->has('is_private') ? 'has-error' : '' }}">
                        <i class='fa fa-info-circle' style='cursor: help; font-size: small; color: rgb(60, 141, 188);' ><label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title="{{ __('message.release_private') }}">
                        </label></i>
                        <!-- name -->
                        {!! html()->label( __('message.private_release'))->for('p_release') !!}
                        <input type="checkbox" value="0" name= "is_private" id="p_release" onclick="privateRelease()">

                    </div>
                    <div class="form-group col-md-4{{ $errors->has('release_type') ? 'has-error' : '' }}">
                        <i class='fa fa-info-circle' style='cursor: help; font-size: small; color: rgb(60, 141, 188);' ><label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title="{{ __('message.release_private') }}">
                        </label></i>
                        <!-- name -->
                        {!! html()->label( __('message.releases'))->for('release_type') !!}
                        <select name="release_type" id="release_type">
                            <option value="official" selected>{{ __('message.official') }}</option>
                            <option value="pre_release">{{ __('message.pre_release') }}</option>
                            <option value="beta">{{ __('message.beta') }}</option>
                        </select>

                    </div>



                    <div class="form-group col-md-4{{ $errors->has('version') ? 'has-error' : '' }}">
                        <i class='fa fa-info-circle' style='cursor: help; font-size: small; color: rgb(60, 141, 188);' ><label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title="{{ __('message.update_restricted') }}">
                        </label></i>
                        {!! html()->label( __('message.restrict_update'))->for('restrict') !!}
                        <input type="checkbox" value="0" name= "is_restricted" id="r_release" onclick="resrictedRelease()">
                    </div>
                </div>
            </div>
            <input type="hidden" name="file_ids" id="file_ids" value="">
            <div class="modal-footer justify-content-between">
                <button type="button" id="close" class="btn btn-default closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
                <button type="submit" class="btn btn-primary" id="uploadVersion"><i class="fa fa-save"></i>&nbsp;{!!Lang::get('message.save')!!}</button>
            </div>


            <!-- {!! html()->form()->close() !!} -->
            <!-- </form> -->
            <br>



        </div>
        <!-- /Form -->
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script>
    function getStates(val) {
        $.ajax({
            type: "GET",
            url: "{{url('get-state')}}/" + val,
            success: function (data) {
                $("#states").html(data);
            }
        });
    }
</script>

{!! html()->form()->close()  !!}