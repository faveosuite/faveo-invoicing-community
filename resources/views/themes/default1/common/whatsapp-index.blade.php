@extends('themes.default1.layouts.master')
@section('title')
    {{ __('message.whatsapp_users') }}
@stop
@section('content-header')

    <div class="col-sm-6 md-6">
        <h1>{{ __('message.whatsapp_users') }}</h1>
    </div>
    <div class="col-sm-6 md-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> {{ __('message.home')}}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"> {{ __('message.settings')}}</a></li>
            <li class="breadcrumb-item active">{{ __('message.whatsapp_users') }}</li>
        </ol>
    </div><!-- /.col -->

@stop
@section('content')
<?php
$products= App\Model\Product\Product::get();
?>

        <!-- /.box-header -->
<div class="card card-secondary card-outline">

        <div class="card-body table-responsive">
            <div id="alertMessage12"></div>
            <div id="successmsg"></div>
            <div class="row" style="height:760px">
                <div class="col-md-12">
                    <button type="button" class="btn btn-primary float-right mb-2"
                            data-toggle="modal" data-target="#whatsapp-integration">
                        {{__('message.manual_number_title')}}
                    </button>

                    <table id="custom-table" class="table display" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                            <th>{{__('message.user_name')}}</th>
                            <th>{{__('message.phone_number')}}</th>
                            <th>{{__('message.waba_id')}}</th>
                            <th>{{__('message.phone_number_id')}}</th>
                            <th>{{__('message.business_id')}}</th>
                            <th>{{__('message.create_at')}}</th>
                            <th>{{__('message.action')}}</th>

                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
</div>

<div class="modal fade" id="whatsapp-integration" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{__('message.manual_whatsapp_integration')}}</h4>
            </div>
            <div class="modal-body">
                <div id="alertMessage-whatsapp"></div>

                <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! html()->label(__('message.phone_number'), 'app_id')->class('required') !!}
                    {!! html()->text('phone_number')->class('form-control whatsapp-app-id')->id('whatsapp-app-id') !!}
                    <h6 id="pipedrive_keycheck"></h6>
                </div>
                <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! html()->label(__('message.phone_number_id'), 'app_id')->class('required') !!}
                    {!! html()->text('phone_numnber_id')->class('form-control whatsapp-app-secret')->id('whatsapp-app-secret') !!}
                    <h6 id="pipedrive_keycheck"></h6>
                </div>
                <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! html()->label(__('message.waba_id'), 'config_id')->class('required') !!}
                    {!! html()->text('waba_id')->class('form-control whatsapp-config-id')->id('whatsapp-config-id') !!}
                    <h6 id="pipedrive_keycheck"></h6>
                </div>
                <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! html()->label(__('message.whatsapp_access_token'), 'verify_token')->class('required') !!}
                    {!! html()->text('access_token')->class('form-control whatsapp-verify-token')->id('whatsapp-verify-token') !!}
                    <h6 id="pipedrive_keycheck"></h6>
                </div>
                <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! html()->label(__('message.callback_url'), 'verify_token')->class('required') !!}
                    {!! html()->text('user_callback_url')->class('form-control whatsapp-verify-token')->id('user_callback_url') !!}
                    <h6 id="pipedrive_keycheck"></h6>
                </div>

                <div class= "form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! html()->label(__('message.business_id'), 'verify_token')->class('required') !!}
                    {!! html()->text('business_id')->class('form-control whatsapp-verify-token')->id('business_id') !!}
                    <h6 id="pipedrive_keycheck"></h6>
                </div>

            </div>

            <div class="modal-footer justify-content-between">
                <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
                <button type="submit" class="form-group btn btn-primary"  id="whatsapp-submit"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="Whatsapp-url-edit" tabindex="-1" role="dialog" aria-labelledby="autorenewModalLabel" aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h4 class="modal-title" id="autorenewModalLabel">{{ __('message.whatsapp_product_heading')}}</h4>
            </div>

            <div class="modal-body">
                <div id="alertMessage-webhook"></div>

                <div class="row">

                    <div class="form-group col">

                        <label class="form-label">{{ __('message.callback_url')}} <span class="text-danger"> *</span>
                            <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="{{__('message.webhook_explanation')}}"></i>
                        </label>
                        <div class="custom-select-1">
                            {!! html()->text('webhook_url_edit')->class('form-control')->id('webhook_url_edit')->placeholder('https://example.com') !!}
                            <div class="space"></div>
                        </div>
                        <input type="hidden" id="webhook_id">
                    </div>

                </div>
            </div>


            <div class="modal-footer justify-content-between">

                <button type="button" id="close" class="btn btn-default pull-left closebutton" data-dismiss="modal"><i class="fa fa-times"></i>&nbsp;{{ __('message.close') }}</button>
                <button type="submit" class="form-group btn btn-primary"  id="whatsapp_close_edit"><i class="fa fa-save">&nbsp;</i>{!!Lang::get('message.save')!!}</button>

{{--                <button type="button" class="btn btn-primary" id="whatsapp_close_edit">{{ __('message.save')}}</button>--}}
            </div>
        </div>
    </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('#products').select2({
            placeholder: "Select products",
            allowClear: true
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('#custom-table').DataTable({
            processing: true,
            serverSide: true,
            stateSave: false,

            ajax: "{{ url('whatsapp-users-table') }}", // Calls the separate function

            oLanguage: {
                sLengthMenu: "_MENU_ Records per page",
                sSearch: "<span style='right: 180px;'>Search:</span> ",
                {{--sProcessing: ' <div class="overlay dataTables_processing"><i class="fas fa-3x fa-sync-alt fa-spin" style=" margin-top: -25px;"></i><div class="text-bold pt-2">{{ __('message.loading') }}</div></div>'--}}
                sProcessing: ' <div class="overlay dataTables_processing"><i class="fas fa-3x fa-sync-alt fa-spin" style=" margin-top: -25px;"></i><div class="text-bold pt-2">{!! __('message.loading') !!}</div></div>'

            },
            language: {
                paginate: {
                    first:      "{{ __('message.paginate_first') }}",
                    last:       "{{ __('message.paginate_last') }}",
                    next:       "{{ __('message.paginate_next') }}",
                    previous:   "{{ __('message.paginate_previous') }}"
                },
                emptyTable:     "{{ __('message.empty_table') }}",
                info:           "{{ __('message.datatable_info') }}",
                zeroRecords:    "{{ __('message.no_matching_records_found') }} ",
                infoEmpty:      "{{ __('message.info_empty') }}",
                infoFiltered:   "{{ __('message.info_filtered') }}",
                lengthMenu:     "{{ __('message.length_menu') }}",
                loadingRecords: "{{ __('message.loading_records') }}",
                search:         "{{ __('message.table_search') }}",
            },

            // Apply 'no-sort' class only to specific targets (3rd and 4th columns)
            columnDefs: [
                {
                    targets: [2, 3], // Status and Action columns
                    orderable: false
                }
            ],

            columns: [
                { data: 'UserName', name: 'UserName', orderable: true, searchable: true },
                { data: 'PhoneNumber', name: 'PhoneNumber', orderable: true, searchable: true },
                { data: 'WabaId', name: 'WabaId', orderable: true, searchable: true },
                { data: 'PhoneNumberId', name: 'PhoneNumberId', orderable: true, searchable: true },
                { data: 'BusinessId', name: 'BusinessId', orderable: true, searchable: true },
                { data: 'created_at', name: 'created_at', orderable: true, searchable: true },
                { data: 'action', name: 'action', orderable: false, searchable: false }

            ]
        });
    });

    $(document).on('click', '.copy-btn', function() {
        const button = $(this);
        const token = button.data('token');
        const message = button.siblings('.copy-msg');

        navigator.clipboard.writeText(token).then(() => {
            message.fadeIn(200).delay(1000).fadeOut(400);
        });
    });

    function deleteWhatsappUser(id) {
        var id = id;
        var orderId = orderId;
        var swl=swal.fire({
            title:"<h2 class='swal2-title custom-title'>{{Lang::get('message.Delete')}}",
            html: "<div class='swal2-html-container custom-content'>" +
                "<div class='section-sa'>" +
                "<p>Are you sure you want to delete this number?" +"?</p></div>"+
                "</div>",
            showCancelButton: true,
            cancelButtonText: "{{ __('message.cancel') }}",
            showCloseButton: true,
            position:"top",
            width:"600px",

            confirmButtonText: @json(trans('message.Delete')),
            confirmButtonColor: "#007bff",

        }).then((result)=> {
            if(id.length > 0){
                if (result.isConfirmed) {

                    $.ajax({
                        url: "{!! url('whatsapp-deregister') !!}",
                        method: "post",
                        data: { 'id': id},
                        success: function (data) {
                            if (data.success === true) {
                                var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-check"></i>{{ __('message.success') }}! </strong>' + data.message + '!</div>';
                                $('#successmsg').show();
                                $('#error').hide();
                                $('#successmsg').html(result);
                                setInterval(function () {
                                    $('#successmsg').slideUp(5000);
                                    location.reload();
                                }, 3000);
                            } else if (data.success === false) {
                                $('#successmsg').hide();
                                $('#error').show();
                                var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-ban"></i>{{ __('message.whoops') }} </strong> {{ __('message.something_wrong') }}<br>' + data.message + '!</div>';
                                $('#error').html(result);
                                setInterval(function () {
                                    $('#error').slideUp(5000);
                                    location.reload();
                                }, 10000);
                            }
                        },
                        error: function (data) {
                            $('#successmsg').hide();
                            $('#error').show();
                            var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-ban"></i>{{ __('message.whoops') }} </strong> {{ __('message.something_wrong') }}<br>' + data.responseJSON.message + '!</div>';
                            $('#error').html(result);
                            setInterval(function () {
                                $('#error').slideUp(5000);
                                location.reload();
                            }, 10000);
                        },

                    });
                } else {
                    window.close();
                }
            }else if (result.dismiss === Swal.DismissReason.cancel) {
                // Action if "No" is clicked
                window.close();             }
        })
        return false;
    }

    $("#whatsapp-submit").on('click',function (e){ //When Submit button is clicked
        if ($('#whatsapp-status').prop('checked')) {//if button is on
            var whatsappStatus = 1;

        } else {

            var whatsappStatus = 0;
        }



        const userRequiredFields = {
            name:@json(trans('message.phone_number_error')),
            type:@json(trans('message.phone_number_id_error')),
            config:@json(trans('message.waba_id_error')),
            token:@json(trans('message.access_token_error')),
            callback_url:@json(trans('message.callback_url_error')),
            business_id:@json(trans('message.business_id_error')),
        };
        var app_id=$('#whatsapp-app-id');
        var app_secret=$('#whatsapp-app-secret');
        var config_id=$('#whatsapp-config-id');
        var token=$('#whatsapp-verify-token');
        var callback_url=$('#user_callback_url');
        var business_id=$('#business_id');
        const userFields = {
            name:app_id,
            type:app_secret,
            config:config_id,
            token:token,
            callback_url:callback_url,
            business_id:business_id,
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
        if (isValid && !isValidURL(userFields.callback_url.val())) {
            showError(userFields.callback_url,'Please enter a Valid URL',);
            isValid = false;
        }
        // If validation fails, prevent form submission
        if (!isValid) {
            preventDefault();
        }

        $("#whatsapp-submit").html("<i class='fas fa-circle-notch fa-spin'></i>  {{ __('message.please_wait') }}");
        $.ajax ({
            url: '{{url("direct-whatsapp")}}',
            type : 'post',
            data: {
                "phone_number": app_id.val(),"phone_number_id" : app_secret.val(),'waba_id':config_id.val(),'access_token':token.val(),'user_id':{{$user_id}},'user_callback_url':callback_url.val(),'business_id':business_id.val(),
            },
            success: function (data) {
                setTimeout(function () {
                    location.reload();
                }, 3000);
                $('#alertMessage-whatsapp').show();
                var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> {{ __('message.success') }}! </strong>' + data.message + '</div>';
                $('#alertMessage-whatsapp').html(result);
                $("#whatsapp-submit").html("<i class='fa fa-save'>&nbsp;</i>{{ __('message.save') }}");
                setInterval(function () {
                    $('#alertMessage-whatsapp').slideUp(3000);
                }, 1000);
            },
            error:function(data){
                $('#alertMessage-whatsapp').show();
                var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-ban"></i> {{ __('message.error') }} </strong>' + data.message + '</div>';
                $('#alertMessage-whatsapp').html(result);
                $("#whatsapp-submit").html("<i class='fa fa-save'>&nbsp;</i>{{ __('message.save') }}");
                setInterval(function () {
                    $('#alertMessage-whatsapp').slideUp(2000);
                }, 6000);
            },
        })
    });


    function isValidURL(str) {
        try {
            new URL(str);
            return true;
        } catch (_) {
            return false;
        }
    }


    const removeErrorMessage = (field) => {
        field.classList.remove('is-invalid');
        const error = field.nextElementSibling;
        if (error && error.classList.contains('error')) {
            error.remove();
        }
    };

    ['whatsapp-app-id','whatsapp-app-secret','whatsapp-config-id','whatsapp-verify-token','user_callback_url','business_id'].forEach(id => {

        document.getElementById(id).addEventListener('input', function () {
            removeErrorMessage(this);

        });
    });

    function editWhatsappUser(id){
        $.ajax({
            url: "{!! url('get-webhook-url') !!}",
            method: "get",
            data: { 'id': id},
            success: function (data) {
                url=data.data.url;
                id=data.data.id;
                $('#webhook_url_edit').val(url);
                $('#webhook_id').val(id);

                $('#Whatsapp-url-edit').modal('show');
            },
            error: function (data) {
                $('#successmsg').hide();
                $('#error').show();
                var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-label="{{ __('message.close') }}"><span aria-hidden="true">&times;</span></button><strong><i class="fa fa-ban"></i>{{ __('message.whoops') }} </strong> {{ __('message.something_wrong') }}<br>' + data.responseJSON.message + '!</div>';
                $('#error').html(result);
                setInterval(function () {
                    $('#error').slideUp(5000);
                    location.reload();
                }, 10000);
            },

        });
    }

    $('#whatsapp_close_edit').on('click',function(e){

        const userRequiredFields = {
            name:@json(trans('message.callback_url_error')),


        };
        var webhook_url=$('#webhook_url_edit');

        const userFields = {
            name:webhook_url,

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

        if (isValid && !isValidURL(userFields.name.val())) {
            showError(userFields.name,@json(trans('message.callback_url_error')));
            isValid = false;
        }

        // If validation fails, prevent form submission
        if (!isValid) {
            e.preventDefault();
        }else{
            var url=webhook_url.val();
            var id=$('#webhook_id').val();
            // var token=$('#verify_token').val();
            $.ajax({
                data: {'url' : url,'id': id,},
                url: '{{url("webhook-url-edit")}}',
                method: 'POST',
                dataType: 'json',
                success: function (response) {
                    $('#alertMessage-webhook').show();
                    var result = '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> ' + @json(__('message.success')) +'! </strong>' + response.message + '.</div>';
                    $('#alertMessage-webhook').html(result);
                    $("#whatsapp_close_edit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setTimeout(function () {
                        $('#alertMessage-webhook').slideUp(3000, function () {
                            setTimeout(function () {
                                location.reload();
                            }, 1000);
                        });
                    })
                },
                error: function (response) {
                    $('#alertMessage-webhook').show();
                    var result = '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><strong><i class="fa fa-check"></i> ' + @json(__('message.success')) +'! </strong>' + response.message + '.</div>';
                    $('#alertMessage-webhook').html(result);
                    $("#whatsapp_close_edit").html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ __('message.save') }}");
                    setTimeout(function () {
                        $('#alertMessage-webhook').slideUp(3000, function () {
                            setTimeout(function () {
                                location.reload();
                            }, 1000);
                        });
                    })
                },
            });
        }
    })

</script>
    @stop