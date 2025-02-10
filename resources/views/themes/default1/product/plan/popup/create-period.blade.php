<div class="modal fade" id="period-modal-show">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">{{ __('message.add_periods') }}</h4>

        </div>

      <div class="modal-body">
         <div id="alertMessage"></div>
         <div id="error"></div>
       <input type="text" name="periods" id="new-period" class="form-control"  placeholder="{{ __('message.enter_period') }}"> <br>
               <select name="select-period"  id="select-period" class="form-control" onchange="calculateAmount(this.value)">
                <option value="" disabled selected>{{ __('message.choose_your_option') }}</option>
                <option value="years">{{ __('message.years') }}</option>
                <option value="months">{{ __('message.months') }}</option>
               </select><br>

      <!--   <input type="checkbox" id="year" name="checkboxName" value="year">
   <label for="html">Year</label>
        <input type="checkbox" id="month"  name="checkboxName" value="month">
   <label for="html">Month</label> -->
       <input type="text" name="days" id="new-days" class="form-control" placeholder="{{ __('message.enter_days') }}" >
      
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-default pull-left close-popup" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
        <button type="button" id="submit1" class="btn btn-primary save-periods"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('Save')!!}</button>
      </div>
    </div>
    <!-- /.modal-content -->
  </div>
  <!-- /.modal-dialog -->
</div>
<script>

            $(".close-popup").click(function() {
               location.reload();
            });

            function calculateAmount(val) {

                
                var number = $("#new-period").val();
                if(val == 'years')
                {

                 var tot_price = number * 365;
                /*display the result*/
                var divobj = document.getElementById('new-days');
                divobj.value = tot_price; 
                }
                if(val == 'months')
                {
                var tot_price = number * 30;
                /*display the result*/
                var divobj = document.getElementById('new-days');
                divobj.value = tot_price;  
            }
 
            }
 </script>