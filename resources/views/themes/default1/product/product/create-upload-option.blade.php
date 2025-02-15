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
              <!--   {!! html()->form('POST', url('upload/save'))->acceptsFiles() !!} -->
                  <div id="error"></div>
                <div id="alertMessage1"></div>
                <div class="row">
                <div class="form-group col-md-6">
                    <label> Product Name </label>
                 
                      <input type="text" id="productname" name="product" class="form-control" value="{{$product->name}}" readonly>
                 </div>
                
                 <div class="form-group col-md-6 {{ $errors->has('title') ? 'has-error' : '' }}">

                     {!! html()->label(__('message.title'))->for('title')->class('required') !!}
                     <input type="text" id="producttitle" class="form-control" name="title">
                 </div>
               </div>
                <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                    <!-- last name -->
                    {!! html()->label(__('message.description'))->for('description') !!}
                    <textarea class="form-control" id= "textarea3" name="description"></textarea>
               </div>

                 <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                        <i class='fa fa-info-circle' style='cursor: help; font-size: small; color: rgb(60, 141, 188);' <label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title="Enter JSON format.">
                        </label></i>
                     {!! html()->label(__('message.dependencies'))->for('dependencies')->class('required') !!}
                     {!! html()->textarea('dependencies')->class('form-control')->id('dependencies')->rows(5) !!}
                     <h6 id= "descheck"></h6>
                     </div>

               <div class="row">
                <div class="form-group col-md-6{{ $errors->has('version') ? 'has-error' : '' }}">
                    <!-- name -->
                    {!! html()->label(__('Version'))->for('Version')->class('required') !!}
                    <input type="text" class="form-control" id="productver" name="version">
                 </div>

                     
                 
              <div class="form-group col-md-6{{ $errors->has('version') ? 'has-error' : '' }}">
                    <!-- name -->
                  {!! html()->label(__('File'))->for('File')->class('required') !!}
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
                    <i class='fa fa-info-circle' style='cursor: help; font-size: small; color: rgb(60, 141, 188);' <label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title="If the release is kept private, product users won't receive notification for this release.">
                        </label></i>
                    <!-- name -->
                    {!! html()->label('Private Release')->for('p_release') !!}
                    <input type="checkbox" value="0" name= "is_private" id="p_release" onclick="privateRelease()">
                    
                 </div>
                   <div class="form-group col-md-4{{ $errors->has('release_type') ? 'has-error' : '' }}">
                    <i class='fa fa-info-circle' style='cursor: help; font-size: small; color: rgb(60, 141, 188);' <label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title="If the release is kept private, product users won't receive notification for this release.">
                        </label></i>
                    <!-- name -->
                       {!! html()->label('Releases')->for('release_type') !!}
                       <select name="release_type" id="release_type">
                        <option value="official" selected>Official</option>
                        <option value="pre_release">Pre Release</option>
                        <option value="beta">Beta</option>
                    </select>

                 </div>

                     
                 
              <div class="form-group col-md-4{{ $errors->has('version') ? 'has-error' : '' }}">
                    <i class='fa fa-info-circle' style='cursor: help; font-size: small; color: rgb(60, 141, 188);' <label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title="If update is kept restricted for this release, product users need to update their versions upto this release first before updating to further releases.">
                        </label></i>
                  {!! html()->label('Restrict update')->for('restrict') !!}
                  <input type="checkbox" value="0" name= "is_restricted" id="r_release" onclick="resrictedRelease()">
                </div>
               
                
              </div>
            </div>
             <input type="hidden" name="file_ids" id="file_ids" value="">
              <div class="modal-footer justify-content-between">
                <button type="button" id="close" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;Close</button>
                 <button type="submit" class="btn btn-primary" id="uploadVersion"><i class="fa fa-save"></i>&nbsp;{!!Lang::get('Save')!!}</button>
            </div>
           
           <!--  <form id="formsubmitform"> -->
             
                   <!-- {!! html()->form()->close() !!} -->
                  <!-- </form> -->
                 <br>
                 <!--  <div id="files_list"></div>
                    <p id="loading"></p>
                    <input type="hidden" name="file_ids" id="file_ids" value=""> -->
                    

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
<script>

</script>
{!! html()->form()->close()  !!}