<div class="modal fade" id="create-upload-option">
    <div class="modal-dialog">
        <div class="modal-content" style="width:max-content;">
            <div class="modal-header">
                <h4 class="modal-title">Add Product Details</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <!-- Form  -->
                <!--   {!! Form::open(['url'=>'upload/save','files' => true,'id'=>'addFiles']) !!} -->
                <div id="error"></div>
                <div id="alertMessage1"></div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label> Product Name </label>

                        <input type="text" id="productname" name="product" class="form-control" value="{{$product->name}}" readonly>
                    </div>

                    <div class="form-group col-md-6 {{ $errors->has('title') ? 'has-error' : '' }}">

                        {!! Form::label('Title',Lang::get('Title'),['class'=>'required']) !!}
                        <input type="text" id="producttitle" class="form-control" name="title">
                        <h6 id= "titlecheck"></h6>

                    </div>
                </div>
                <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                    <!-- last name -->
                    {!! Form::label('description',Lang::get('message.description')) !!}
                    <textarea class="form-control" id= "textarea3" name="description"></textarea>
                </div>

                <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                    <i class='fa fa-info-circle' style='cursor: help; font-size: small; color: rgb(60, 141, 188);'><label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title="Enter JSON format.">
                    </label></i>
                    {!! Form::label('dependencies',Lang::get('message.dependencies'),['class'=>'required']) !!}

                    {!! Form::textarea('dependencies',null,['class' => 'form-control','id'=>'dependencies','rows'=>'5']) !!}
                    <h6 id= "descheck"></h6>
                </div>

                <div class="row">
                    <div class="form-group col-md-6{{ $errors->has('version') ? 'has-error' : '' }}">
                        <!-- name -->
                        {!! Form::label('Version',Lang::get('Version'),['class'=>'required']) !!}
                        <input type="text" class="form-control" id="productver" name="version">
                        <h6 id= "vercheck"></h6>

                    </div>



                    <div class="form-group col-md-6{{ $errors->has('version') ? 'has-error' : '' }}">
                        <!-- name -->
                        {!! Form::label('File',Lang::get('File'),['class'=>'required']) !!}
                        <div id="resumable-drop" style="display: none">
                            <p><button id="resumable-browse" data-url="{{ url('chunkupload') }}" >Upload</button> or drop here
                            </p>
                        </div>
                        <ul id="file-upload-list" class="list-unstyled"  style="display: none">

                        </ul>

                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-4{{ $errors->has('is_private') ? 'has-error' : '' }}">
                        <i class='fa fa-info-circle' style='cursor: help; font-size: small; color: rgb(60, 141, 188);' ><label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title="If the release is kept private, product users won't receive notification for this release.">
                        </label></i>
                        <!-- name -->
                        {!! Form::label('p_release','Private Release') !!}&nbsp;
                        <input type="checkbox" value="0" name= "is_private" id="p_release" onclick="privateRelease()">

                    </div>
                    <div class="form-group col-md-4{{ $errors->has('release_type') ? 'has-error' : '' }}">
                        <i class='fa fa-info-circle' style='cursor: help; font-size: small; color: rgb(60, 141, 188);' ><label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title="If the release is kept private, product users won't receive notification for this release.">
                        </label></i>
                        <!-- name -->
                        {!! Form::label('release_type','Releases') !!}&nbsp;
                        <select name="release_type" id="release_type">
                            <option value="official" selected>Official</option>
                            <option value="pre_release">Pre Release</option>
                            <option value="beta">Beta</option>
                        </select>

                    </div>



                    <div class="form-group col-md-4{{ $errors->has('version') ? 'has-error' : '' }}">
                        <i class='fa fa-info-circle' style='cursor: help; font-size: small; color: rgb(60, 141, 188);' ><label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title="If update is kept restricted for this release, product users need to update their versions upto this release first before updating to further releases.">
                        </label></i>
                        {!! Form::label('restrict','Restrict update') !!}&nbsp;
                        <input type="checkbox" value="0" name= "is_restricted" id="r_release" onclick="resrictedRelease()">
                    </div>


                </div>
            </div>
            <input type="hidden" name="file_ids" id="file_ids" value="">
            <div class="modal-footer justify-content-between">
                <button type="button" id="close" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;Close</button>
                <button type="submit" class="btn btn-primary" id="uploadVersion"><i class="fa fa-save"></i>&nbsp;{!!Lang::get('Save')!!}</button>
            </div>


            <!-- {!! Form::close()  !!} -->
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
                // console.log(data)


                $("#states").html(data);

            }
        });
    }
</script>

{!! Form::close()  !!}