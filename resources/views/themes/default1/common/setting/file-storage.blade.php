@extends('themes.default1.layouts.master')
@section('title')
    {{ trans('message.file_storage') }}
@stop
@section('content-header')
    <div class="col-sm-6">
        <h1>{{ trans('message.file_storage') }}</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{url('/')}}"><i
                            class="fa fa-dashboard"></i> {{ trans('message.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{url('settings')}}"><i
                            class="fa fa-dashboard"></i> {{ trans('message.settings') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('message.file_storage') }}</li>
        </ol>
    </div><!-- /.col -->
@stop
@section('content')
    <style>
        .s3_config, .system_config {
            display: none;
        }
    </style>
    <div id="alert-container"></div>
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <div id="response"></div>
            <h5>{{ trans('message.set_file_storage') }}</h5>
        </div>

        <div class="card-body flex">
            <form id="file_form">
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label>{{ trans('message.storage_disk') }} </label>
                        <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top"
                           title="{{ trans('message.disk_tooltip') }}"></i>
                        <select class="form-control" name="disk" id="disk">
                            <option value="system" {{ $fileStorage->disk == 'system' ? 'selected' : '' }}>System
                            </option>
                            <option value="s3" {{ $fileStorage->disk == 's3' ? 'selected' : '' }}>S3</option>
                        </select>
                    </div>

                    <div class="form-group col-sm-6 system_config">
                        <label class="required">{{ trans('message.storage_path') }}</label>
                        <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top"
                           title="{{ trans('message.path_tooltip') }}"></i>
                        <input class="form-control" name="path" type="text" id="path"
                               value="{{ $fileStorage->local_file_storage_path }}"
                               placeholder="{{ __('message.storage_path') }}">
                    </div>


                    <!-- S3 Configuration Fields -->
                    <div class="form-group col-sm-6 s3_config">
                        <label>{{ trans('message.s3_path_style_endpoint') }}</label>
                        <select class="form-control" name="s3_path_style_endpoint" id="s3_path_style_endpoint">
                            <option value="true" {{ $fileStorage->s3_path_style_endpoint === true ? 'selected' : '' }}>{{ __('message.yes') }}</option>
                            <option value="false" {{ $fileStorage->s3_path_style_endpoint !== true ? 'selected' : '' }}>{{ __('message.no') }}</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-6 s3_config">
                        <label class="required">{{ trans('message.s3_bucket') }}</label>
                        <input class="form-control" name="s3_bucket" type="text" id="s3_bucket"
                               placeholder="{{ __('message.enter_bucket_name') }}" value="{{ $fileStorage->s3_bucket }}">
                    </div>
                    <div class="form-group col-sm-6 s3_config">
                        <label class="required">{{ trans('message.s3_region') }}</label>
                        <input class="form-control" name="s3_region" type="text" id="s3_region"
                               placeholder="{{ __('message.enter_region') }}" value="{{ $fileStorage->s3_region }}">
                    </div>
                    <div class="form-group col-sm-6 s3_config">
                        <label class="required">{{ trans('message.s3_access_key') }}</label>
                        <input class="form-control" name="s3_access_key" type="password" id="s3_access_key"
                               placeholder="{{ __('message.enter_access_key') }}" value="{{ $fileStorage->s3_access_key }}">
                    </div>
                    <div class="form-group col-sm-6 s3_config">
                        <label class="required">{{ trans('message.s3_secret_key') }}</label>
                        <input class="form-control" name="s3_secret_key" type="password" id="s3_secret_key"
                               placeholder="{{ __('message.enter_security_key') }}" value="{{ $fileStorage->s3_secret_key }}">
                    </div>

                    <div class="form-group col-sm-6 s3_config">
                        <label class="required">{{ trans('message.s3_endpoint_url') }}</label>
                        <input class="form-control" name="s3_endpoint_url" type="text" id="s3_endpoint_url"
                               placeholder="{{ __('message.enter_endpoint_url') }}" value="{{ $fileStorage->s3_endpoint_url }}">
                    </div>

                    <div class="form-group col-sm-6 s3_config">
                        <label>{{ trans('message.s3_url') }}</label>
                        <input class="form-control" name="s3_url" type="text" id="s3_url"
                               placeholder="{{ __('message.enter_url') }}" value="{{ $fileStorage->s3_url }}">
                    </div>

                </div>

                <button type="submit" class="btn btn-primary pull-right" id="submit"
                        data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving...">
                    <i class="fa fa-save">&nbsp;&nbsp;</i>{{ trans('message.save') }}
                </button>
            </form>
        </div>


    </div>

    <script>
        $('ul.nav-sidebar a').filter(function () {
            return this.id == 'setting';
        }).addClass('active');

        // for treeview
        $('ul.nav-treeview a').filter(function () {
            return this.id == 'setting';
        }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');

        $(document).ready(function () {
            var isS3Enabled = $('#disk').val() === 's3';

            var validator = $("#file_form").validate({
                rules: {
                    disk: {
                        required: true
                    },
                    path: {
                        required: true
                    },
                    s3_bucket: {
                        required: {
                            depends: function () {
                                return isS3Enabled;
                            }
                        }
                    },
                    s3_region: {
                        required: {
                            depends: function () {
                                return isS3Enabled;
                            }
                        }
                    },
                    s3_access_key: {
                        required: {
                            depends: function () {
                                return isS3Enabled;
                            }
                        }
                    },
                    s3_secret_key: {
                        required: {
                            depends: function () {
                                return isS3Enabled;
                            }
                        }
                    },
                    s3_endpoint_url: {
                        required: {
                            depends: function () {
                                return isS3Enabled;
                            }
                        }
                    }
                },
                messages: {
                    disk: {
                        required: "{{ trans('message.disk_required') }}"
                    },
                    path: {
                        required: "{{ trans('message.path_required') }}"
                    },
                    s3_bucket: {
                        required: "{{ trans('message.s3_bucket_required') }}"
                    },
                    s3_region: {
                        required: "{{ trans('message.s3_region_required') }}"
                    },
                    s3_access_key: {
                        required: "{{ trans('message.s3_access_key_required') }}"
                    },
                    s3_secret_key: {
                        required: "{{ trans('message.s3_secret_key_required') }}"
                    },
                    s3_endpoint_url: {
                        required: "{{ trans('message.s3_endpoint_url_required') }}"
                    }
                },
                errorClass: "is-invalid",
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    error.insertAfter(element);
                    error.css('font-weight', 'normal');
                },
                submitHandler: function (form) {
                    let formData = $(form).serialize();
                    let submitButton = $('#submit');
                    $.ajax({
                        url: '{{ url('/file-storage-path') }}',
                        type: 'POST',
                        data: formData,
                        beforeSend: function () {
                            submitButton.prop('disabled', true).html(submitButton.data('loading-text'));
                        },
                        success: function (response) {
                            showAlert('success', response.message);
                        },
                        error: function (error) {
                            if (error.status === 422) {
                                showValidationErrors(error.responseJSON.errors);
                            } else {
                                showAlert('error', error.responseJSON.message);
                            }
                        },
                        complete: function () {
                            submitButton.prop('disabled', false).html("<i class='fa fa-save'>&nbsp;&nbsp;</i>{{ trans('message.save') }}");
                        }
                    });
                }
            });

            function showValidationErrors(errors) {

                $.each(errors, function (field, messages) {
                    validator.showErrors({
                        [field]: messages[0]
                    });
                });

            }

            function showAlert(type, message) {
                let icon = (type === 'success') ? 'fa-check-circle' : 'fa-ban';
                let alertType = (type === 'success') ? 'alert-success' : 'alert-danger';

                $('#alert-container').html(`
        <div class="alert ${alertType} alert-dismissable">
            <i class="fa ${icon}"></i> ${message}
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        </div>
    `);

                setTimeout(function () {
                    $('#alert-container .alert').fadeOut('slow', function () {
                        $(this).remove();
                    });
                }, 5000);
            }

            $('#disk').change(function () {
                isS3Enabled = $(this).val() === 's3';
                $('.s3_config').toggle(isS3Enabled);
                $('.system_config').toggle(!isS3Enabled);
                validator.resetForm();
                $('#file_form').find('.is-invalid').removeClass('is-invalid');
                $('#file_form').find('.invalid-feedback').remove();
            }).trigger('change');
            $(document).ready(function () {
                $('[data-toggle="tooltip"]').tooltip();
            });
        });
    </script>

@stop

