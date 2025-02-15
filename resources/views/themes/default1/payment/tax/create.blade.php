<div class="modal fade" id="create{{$key}}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Create Tax</h4>
            </div>
            <div class="modal-body">
                <!-- Form  -->
                {!! html()->form('POST', url('tax'))->open() !!}

                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    <!-- name -->
                    {!! html()->hidden('tax_classes_id', $key) !!}
                    {!! html()->label(Lang::get('message.name'), 'name')->class('required') !!}
                    <!-- {!! html()->text('name')->class('form-control') !!} -->
                     <select name="name" class="form-control">
                      <option>OTHERS</option>
                      <option> CGST + SGST</option>
                      <option>IGST</option>
                      <option>UTGST</option>
  
                      </select>
                </div>
                <div class="form-group {{ $errors->has('level') ? 'has-error' : '' }}">
                    <!-- name -->
                    {!! html()->label(Lang::get('message.level'))->for('level')->class('required') !!}
                    {!! html()->text('level')->class('form-control') !!}

                </div>
                <div class="form-group">
                    <!-- name -->
                    {!! html()->label(Lang::get('message.status'))->for('status') !!}

                </div>
                <div class="row">
                    <div class="col-md-3 form-group {{ $errors->has('active') ? 'has-error' : '' }}">
                        <!-- name -->
                        {!! html()->label(Lang::get('message.active'))->for('active') !!}
                        {!! html()->radio('active', 1)->checked() !!}

                    </div>
                    <div class="col-md-3 form-group {{ $errors->has('active') ? 'has-error' : '' }}">
                        <!-- name -->
                        {!! html()->label(Lang::get('message.inactive'))->for('inactive') !!}
                        {!! html()->radio('active', 0) !!}

                    </div>
                </div>
                
                <div class="form-group {{ $errors->has('country') ? 'has-error' : '' }}">
                    <!-- name -->
                    {!! html()->label(Lang::get('message.country'))->for('country') !!}
                    <?php $countries = \App\Model\Common\Country::pluck('country_name', 'country_code_char2')->toArray(); ?>
                    {!! html()->select('country', ['' => 'Select a Country', 'Countries' => $countries])->class('form-control')->attribute('onChange', 'getState(this.value);') !!}

                </div>
                 <div class="form-group {{ $errors->has('state') ? 'has-error' : '' }}">
                    <!-- name -->
                     {!! html()->label(Lang::get('message.state'))->for('state') !!}


                     <select name="state"  class="form-control" id="statess">
                        <option name="state">Please Select Country</option>
                    </select>

                </div>

                <div class="form-group {{ $errors->has('rate') ? 'has-error' : '' }}">
                    <!-- name -->
                    {!! html()->label(Lang::get('message.rate') . ' (%)')->for('rate')->class('required') !!}
                    {!! html()->text('rate')->class('form-control') !!}

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

<script>
    function getState(val) {
      $.ajax({
            type: "GET",
            url: "{{url('get-state')}}/" + val,
            success: function (data) {
              $("#statess").html(data);
                
            }
        });
    }
</script>
{!! html()->form()->close()  !!}