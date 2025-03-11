@extends('themes.default1.layouts.master')
@section('title')
Razorpay
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>Razorpay</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i class="fa fa-dashboard"></i> Settings</a></li>
            <li class="breadcrumb-item"><a href="{{url('plugin')}}"><i class="fa fa-dashboard"></i> Payment Gateways</a></li>
            <li class="breadcrumb-item active">Razorpay</li>
        </ol>
    </div><!-- /.col -->
@stop

@section('content')

  <div id="alertMessage"></div>
                <div id="errorMessage"></div>
    <div class="col-md-12">
        <div class="card card-secondary card-outline">


          <div class="card-header">
        <h3 class="card-title">Api Keys</h3>

        
    </div>
                
                <div class="card-body table-responsive">
        <div class="row">

               


                    
                   
                    
                    <tr>

                        <td>
                          <!-- last name -->
                            {!! html()->label('Razorpay key', 'rzp_key')->class('required') !!}
                            {!! html()->text('rzp_key', $rzpKeys->rzp_key)->class('form-control rzp_key')->id('rzp_key') !!}
                            <span id="rzp_keycheck"></span>
                         </td>

                         
                         <br><br>
                        <td>
                          <!-- last name -->
                            {!! html()->label('Razorpay Secret', 'rzp_secret')->class('required') !!}
                            {!! html()->text('rzp_secret', $rzpKeys->rzp_secret)->class('form-control rzp_secret')->id('rzp_secret') !!}
                            <span id="rzp_secretcheck"></span>
                         </td>
                          <br><br>
                          <td>
                          <!-- last name -->
                              {!! html()->label('ApiLayer Access Key (For Exchange Rate Conversion)', 'apilayer_key')->class('required') !!}

                              {!! html()->text('apilayer_key', $rzpKeys->apilayer_key)->class('form-control apilayer_key')->id('apilayer_key') !!}
                              <span id="apilayer_check"></span>
                         </td>
                       
                   
                       
                    </tr>

                
               </div>
               <br>
                <button type="submit" class="btn btn-primary btn-sm" id="key_update"><i class="fa fa-sync-alt"></i>&nbsp;Update</button>
             </div>



        </div>
        

        <!-- /.box -->

    </div>


<script type="text/javascript">
           
             $('#b_currency_update').on('click',function(){
                 $('#b_currency_update').html("<i class='fa fa-circle-o-notch fa-spin fa-1x fa-fw'></i>Please Wait..."); 
                 $('#b_currency_update').attr('disabled',true); 
                $.ajax ({
              url: '{{url("change-base-currency/payment-gateway/stripe")}}',
              type : 'post',
              data: {
               "b_currency": $('#b_currency').val(),
                },
               success: function (data) {
                    $('#alertMessage').show();
                     $("#b_currency_update").attr('disabled',false);  
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+data.update+'.</div>';
                    $('#alertMessage').html(result+ ".");
                    $("#b_currency_update").html("<i class='fa fa-floppy-o'>&nbsp;&nbsp;</i>Save");
                      setInterval(function(){ 
                        $('#alertMessage').slideUp(3000); 
                    }, 1000);
                  },
                })

             })
             



               //Validate and pass value through ajax
        $("#key_update").on('click',function (){ //When Submit button is checked
        $('#key_update').html("<i class='fas fa-circle-notch fa-spin'></i>  Please Wait...");
        $("#key_update").attr('disabled',true);  
             var rzpstatus = 1;
           if ($('#rzp_key').val() == "") { //if value is not entered
            $('#rzp_keycheck').show();
            $('#rzp_keycheck').html("Please Enter Razorpay Key");
            $('#rzp_key').css("border-color","red");
            $('#rzp_keycheck').css({"color":"red","margin-top":"5px"});
            return false;
          } else if ($('#rzp_secret').val() == "") {
            $("#key_update").attr('disabled',false);  
             $('#rzp_secretcheck').show();
            $('#rzp_secretcheck').html("Please Enter Stripe Secret");
            $('#rzp_secret').css("border-color","red");
            $('#rzp_secretcheck').css({"color":"red","margin-top":"5px"});
            return false;
          } else if($('#apilayer_key').val() == "") {
            $("#key_update").attr('disabled',false);  
             $('#apilayer_check').show();
            $('#apilayer_check').html("Please Enter Api Layer Access key");
            $('#apilayer_key').css("border-color","red");
            $('#apilayer_check').css({"color":"red","margin-top":"5px"});
            return false;
          }
    
     
    $.ajax ({
      url: '{{url("update-api-key/payment-gateway/razorpay")}}',
      type : 'get',
      data: {
       "status": rzpstatus,
       "rzp_key": $('#rzp_key').val(),"rzp_secret" : $('#rzp_secret').val(), "apilayer_key" : $('#apilayer_key').val() },
       success: function (data) {
        $("#key_update").attr('disabled',false); 
        $('#rzp_keycheck').hide();
         $('#rzp_secret').css("border-color","");
           $('#key_update').html("<i class='fas fa-circle-notch fa-spin'></i>  Update");
             
            $('#alertMessage').show();
            var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> Success! </strong>'+data.message.message+'.</div>';
            $('#alertMessage').html(result+ ".");
            $("#key_update").html("<i class='fa fa-sync-alt'>&nbsp;</i>Update");
              setInterval(function(){ 
                $('#alertMessage').slideUp(3000); 
            }, 1000);
          }, error: function(data) {
            $("#key_update").attr('disabled',false);  
            $('#key_update').html("<i class='fas fa-circle-notch fa-spin'></i>  Update");
                $('#errorMessage').show();
                var result =  '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-ban"></i> Failed! </strong>'+data.responseJSON.message+'.</div>';
                $('#errorMessage').html(data.responseJSON.message);
                $('#errorMessage').html(result+ ".");
                $("#key_update").html("<i class='fa fa-sync-alt'>&nbsp;</i>Update");
                setInterval(function(){ 
                $('#errorMessage').slideUp(3000); 
            }, 1000);
          }
    })
  });
             
        
</script>
<script>
     $('ul.nav-sidebar a').filter(function() {
        return this.id == 'setting';
    }).addClass('active');

    // for treeview
    $('ul.nav-treeview a').filter(function() {
        return this.id == 'setting';
    }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');
</script>
@stop