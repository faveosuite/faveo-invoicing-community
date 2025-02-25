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




            <table class="table table-condensed">
                    <tr>

                        <td>

                                {!! Form::label('rzp_key','Razorpay key',['class'=>'required']) !!}
                            <div class="form-group col-lg-5 pl-0">
                            {!! Form::text('rzp_key',$rzpKeys->rzp_key,['class' => 'form-control rzp_key','id'=>'rzp_key']) !!}
                                   <span id="rzp_keycheck"></span>
                            </div>

                         </td>
                        </tr>



                <tr>
                        <td>
                          <!-- last name -->
                            {!! Form::label('rzp_secret','Razorpay Secret', ['class'=>'required']) !!}
                            <div class="form-group col-lg-5 pl-0">
                            <div class="input-group">
{{--                        {!! Form::text('rzp_secret',$rzpKeys->rzp_secret,['class' => 'form-control rzp_secret','id'=>'rzp_secret']) !!}--}}
{{--                            {!! Form::password('rzp_secret',['class' => 'form-control rzp_secret','id'=>'rzp_secret']) !!}--}}
                             <input type="password" name="rzp_secret" value="{{$rzpKeys->rzp_secret}}"  class="form-control rzp_secret" id="rzp_secret">


                                <div class="input-group-append">
                                        <span class="input-group-text" role="button" onclick="togglePasswordVisibility(this)">
                                            <i class="fa fa-eye-slash"></i>
                                        </span>
                            </div>
                            </div>
                           <span id="rzp_secretcheck"></span>
                            </div>
                         </td>

                </tr>

                <tr>
                          <td>
                          <!-- last name -->
                        {!! Form::label('apilayer_key','ApiLayer Access Key(For Exchange Rate Conversion)', ['class'=>'required']) !!}
                              <div class="form-group col-lg-5 pl-0">
                                {!! Form::text('apilayer_key',$rzpKeys->apilayer_key,['class' => 'form-control apilayer_key','id'=>'apilayer_key']) !!}

                           <span id="apilayer_check"></span>
                              </div>
                         </td>
                       
                   
                       
                    </tr>
            </table>

               </div>
               <br>
                <button type="submit" class="btn btn-primary btn-sm" id="key_update"><i class="fa fa-sync-alt"></i>&nbsp;Update</button>

             </div>



        </div>


        <!-- /.box -->

    </div>

  <script>

      $(document).ready(function() {

          const userRequiredFields = {
              rzp_key:@json(trans('message.razorpay_details.rzp_key')),
              rzp_secret:@json(trans('message.razorpay_details.rzp_secret')),
              apilayer_key:@json(trans('message.razorpay_details.apilayer_key')),


          };

          $('#key_update').on('click', function (e) {
              const userFields = {
                  rzp_key:$('#rzp_key'),
                  rzp_secret:$('#rzp_secret'),
                  apilayer_key:$('#apilayer_key'),
              };


              // Clear previous errors
              Object.values(userFields).forEach(field => {
                  field.removeClass('is-invalid');
                  field.next().next('.error').remove();

              });

              let isValid = true;

              const showError = (field, message) => {
                  field.addClass('is-invalid');
                  field.next().after(`<span class='error invalid-feedback'>${message}</span>`);
              };

              // Validate required fields
              Object.keys(userFields).forEach(field => {
                  if (!userFields[field].val()) {
                      showError(userFields[field], userRequiredFields[field]);
                      isValid = false;
                  }
              });


              // If validation fails, prevent form submission
              if (!isValid) {
                  console.log(3);
                  e.preventDefault();
              }else{
                  var rzpstatus = 1;
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
              }
          });

          // Function to remove error when input'id' => 'changePasswordForm'ng data
          const removeErrorMessage = (field) => {
              field.classList.remove('is-invalid');
              const error = field.nextElementSibling;
              if (error && error.classList.contains('error')) {
                  error.remove();
              }
          };

          // Add input event listeners for all fields
          ['rzp_key','rzp_secret','apilayer_key'].forEach(id => {

              document.getElementById(id).addEventListener('input', function () {
                  removeErrorMessage(this);

              });
          });
      });

  </script>
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