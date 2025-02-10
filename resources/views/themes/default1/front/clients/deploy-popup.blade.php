<a data-id="{{$orderNumber}}" href="#tenancy" class="btn  btn-primary btn-xs open-createtenancyDialog" data-toggle="modal"><i class="fa fa-refresh"></i>&nbsp;{{ __('message.deploy')}}</a>


<style>
    .custom-line {
        border: none;
        border-top: 1px solid #ccc;
        margin: 10px 0;
    }
    #validationMessage {
      position: absolute;
      top: 80px; /* Adjust this value to align the error message properly */
      margin-left:32px;
      left: 0;
      font-size: 12px;
      color: red;
     }
</style>

<script>
    $(document).on("click", ".open-createtenancyDialog", function () {
        var orderId = $(this).data('id');
        $(".modal-body #orderId").val( orderId );
        $('#tenancy').modal('show');
    });

    $(document).ready(function(){
        $('.createtenancy').attr('disabled',true);
        $('#userdomain').keyup(function(){
            if($(this).val().length ==0 || $(this).val().length>28)
                $('.createtenancy').attr('disabled', true);
            else
                $('.createtenancy').attr('disabled',false);
        })
    });


    $('.closebutton').on('click',function(){
        location.reload();
    });

    function createtenancy(){
        $('#createtenancy').attr('disabled',true)
        $("#createtenancy").html("<i class='fas fa-circle-notch fa-spin'></i> " + @json(__('message.please_wait')));
        var domain = $('#userdomain').val();
        var password = $('#password').val();
        var order = $('#orderId').val();
        $.ajax({
            url: "{{url('create/tenant')}}",
            type: "POST",
            data: {'domain': domain, 'password': password, 'orderNo': order},
            success: function (data) {
                $('#createtenancy').attr('disabled',false)
                $("#createtenancy").html("<i class='fa fa-check'>&nbsp;&nbsp;</i>" + @json(__('message.submit')));
                if(data.status == 'validationFailure') {

                    var html = '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>' + @json(__('message.whoops')) + ' </strong>' + @json(__('message.something_wrong')) + '<ul>';
                    for (var key in data.message)
                    {
                        html += '<li>' + data.message[key][0] + '</li>'
                    }
                    html += '</ul></div>';
                    $('#error').show();
                    $('#success').hide();
                    document.getElementById('error').innerHTML = html;
                } else if(data.status == 'false') {
                    $('#error').show();
                    $('#success').hide();
                    var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>' + @json(__('message.whoops')) + ' </strong>' + @json(__('message.something_wrong')) + '!!<br><ul><li>' + data.message + '</li></ul></div>';
                    $('#error').html(result);
                } else if(data.status == 'success_with_warning') {
                    console.log('here');
                    $('#error').show();
                    $('#success').hide();
                    var result = '<div class="alert alert-warning alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>' + @json(__('message.whoops')) + ' </strong><br><ul><li>' + data.message + '</li></ul></div>';
                    $('#error').html(result);
                } else {
                    $('#error').hide();
                    $('#success').show();
                    var result =  '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Success! </strong>'+data.message+'!</div>';
                    $('#success').html(result);
                }
            },error: function (response) {
                $('#createtenancy').attr('disabled',false)
                $("#createtenancy").html("<i class='fa fa-check'>&nbsp;&nbsp;</i>Submit");
                $("#generate").html("<i class='fa fa-check'>&nbsp;&nbsp;</i>Submit");
                if(response.status == 422) {

                    var html = '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Whoops! </strong>Something went wrong<ul>';
                    for (var key in response.responseJSON.errors)
                    {
                        html += '<li>' + response.responseJSON.errors[key][0] + '</li>'
                    }

                } else {
                    var html = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>Whoops! </strong>Something went wrong<ul>';
                    html += '<li>' + response.responseJSON.message + '</li>'
                }

                html += '</ul></div>';
                $('#error').show();
                $('#success').hide();
                document.getElementById('error').innerHTML = html;

            }

        })
    }
    $(document).ready(function() {
        $('#tenancy').on('shown.bs.modal', function () {
            $('#userdomain').focus();
        });
    });

    const domainInput = document.getElementById("userdomain");
    const validationMessage = document.getElementById("validationMessage");

    domainInput.addEventListener("input", function() {
      const domain = domainInput.value;

      if (domain.length > 28) {
        validationMessage.textContent = "Domain must be 28 characters or less.";
        validationMessage.style.color = "red";
      } else {
        validationMessage.textContent = "";
        validationMessage.style.color = "";
      }
    });
</script>
