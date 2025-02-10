<a data-id="{{$orderNumber}}" href="#edittenant" class="btn btn-light-scale-2 btn-sm text-dark" data-toggle="modal"><i class="fa fa-edit" data-toggle="tooltip" title="{{ __('message.change_domain')}}"></i>&nbsp;</a>
<div class="modal fade" id="edittenant" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            {!! Form::open() !!}
            <div class="modal-header">
                <h4 class="modal-title">{{ __('message.change_faveo')}}</h4>
            </div>

            <div class="modal-body">
                <div id="success">
                </div>
                <div id="error">
                </div>
                <!-- Form  -->

                <div class="container">
                    <form action="" method="post" style="width:500px; margin: auto auto;" class="card card-body">
                        <input type="hidden" id="orderNo" name="order" value={{$orderNumber}}>
                        <div class="form-group">
                            <label>{{ __('message.current_domain')}}</label>
                            <div class="row" style="margin-left: 2px; margin-right: 2px;">
                                <input type="hidden"  name="order" id="orderId" value=""/>
                                <input  type="text" name="userdomain" autocomplete="off" id= "userdomain"  class="form-control col col-12" placeholder="billing.faveocloud.com" required>
                                <!-- <input type="text" class="form-control col col-8" value=".faveocloud.com" disabled="true">-->
                            </div>
                            <br>
                            <label>{{ __('message.enter_domain')}}</label>
                            <div class="row" style="margin-left: 2px; margin-right: 2px;">
                                <input type="hidden"  name="order" id="orderId" value=""/>
                                <input  type="text" name="usernewdomain" autocomplete="off" id= "usernewdomain"  class="form-control col col-12" placeholder="billing.faveocloud.com" required>
                                <!-- <input type="text" class="form-control col col-8" value=".faveocloud.com" disabled="true">-->
                            </div>
                        </div>
                    </form>
                </div>



            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left closebutton" id="closebutton" data-dismiss="modal"><i class="fa fa-times">&nbsp;&nbsp;</i>{{ __('message.close')}}</button>
                <button type="submit"  class="btn btn-primary createTenant" id="createTenant" onclick="changeTenantDomain()"><i class="fa fa-check">&nbsp;&nbsp;</i>{{ __('message.submit')}}</button>
                {!! Form::close()  !!}
            </div>
            <!-- /Form -->
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>

    $('.closebutton').on('click',function(){
        location.reload();
    });

    function changeTenantDomain(){
        $('#createTenant').attr('disabled',true)
        $("#createTenant").html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait')}}");
        var domain = $('#userdomain').val();
        console.log(domain);
        var domainNew = $('#usernewdomain').val();
        var order = $('#orderId').val();
        $.ajax({
            url: "{{url('change/domain')}}",
            type: "POST",
            data: {'currentDomain': domain, 'newDomain': domainNew, 'orderNo': order},
            success: function (data) {
                $('#createTenant').attr('disabled',false)
                $("#createTenant").html("<i class='fa fa-check'>&nbsp;&nbsp;</i>" + @json(__('message.submit')));
                if(data.status == 'validationFailure') {

                    var html = '<div class="alert alert-danger">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
                        '<strong>' + @json(__('message.whoops')) + '</strong> ' + @json(__('message.something_wrong')) +
                            '<ul>';

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
                    var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>' + @json(__('message.Whoops!')) + ' </strong>' + @json(__('message.something_wrong')) + '<br><ul><li>' + data.message + '</li></ul></div>';
                    $('#error').html(result);
                } else if(data.status == 'success_with_warning') {
                    console.log('here');
                    $('#error').show();
                    $('#success').hide();
                    var result = '<div class="alert alert-warning alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>' + @json(__('message.whoops!')) + ' </strong><br><ul><li>' + data.message + '</li></ul></div>';
                    $('#error').html(result);
                } else {
                    $('#error').hide();
                    $('#success').show();
                    var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-check"></i> ' + @json(__('message.success')) + ' </strong>' + data.message + '!</div>';
                    $('#success').html(result);
                }
            },error: function (response) {
                $('#createTenant').attr('disabled',false)
                $("#createTenant").html("<i class='fa fa-check'>&nbsp;&nbsp;</i>" + @json(__('message.submit')));
                $("#generate").html("<i class='fa fa-check'>&nbsp;&nbsp;</i>" + @json(__('message.submit')));
                if(response.status == 422) {

                    var html = '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>' + @json(__('message.whoops')) + ' </strong>' + @json(__('message.something_wrong')) + '<ul>';
                    for (var key in response.responseJSON.errors)
                    {
                        html += '<li>' + response.responseJSON.errors[key][0] + '</li>'
                    }

                } else {
                    var html = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong>' + @json(__('message.whoops')) + ' </strong>' + @json(__('message.something_wrong')) + '<ul>';
                    html += '<li>' + response.responseJSON.message + '</li>'
                }

                html += '</ul></div>';
                $('#error').show();
                $('#success').hide();
                document.getElementById('error').innerHTML = html;

            }

        })
    }
</script>