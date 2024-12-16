@extends('themes.default1.layouts.master')
@section('title')
Payment
@stop
@section('content-header')
   <div class="col-sm-6">
       <h1> Create New Payment</h1>
   </div>
   <div class="col-sm-6">
       <ol class="breadcrumb float-sm-right">
           <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
           <li class="breadcrumb-item"><a href="{{url('clients')}}"> All Users</a></li>
           <li class="breadcrumb-item"><a href="{{url('clients/'.$clientid)}}">View User</a></li>
           <li class="breadcrumb-item active">New Payment</li>
       </ol>
   </div><!-- /.col -->

@stop

   @section('content')
   <div class="card card-secondary card-outline">
     <div class="card-header">

          <div id="alertMessage"></div>
          <div id="error1"></div>
           <h5>New Payment</h5>

       </div>

    <div class="card-body">

           <div class="row">

               <div class="col-md-12">

                  

                   <div class="row">

                       <div class="col-md-4 form-group {{ $errors->has('invoice_status') ? 'has-error' : '' }}">
                           <!-- first name -->
                           {!! html()->label(Lang::get('message.date-of-payment'), 'payment_date')->class('required') !!}
                           <div class="input-group date" id="payment" data-target-input="nearest">
                                <input type="text" id="payment_date" name="payment_date" class="form-control datetimepicker-input" autocomplete="off"  data-target="#payment"/>
                               <div class="input-group-append" data-target="#payment" data-toggle="datetimepicker">
                                   <div class="input-group-text"><i class="fa fa-calendar"></i></div>

                               </div>
                            

                           </div>

                       </div>

                       <div class="col-md-4 form-group {{ $errors->has('payment_method') ? 'has-error' : '' }}">
                           <!-- last name -->
                           {!! html()->label(Lang::get('message.payment-method'), 'payment_method')->class('required') !!}
                           {!! html()->select('payment_method', [
                               '' => 'Choose',
                               'cash' => 'Cash',
                               'check' => 'Check',
                               'online payment' => 'Online Payment',
                               'razorpay' => 'Razorpay',
                               'stripe' => 'Stripe',
                               'Credit Balance' => 'Credit Balance'
                           ])->class('form-control')->id('payment_method') !!}

                       </div>

                      
                       <div class="col-md-4 form-group {{ $errors->has('amount') ? 'has-error' : '' }}">
                           <!-- first name -->
                           {!! html()->label(Lang::get('message.amount'), 'amount')->class('required') !!}
                           {!! html()->text('amount', null)->class('form-control')->id('amount') !!}
                           <input type="hidden" name="hidden" id="amount1">
                       </div>

                      


                   </div>
                    <button type="submit" class="btn btn-primary pull-right" onclick="multiplePayment()" id="submit"><i class="fas fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button>

               </div>

           </div>

       </div>



   </div>
   <div class= "card card-primary">
      
                       <div class="card-body">
                       <div class="row">
                           @if($invoices)
                           <div class="col-md-12">
                               <table id="payment-table" class="table table-bordered table-hover">
                                   <thead>
                                       <tr>
                                            <th></th>
                                           <th>{{Lang::get('message.date')}}</th>
                                           <th>{{Lang::get('message.invoice_number')}}</th>
                                           <th>{{Lang::get('message.total')}}</th>
                                           <th>Due</th>
                                           <th>{{Lang::get('message.pay')}}</th>
                                          
                                         
                                       </tr>
                                   </thead>
                                   <tbody>
                                       @forelse ($invoices as $invoice)
                                       <?php
                                    
                                       $payment = \App\Model\Order\Payment::where('invoice_id',$invoice->id)->select('amount')->get();
                                        $c=count($payment);
                                          $sum= 0;
                                          for($i=0 ;  $i <= $c-1 ; $i++)
                                          {
                                            $sum = $sum + $payment[$i]->amount;
                                          }
                                          $pendingAmount = ($invoice->grand_total)-($sum);
                                       ?>
                                       @if ($invoice->status != 'Success')
                                       <tr>

                                            <td class="selectedbox1">
                                                <input type="checkbox"  id="check" class="selectedbox" name='selectedcheckbox' value="{{$invoice->id}}">
                                           </td>
                                           <td>
                                                {!! getDateHtml($invoice->date) !!}
                                           </td>
                                           <td class="invoice-number">
                                               <a href="{{url('invoices/show?invoiceid='.$invoice->id)}}">{{$invoice->number}}</a>
                                           </td>
                                           <td class="invoice-total">
                                              {{$invoice->grand_total}}
                                           </td>
                                           <td id="pendingamt">
                                                 <input type="text" class="pendingamt" name="pending" value ="{{$pendingAmount}}" id="pending_{{$invoice->id}}" disabled="disabled">
                                             
                                           </td>
                                           <td class="changeamt">
                                               <input type="text" class="changeamt" name="amount" id="{{$invoice->id}}" disabled="disabled">
                                              
                                           </td>
                                         

                                       </tr>
                                       @endif
                                       @empty
                                       <tr>
                                           <td>No Invoices</td>
                                       </tr>
                                       @endforelse



                                   </tbody>
                               </table>
                           </div>
                           @endif
                       </div>
                         <h3>Amount To Credit : {{$symbol}} <span class="creditAmount">0</span></h3>
                   </div>
   </div>
      <script>
      $('ul.nav-sidebar a').filter(function() {
       return this.id == 'all_user';
   }).addClass('active');

   // for treeview
   $('ul.nav-treeview a').filter(function() {
       return this.id == 'all_user';
   }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
   </script>
   @stop

   @section('datepicker')
   <script type="text/javascript">
   $(function () {
       $('#payment').datetimepicker({
           format: 'L'
       });
   });
   </script>
   <script>
        function checking(e){

           $('#payment-table').find("td input[type='checkbox']").prop('checked', $(e).prop('checked'));
        }

        $(document).ready(function () {
   // Handle checkbox click event
   $(".selectedbox").click(function () {
       updateSelectedAmounts();
   });

   // Handle amount input change event
   $("#amount").change(function () {
       updateSelectedAmounts();
   });

   // Update selected amounts based on checkboxes and amount input
   function updateSelectedAmounts() {
       var selectedAmount = parseInt($("#amount").val() || 0);
       var remainingAmount = selectedAmount;

       $(".selectedbox:checked").each(function () {
           var pendingAmount = parseInt($("#pending_" + $(this).val()).val() || 0);
           var currentAmount = Math.min(remainingAmount, pendingAmount);
           $("#" + $(this).val()).val(currentAmount);
           remainingAmount -= currentAmount;
       });
       $(".selectedbox").change(function() {
   if ($(this).is(":checked")) {
       // Checkbox is checked, do nothing
   } else {
       var checkboxValue = $(this).val();
       $('#' + checkboxValue).val('');
   }
});


       $(".changeamt").each(function () {
           if (!$(this).is(":disabled")) {
               var invoiceId = $(this).attr("id");
               if (!$("#check").is(":checked") || !$("#check[value='" + invoiceId + "']").is(":checked")) {
                   $(this).val(0);
               }
           }
       });

       $(".creditAmount").html(selectedAmount - remainingAmount);
       $("#amount1").val(remainingAmount);
   }
});

  
   function multiplePayment(){
    $("#submit").html("<i class='fas fa-circle-notch fa-spin'></i>  Please Wait...");
   var invoice = [];
   var invoiceAmount = [];
   $(":checked").each(function() {
     if($(this).val() != ""){
      var value = $('#'+ $(this).val()).val();
       invoice.push($(this).val());
       invoiceAmount.push(value);

     }

   });


   var data = {
           "totalAmt":   $('#amount').val(),
           "invoiceChecked"  : invoice,
           "invoiceAmount"  : invoiceAmount,
           "amtToCredit"   :   $('.creditAmount').html(),
           "payment_date"  : $('#payment_date').val(),
           "payment_method" :$('#payment_method').val(),
       };
   $.ajax({
     url: '{{url('newMultiplePayment/receive/'.$clientid)}}',
     type: 'POST',
     data: data,
         success: function (response) {
           $('#alertMessage').show();
           var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+response.message+'.</div>';
           $('#alertMessage').html(result+ ".");
           $("#submit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
         },
         error: function (ex) {
           var errors = ex.responseJSON;
            $("#submit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
              $('#error1').show();
           var html = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-ban"></i>Whoops! </strong>Something went wrong <br><ul>';
          
           for (var key in ex.responseJSON.errors)
           {
               html += '<li>' + ex.responseJSON.errors[key][0] + '</li>'
           }
           html += '</ul></div>';
            $('#alertMessage').hide();
            // $('#alertMessage2').hide();
           $('#error1').show();
            document.getElementById('error1').innerHTML = html;

         
         }
   });
 }
</script>

   @stop
