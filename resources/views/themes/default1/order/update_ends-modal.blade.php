  <div class="modal fade" id="updateEndsModal" tabindex="-1" role="dialog" aria-labelledby="defaultModalLabel" aria-hidden="true">
<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
			<h4 class="modal-title" id="defaultModalLabel">Enter Updates Expiry Date</h4>
			<!-- <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button> -->
		</div>
		<div class="modal-body">
      <div id="error2"></div>
			 <div id="response2"></div>
			   <input type="hidden" name="orderId" value="" id="order">
		  <div class="form-group">
                    <!-- name -->
              {!! html()->label(Lang::get('message.update_end'), 'updates')->class('required') !!}
              <div class="input-group date" id="updateEnds" data-target-input="nearest">
                  <input type="text" name="update_ends_at" id="newDate" class="form-control datetimepicker-input" autocomplete="off"  data-target="#updateEnds"/>
                  <div class="input-group-append" data-target="#updateEnds" data-toggle="datetimepicker">
                      <div class="input-group-text"><i class="fa fa-calendar"></i></div>

                  </div>


              </div>



                    
                           <h6 id ="domaincheck"></h6>
                </div>
        
		</div>
		
		  <div class="modal-footer justify-content-between">
                <button type="button" id="close" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;Close</button>
                <button type="submit" id="updatesSave" class="btn btn-primary"><i class="fas fa-save"></i>&nbsp;Save</button>
            </div>
	</div>
</div>
</div>
@section('script')
 <script type="text/javascript">
 	
 
      $('#domaincheck').hide();
function validdomaincheck(){
          alert('ds');
            var pattern = new RegExp(/^((?!-))(xn--)?[a-z0-9][a-z0-9-_]{0,61}[a-z0-9]{0,1}\.(xn--)?([a-z0-9\-]{1,61}|[a-z0-9-]{1,30}\.[a-z]{2,})$/);
              if (pattern.test($('#newDomain').val())){
                 $('#domaincheck').hide();
                 $('#newDomain').css("border-color","");
                 return true;
               
              }
              else{
                 $('#domaincheck').show();
               $('#domaincheck').html("Please enter a valid Domain");
                 $('#domaincheck').focus();
                  $('#newDomain').css("border-color","red");
                 $('#domaincheck').css({"color":"red","margin-top":"5px"});
                   domErr = false;
                    return false;
              
      }

    }


</script>
@stop