$(document).ready(function(){
     	 var status = $('.checkbox').val();
         if(status ==1) {
         $('#2fa').prop('checked',true)
         } else if(status ==0) {
          $('#2fa').prop('checked',false)
         }
     	});

         $('.closeandrefresh').on('click',function(){
            location.reload();
        })

function copyRecoveryCode() {
    var icon = document.getElementById('copy_icon_recovery');
    var copyText = document.getElementById('recoverycode');
    var nextRecCodeButton = $('#next_rec_code');

    // Check if Clipboard API is available
    if (!navigator.clipboard) {
        console.error('Clipboard API is not supported by your browser.');
        return;
    }

    // Copy the text to clipboard using Clipboard API
    navigator.clipboard.writeText(copyText.value).then(function() {
        // Change icon to indicate success
        icon.classList.remove('fa-clipboard');
        icon.classList.add('fa-check', 'text-success');
        nextRecCodeButton.prop('disabled', false); // Enable the button

        // Reset the icon after 1 second
        setTimeout(function () {
            icon.classList.remove('fa-check', 'text-success');
            icon.classList.add('fa-clipboard');
        }, 1000);
        setTimeout(function(){
            nextRecCodeButton.prop('disabled', true); // Disable button again after 2 seconds
        }, 5000);
    }).catch(function(error) {
        console.error('Failed to copy text: ', error);
    });
}




$('#2fa').on('change',function () {
        if ($(this).prop("checked")) {
            // checked
            
            $('#2fa-modal1').modal('show');
            $('#verify_password').on('click',function(){
                var password = $('#user_password').val();
                var login_type = $('#login_type').val();
                
                if(password.length == 0 && login_type == 'login') {
                $("#verify_password").html("<i class='fa fa-check'></i>" + window.trans.validate);
                $('#passerror').show();
                $('#passerror').html(window.trans.please_enter_password);
                $('#passerror').focus();
                $('#user_password').css("border-color","red");
                $('#passerror').css({"color":"red"});
                return false;
                }
                $("#verify_password").attr('disabled',true);
                $("#verify_password").html("<i class='fa fa-circle-o-notch fa-spin fa-1x fa-fw'></i>" + window.trans.verifying);
                
                $.ajax({
                    url : "verify-password",
                    method : 'GET',
                    data : {
                        "user_password" : password,
                        "login_type" : login_type,
                    },
                    success: function(response) {
                        $('#2fa-modal1').modal('hide');
                        $.ajax({
                            url : "2fa-recovery-code",
                            method : 'post',
                            success: function(response){
                                $('#next_rec_code').attr('disabled',true);
                                $('#alertMessage').hide(); 
                                $('#2fa-recover-code-modal').modal('show');
                                 $('#recoverycode').val(response.message.code);
                                 $('#next_rec_code').on('click',function(){
                                    $('#2fa-recover-code-modal').modal('hide');
                                    $.ajax({
                                        url : "2fa/enable",
                                        method : 'post',
                                        success: function(response) {
                                             $('#svgshow').append(response.data.image);
                                            $('#2fa-modal2').modal('show');
                                             $(".bar-code").attr("hidden",false);
                                             $(".secret-key").attr("hidden",true);
                                            //  document.querySelectorAll('[id="image"]')[0].src = response.data.image;
                                             $('#cantscanit').on('click',function(){
                                                $(".bar-code").attr("hidden",true);
                                                $("#secretkeyid").val(response.data.secret);
                                                $(".secret-key").attr("hidden",false)

                                             })
                                             $('#scanbarcode').on('click',function(){
                                                $('#alertMessage2').hide();
                                                $(".bar-code").attr("hidden",false);
                                                // document.querySelectorAll('[id="image"]')[0].src = response.data.image;
                                                $(".secret-key").attr("hidden",true)

                                             })

                                             $('#scan_complete').on('click',function(){
                                                $('#2fa-modal2').modal('hide');
                                                 $('#2fa-modal3').modal('show');
                                                 $('#pass_btn').on('click',function(){
                                                    var code = $('#passcode').val();
                                                    if(code.length == 0) {
                                                        $('#passcodeerror').show();
                                                        $('#passcodeerror').html(window.trans.please_enter_code);
                                                        $('#passcodeerror').focus();
                                                      $('#passcode').css("border-color","red");
                                                      $('#passcodeerror').css({"color":"red"});
                                                      return false;
                                                    }
                                                    $("#pass_btn").attr('disabled',true);
                                                    $("#pass_btn").html("<i class='fa fa-circle-o-notch fa-spin fa-1x fa-fw'></i>" + window.trans.verifying);
                                                    $.ajax({
                                                        url : "2fa/setupValidate",
                                                        method : 'POST',
                                                        data : {
                                                            'totp' : code,
                                                        },
                                                    success : function(response) {
                                                        $("#pass_btn").attr('disabled',false);
                                                       $('#2fa-modal3').modal('hide');
                                                        $('#2fa-modal4').modal('show');
                                                        setTimeout(function(){
                                                            location.reload();
                                                        },5000);
                                                    },
                                                    error: function (data) {
                                                        $("#pass_btn").attr('disabled',false);
                                                    $("#pass_btn").html("<i class='fa fa-check'></i>" + window.trans.verify);
                                                    $('#passcodeerror').show();
                                                    $('#passcodeerror').html(window.trans.wrong_code);
                                                     $('#passcodeerror').focus();
                                                      $('#passcode').css("border-color","red");
                                                     $('#passcodeerror').css({"color":"red"});
                                                    },
                                                    })
                                                    
                                                 })
                                                 $('#prev_button').on('click',function(){
                                                    $('#2fa-modal3').modal('hide');
                                                    $('#2fa-modal2').modal('show');
                                                 })
                                             })
                                        },
                                    });
                                 })
                            },
                           
                        })
                        }, 
                        error: function (data) {
                            
                                        $("#verify_password").attr('disabled',false);
                                        $("#verify_password").html("<i class='fa fa-check'></i>" + window.trans.validate);
                                        $('#passerror').show();
                                        $('#passerror').html(window.trans.incorrect_password);
                                         $('#passerror').focus();
                                          $('#user_password').css("border-color","red");
                                         $('#passerror').css({"color":"red"});
                            },
                    })
                        
            
                }) 
   
        } else {
            $('#2fa-modal5').modal('show');
            $('#turnoff2fa').on('click', function () {
                $("#turnoff2fa").attr('disabled', true);
                $("#turnoff2fa").html("<i class='fa fa-circle-o-notch fa-spin fa-1x fa-fw'></i>" + window.trans.please_wait);

                $.ajax({
                    url: "2fa/disable",
                    method: 'post',
                    success: function (response) {
                        $("#turnoff2fa").attr('disabled', false);
                        $("#turnoff2fa").html("<i class='fa fa-power-off'></i>" + window.trans.turned_off);

                        var result = '<div class="alert alert-success alert-dismissible">' +
                            '<button type="button" class="btn-close" data-dismiss="alert" aria-hidden="true"></button>' +
                            '<strong></strong>' + response.message + '.</div>';

                        $('#alertMessage').html(result);

                        setTimeout(function () {
                            location.reload();
                        }, 5000);
                    },
                    complete: function () {
                        $('#2fa-modal5').modal('hide');
                    }
                });
            });
        }
    })

     $('#viewRecCode').on('click',function(){
        
        $.ajax({
            url: "get-recovery-code",
            method: 'get',
            success: function(response){
                 $('#alertMessage4').hide();
                $('#2fa-view-code-modal').modal('show');
                $('#newrecoverycode').val(response.message.code);
            }
        })
        $('#generateNewCode').on('click',function(){

            $("#generateNewCode").attr('disabled',true)
            $("#generateNewCode").html("<i class='fa fa-circle-o-notch fa-spin fa-1x fa-fw'></i>" + window.trans.please_wait);
            $.ajax({
                url: "2fa-recovery-code",
                method: 'post',
                success: function(response){
                    $('#newrecoverycode').val(response.message.code)
                    $('#alertMessage4').hide();
                    $('#alertMessage3').show();

                    $("#generateNewCode").attr('disabled',false);
                    $("#generateNewCode").html(window.trans.generate_new);
                    var result = '<div class="alert alert-success alert-dismissable">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                        '<strong></strong>' + window.trans.new_code_generated + '</div>';
                        $('#alertMessage3').html(result);

                    },
            })
        })
     })

function copyNewRecoveryCode() {
    var icon = document.getElementById('copy_icon');
    var copyText = document.getElementById('newrecoverycode');

    // Copy the text to clipboard using Clipboard API
    navigator.clipboard.writeText(copyText.value).then(function() {
        // Change icon to indicate success
        icon.classList.remove('fa-clipboard');
        icon.classList.add('fa-check', 'text-success');

        // Reset the icon after 1 second
        setTimeout(function () {
            icon.classList.remove('fa-check', 'text-success');
            icon.classList.add('fa-clipboard');
        }, 1000);
    }).catch(function(error) {
        console.error('Failed to copy text: ', error);
    });
}
