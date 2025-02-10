@extends('themes.default1.layouts.master')
@section('title')
Edit Payment
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>  {{Lang::get('message.link-extra')}}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('clients')}}"> {{ __('message.all-users') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('clients/'.$clientid)}}">{{ __('message.view_user') }}</a></li>
            <li class="breadcrumb-item active">Edit Payment</li>
        </ol>
    </div><!-- /.col -->

@stop

    @section('content')
    <div class="card card-primary card-outline">
       <div class="card-header">
          <div id="alertMessage"></div>
                    <div id="error1"></div>
           
          
            <h5>{{Lang::get('message.new-payment')}} </h5>

        </div>

      <div class="card-body">

            <div class="row">

                <div class="col-md-12">

                    

                    <div class="row">

                        <div class="col-md-4 form-group {{ $errors->has('invoice_status') ? 'has-error' : '' }}">
                            <!-- first name -->
                            {!! Form::label('payment_date',Lang::get('message.date-of-payment'),['class'=>'required']) !!}
                              <div class="input-group date" id="payment" data-target-input="nearest">
                                 <input type="text" id="payment_date" name="payment_date" class="form-control datetimepicker-input" autocomplete="off"  data-target="#payment"/>
                                <div class="input-group-append" data-target="#payment" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>

                                </div>
                              

                            </div>
                      

                        </div>

                        <div class="col-md-4 form-group {{ $errors->has('payment_method') ? 'has-error' : '' }}">
                            <!-- last name -->
                            {!! Form::label('payment_method',Lang::get('message.payment-method'),['class'=>'required']) !!}
                            {!! Form::select('payment_method',[''=>'Choose','cash'=>'Cash','check'=>'Check','online payment'=>'Online Payment','razorpay'=>'Razorpay'],null,['class' => 'form-control','id'=>'payment_method']) !!}

                        </div>

                        
                        <div class="col-md-4 form-group {{ $errors->has('amount') ? 'has-error' : '' }}">
                            <!-- first name -->
                            {!! Form::label('amount',Lang::get('message.extra-amount'),['class'=>'required']) !!}
                            {!! Form::text('amount',$amountReceived,['class' => 'form-control','id'=>'amount','disabled'=>'disabled']) !!}
                            <input type="hidden" value="{{$amountReceived}}" name="hidden" id="amount1">
                        </div>

                        


                    </div>
                    <button type="submit" class="form-group btn btn-primary pull-right" onclick="multiplePayment()" id="submit"><i class="fa fa-save">&nbsp;&nbsp;</i>{!!Lang::get('message.save')!!}</button>

                </div>

            </div>

        </div>



    </div>
  </div>
    <div class= "card card-primary">
       
                        <div class="card-body">
                            @if(count($invoices)!=0)
                          <h4>{{Lang::get('message.link')}}</h4>
                        <div class="row">
                          
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
                                         $date1 = new DateTime($invoice->date);
                                         $tz = \Auth::user()->timezone()->first()->name;
                                         $date1->setTimezone(new DateTimeZone($tz));
                                        $date = $date1->format('M j, Y, g:i a ');
                                        $payment = \App\Model\Order\Payment::where('invoice_id',$invoice->id)->select('amount')->get();
                                         $c=count($payment);
                                           $sum= 0;
                                           for($i=0 ;  $i <= $c-1 ; $i++)
                                           {
                                             $sum = $sum + $payment[$i]->amount;
                                           }
                                           $pendingAmount = ($invoice->grand_total)-($sum);
                                        ?>
                                        @if ($invoice->status == 'Pending')
                                        <tr>
                                             <td class="selectedbox1">
                                                 <input type="checkbox"  id="check" class="selectedbox" name='selectedcheckbox' value="{{$invoice->id}}">
                                            </td>
                                            <td>
                                                  {{$date}}
                                            </td>
                                            <td class="invoice-number">
                                                <a href="{{url('invoices/show?invoiceid='.$invoice->id)}}">{{$invoice->number}}</a>
                                            </td>
                                            <td class="invoice-total"> 
                                               {{currencyFormat($invoice->grand_total,$code = $currency)}}
                                            </td>
                                            <td id="pendingamt">
                                                  <input type="text" class="pendingamt" name="pending" value ="{{currencyFormat($pendingAmount,$code = $currency)}}" id="pending_{{$invoice->id}}" disabled="disabled">
                                               
                                            </td>
                                            <td class="changeamt">
                                                <input type="text" class="changeamt" name="amount" id="{{$invoice->id}}">
                                                
                                            </td>
                                           

                                        </tr>
                                        @endif
                                        @empty 
                                        <tr>
                                            <td>{{ __('message.no_invoices') }}</td>
                                        </tr>
                                        @endforelse



                                    </tbody>
                                </table>
                            </div>
                            
                        </div>
                        @endif
                          <h3>{{ __('message.amount_to_credit') }} {{$symbol}} <span class="creditAmount">0</span></h3>
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
        /* Get the checkboxes values based on the class attached to each check box */
        $(".selectedbox").click(function() {
            getValueUsingClass();
        });
         });

         function getValueUsingClass(){
        /* declare an checkbox array */
        var chkArray = [];
        
        /* look for all checkboes that have a class 'chk' attached to it and check if it was checked */
        $(".selectedbox:checked").each(function() {
            chkArray.push($(this).val());
        });      
        amountChange();
      var amt = $("#amount1").val();
      function amountChange()
        {
        $('.selectedbox').change(function () {


      if(!this.checked ){
        var revertValue =  $('#'+ $(this).val()).val();
        $('.creditAmount').html((+revertValue + + $('.creditAmount').html()));
        $("#amount1").val((+revertValue + + $('.creditAmount').html()))
        $('#'+ $(this).val()).val('');
        }
        else{

          if(chkArray[0] > 0 &&  +amt != 0 ){
             for (var i in chkArray) {

                 var pending= $('#pending_'+ chkArray[i]).val();
                      
                       if(+amt > +pending){
                        $('#' + chkArray[i]).val(pending);
                        $('.creditAmount').html((amt - pending));
                        $("#amount1").val(amt - pending);
                       }
                     
                       else{
                        // console.log(chkArray[chkArray.length - 1]);
                        $('#' + chkArray[chkArray.length - 1]).val(amt);
                        $('.creditAmount').html((0));
                        $("#amount1").val(0);
                       }
                
                  }
             }
        }

        });
          }
        }
         
         $('#amount').change(function () {
        $("#amount1").val($("#amount").val());
          var chkArray = [];
        
        $(".selectedbox:checked").each(function() {
            chkArray.push($(this).val());
        });   
        for (var i in chkArray) {
             $('#'+chkArray[i]).val('');
            }
       $(".selectedbox").prop("checked", false);
       $('.creditAmount').html($("#amount1").val());
    }); 
    
    function multiplePayment(){
     $("#submit").html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait') }}");
    var invoice = [];
    var invoiceAmount = [];
    $(":checked").each(function() {
      if($(this).val() != ""){
       var value = $('#'+ $(this).val()).val();
        invoice.push($(this).val());
        invoiceAmount.push(value);

      }
  
    });


// console.log(invoice);
    var data = {
            "totalAmt":   $('#amount').val(),
            "invoiceChecked"  : invoice,
            "invoiceAmount"  : invoiceAmount,
            "amtToCredit"   :   $('.creditAmount').html(),
            "payment_date"  : $('#payment_date').val(),
            "payment_method" :$('#payment_method').val(),
        };
    $.ajax({
      url: '{{url('newMultiplePayment/update/'.$clientid)}}',
      type: 'POST',
      data: data,
          success: function (response) {
            $('#alertMessage').show();
            // console.log(response)
            var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>'+response.message+'.</div>';
            $('#alertMessage').html(result+ ".");
              $("#submit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
          },
          error: function (ex) {
               var errors = ex.responseJSON;
               $('#error1').show();
            var html = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-ban"></i>{{ __('message.alert') }}! </strong>'+ex.responseJSON.message+' <br><ul>';
             $("#submit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>Save");
            for (var key in ex.responseJSON.errors)
            {
                html += '<li>' + ex.responseJSON.errors[key][0] + '</li>'
            }
            html += '</ul></div>';
             $('#alertMessage').hide(); 
             $('#error1').show();
             document.getElementById('error1').innerHTML = html;
            

           
          }
    }) 
  }
 </script>

    @stop