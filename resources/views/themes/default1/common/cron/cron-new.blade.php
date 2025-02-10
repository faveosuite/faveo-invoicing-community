@section('custom-css')

<style type="text/css">
.noselect {
  -webkit-touch-callout: none; /* iOS Safari */
    -webkit-user-select: none; /* Safari */
     -khtml-user-select: none; /* Konqueror HTML */
       -moz-user-select: none; /* Firefox */
        -ms-user-select: none; /* Internet Explorer/Edge */
            user-select: none; /* Non-prefixed version, currently
                                  supported by Chrome and Opera */
}
</style>
@stop

      {{-- alert block --}}
        <div class="alert alert-success cron-success alert-dismissable" style="display: none;">
            <i class="fa  fa-check-circle"></i>
            <span class="alert-success-message"></span>
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        </div>
        <div class="alert alert-danger cron-danger" style="display: none;">
            <i class="fa fa-ban"></i>
            <span class="alert-danger-message"></span>
             <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        </div>
        {{-- alert block end --}}
        @if(!$execEnabled)
        <div class="alert alert-warning">
            {{Lang::get('message.please_enable_php_exec_for_cronjob_check')}}
        </div>
        @endif


{!! html()->modelForm($status, 'PATCH', url('post-scheduler'))->id('Form')->open() !!}
<div class="card-header">
        <h4 class="card-title">{{Lang::get('message.cron')}} </h4>


    </div>

    <div class="card-body table-responsive"style="overflow:hidden;">
  <div class="row">
                <div class="col-md-12">
                   <p>{{ Lang::get('message.copy-cron-command-description')}} </p>
                </div>
        </div>


         <div class="alert  alert-dismissable" style="background: #F3F3F3">
            <div class="row">
            <div class="col-md-2 copy-command1">
                    <span style="font-size: 20px">*&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;*</span>
                </div>
             <div class="col-md-4">
                    <select class="form-control" id="phpExecutableList" onchange="checksome()">
                        <option value="0">{{ Lang::get('message.specify-php-executable')}}</option>
                        @foreach($paths as $path)
                            <option>{{$path}}</option>
                        @endforeach
                        <option value="Other">{{ __('message.other') }}</option>
                    </select>
                    <div class="has-feedback" id='phpExecutableTextArea' style="display: none;">
                        <div class="has-feedback">
                            <input type="text" class="form-control input-sm" style=" padding:5px;height:34px" name="phpExecutableText" id="phpExecutableText" placeholder="{{Lang::get('message.specify-php-executable')}}">
                            <span class="fa fa-close form-control-feedback" style="pointer-events: initial; cursor: pointer; color: #74777a" onclick="checksome(false)"></span>
                        </div>
                    </div>
                </div>
                  <div class="col-md-5 copy-command2">
                   <span style="font-size: 15px">-q {{$cronPath}} schedule:run 2>&1 </span>
                </div>
                <div class="col-md-1">
                    <span style="font-size: 20px" id="copyBtn" title="{{Lang::get('message.verify-and-copy-command')}}" onclick="verifyPHPExecutableAndCopyCommand()"><i class="fa fa-clipboard"></i></span>
                    <span style="font-size: 20px; display:none;" id="loader"><i class="fas fa-circle-notch fa-spin"></i></span>
                </div>
            </div>
        </div>

     <div class="row">
        <div class="col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-info" style="height: 70px;"><i class="fa fa-envelope"></i></span>
                <!-- Apply any bg-* class to to the icon to color it -->
                <div class="info-box-content" style="display: block;">

                    <div class="col-md-6">

                        <div class="form-group">

                            {!! html()->label()->for('email_fetching')->html(
    Lang::get('message.expiry_mail') .
    ' <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top"
    title="' . Lang::get('message.expiry_mail_tooltip') . '"></i>'
) !!}
                            <br>
                            {!! html()->checkbox('expiry_cron', $condition->checkActiveJob()['expiryMail'])->id('email_fetching') ,1 !!}
                            &nbsp;{{ Lang::get('message.enable_expiry-cron') }}

                        </div>

                    </div>
                    <div class="col-md-6" id="fetching">
                        {!! html()->select('expiry-commands', $commands, $condition->getConditionValue('expiryMail')['condition'])
    ->class('form-control')
    ->id('fetching-command')
!!}

                        <div id="fetching-daily-at">
                            {!! html()->text('expiry-dailyAt', $condition->getConditionValue('expiryMail')['at'])
                                ->class('form-control time-picker')
                                ->placeholder('HH:MM')
                            !!}

                    </div>
                    </div>
                </div>
            </div><!-- /.info-box-content -->

        </div><!-- /.info-box -->

        <div class="col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-info" style="height: 70px;"><i class="fa fa-archive"></i></span>
                <!-- Apply any bg-* class to to the icon to color it -->
                <div class="info-box-content" style="display: block;">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! html()->label(Lang::get('message.delete_activity'))->for('auto_close') !!}
                            <br>
                            {!! html()->checkbox('activity', $condition->checkActiveJob()['deleteLogs'])->id('auto_close') ,1 !!}
                            {{ Lang::get('message.enable_activity_clean') }}
                        </div>
                    </div>
                    <div class="col-md-6" id="workflow">
                        {!! html()->select('activity-commands', $commands, $condition->getConditionValue('deleteLogs')['condition'])
    ->class('form-control')
    ->id('workflow-command')
!!}

                        <div id="workflow-daily-at">
                            {!! html()->text('activity-dailyAt', $condition->getConditionValue('deleteLogs')['at'])
                                ->class('form-control time-picker')
                                ->placeholder('HH:MM')
                            !!}
                        </div>

                    </div>
                </div><!-- /.info-box-content -->
            </div><!-- /.info-box -->
        </div>

         <div class="col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-info" style="height: 70px;"><i class="fa fa-cloud"></i></span>
                <!-- Apply any bg-* class to to the icon to color it -->
                <div class="info-box-content" style="display: block;">

                    <div class="col-md-6">

                        <div class="form-group">

                            {!! html()->label()
     ->for('sub_fetching')
     ->html(
         Lang::get('message.subscription_renewal_reminder_autopayment') .
         ' <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top"
         title="' . Lang::get('message.auto_renewal_reminder_tooltip') . '"></i>'
     )
 !!}
                            <br>
                            {!! html()->checkbox('subs_expirymail', $condition->checkActiveJob()['subsExpirymail'] ,1)
                                ->id('sub_fetching')
                            !!}
                            &nbsp; {{ Lang::get('message.enable_expiry-cron') }}
                            <!-- <input type="checkbox" name="subs_expirymail" value="1"> -->
                        </div>

                    </div>
                    <div class="col-md-6" id="subfetching">
                        {!! html()->select('subexpiry-commands', $commands, $condition->getConditionValue('subsExpirymail')['condition'])
                            ->class('form-control')
                            ->id('subfetching-command')
                        !!}

                        <div id="subfetching-daily-at">
                            {!! html()->text('subexpiry-dailyAt', $condition->getConditionValue('subsExpirymail')['at'])
                                ->class('form-control time-picker')
                                ->placeholder('HH:MM')
                            !!}
                        </div>
                    </div>
                </div>
            </div><!-- /.info-box-content -->

        </div><!-- /.info-box -->





        <div class="col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-info" style="height: 70px;"><i class="fa fa-envelope"></i></span>
                <!-- Apply any bg-* class to to the icon to color it -->
                <div class="info-box-content" style="display: block;">

                    <div class="col-md-6">

                        <div class="form-group">
                            {!! html()->label()->for('postsub_fetching')->html(
        Lang::get('message.subscription_expired') .
        ' <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top"
        title="' . Lang::get('message.auto_renewal_reminder_tooltip') . '"></i>'
    )
!!}
                            <br>
                            {!! html()->checkbox('postsubs_expirymail', $condition->checkActiveJob()['postExpirymail'], 1)
                                ->id('postsub_fetching')
                            !!}
                            &nbsp;{{ Lang::get('message.enable_expiry-cron') }}
                        </div>


                    </div>
                    <div class="col-md-6" id="postsubfetching">
                        {!! html()->select('postsubexpiry-commands', $commands, $condition->getConditionValue('postExpirymail')['condition'])
                            ->class('form-control')
                            ->id('postsubfetching-command')
                        !!}

                        <div id="postsubfetching-daily-at">
                            {!! html()->text('postsubexpiry-dailyAt', $condition->getConditionValue('postExpirymail')['at'])
                                ->class('form-control time-picker')
                                ->placeholder('HH:MM')
                            !!}
                        </div>
                    </div>
                </div>
            </div><!-- /.info-box-content -->

        </div><!-- /.info-box -->

         <div class="col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-info" style="height: 70px;"><i class="fa fa-cloud"></i></span>
                <!-- Apply any bg-* class to to the icon to color it -->
                <div class="info-box-content" style="display: block;">

                    <div class="col-md-6">

                        <div class="form-group">
                            {!! html()->label()->for('cloud_fetching')->class('form-label')->html(
        Lang::get('message.cloud_subscription_deletion') .
        ' <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top"
        title="' . Lang::get('message.cron_trigger_cloud_new') . '"></i>'
    )
!!}

                            <br>

                            {!! html()->checkbox('cloud_cron', $condition->checkActiveJob()['cloud'] ,1)
                                ->id('cloud_fetching')
                            !!}
                            &nbsp;{{ Lang::get('message.enable_faveo_cloud') }}
                        </div>


                    </div>
                    <div class="col-md-6" id="cloud">
                        {!! html()->select('cloud-commands', $commands, $condition->getConditionValue('cloud')['condition'])
                            ->class('form-control')
                            ->id('cloud-command')
                        !!}

                        <div id="cloud-daily-at">
                            {!! html()->text('cloud-dailyAt', $condition->getConditionValue('cloud')['at'])
                                ->class('form-control time-picker')
                                ->placeholder('HH:MM')
                            !!}
                        </div>
                    </div>



                </div>
            </div><!-- /.info-box-content -->

        </div><!-- /.info-box -->

            <div class="col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-info" style="height: 70px;"><i class="fas fa-file-invoice"></i></span>
                <!-- Apply any bg-* class to to the icon to color it -->
                <div class="info-box-content" style="display: block;">

                    <div class="col-md-6">

                        <div class="form-group">

                            <div class="form-group">
                                {!! html()->label(
                                    Lang::get('message.invoice_deletion') .
                                    ' <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="' . Lang::get('message.cron_trigger_deletion_old') . '"></i>'
                                )->for('invoice_fetching')->class('required') !!}

                                <br>

                                {!! html()->checkbox('invoice_cron', $condition->checkActiveJob()['invoice'], 1)
                                    ->id('invoice_fetching')
                                !!}
                                &nbsp; {{ Lang::get('message.enable_invoice_deletion') }}
                            </div>
                        </div>

                    </div>
                    <div class="col-md-6" id="invoice">
                        {!! html()->select('invoice-commands', $commands, $condition->getConditionValue('invoice')['condition'])
                            ->class('form-control')
                            ->id('invoice-command')
                        !!}

                        <div id="invoice-daily-at">
                            {!! html()->text('invoice-dailyAt', $condition->getConditionValue('invoice')['at'])
                                ->class('form-control time-picker')
                                ->placeholder('HH:MM')
                            !!}
                        </div>
                    </div>
                </div>
            </div><!-- /.info-box-content -->

        </div><!-- /.info-box -->

        <div class="col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-info" style="height: 70px;"><i class="fas fa-comments"></i></span>
                <!-- Apply any bg-* class to to the icon to color it -->
                <div class="info-box-content" style="display: block;">

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! html()
                                ->label(
                                    __('message.msg91_reports_deletion').'<i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="' . Lang::get('message.cron_trigger_deletion_msg91_reports') . '"></i>'
                                )
                                ->for('msg91_fetching')
                                ->toHtml()
                            !!}

                            <br>

                            {!! html()
                                ->checkbox('msg91_cron', 1, $condition->checkActiveJob()['msg91Reports'])
                                ->id('msg91_fetching')
                                ->toHtml()
                            !!}
                            &nbsp;{{ Lang::get('message.enable_msg_cron') }}
                        </div>
                    </div>

                    <div class="col-md-6" id="invoice">
                        {!! html()
                            ->select('msg91-commands', $commands)
                            ->class('form-control')
                            ->id('msg91-command')
                            ->value($condition->getConditionValue('msg91Reports')['condition'])
                            ->toHtml()
                        !!}

                        <div id='msg91-daily-at'>
                            {!! html()
                                ->text('msg91-dailyAt', $condition->getConditionValue('msg91Reports')['at'])
                                ->class('form-control time-picker')
                                ->placeholder('HH:MM')
                                ->toHtml()
                            !!}
                        </div>
                    </div>
                </div>
            </div><!-- /.info-box-content -->

        </div><!-- /.info-box -->
    </div>
    <h4><button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> {{ __('message.saving') }}"><i class="fa fa-sync-alt">&nbsp;&nbsp;</i>{!!Lang::get('message.update')!!}</button></h4>
        </div>
    </div>

{!! html()->form()->close() !!}



<script>
    $(function () {
        // Init datetime picker
        $(".time-picker").datetimepicker({ format: 'HH:ss' });

        // Tab switching
        $('#tab2url').on('click', function () {
            $('#tab1').removeClass('active');
            $('#tab2').addClass('active');
        });

        // Toggle sections by checkbox
        function handleToggle(checkboxId, sectionId) {
            const $checkbox = $(`#${checkboxId}`);
            const $section = $(`#${sectionId}`);
            const update = () => $section.toggle($checkbox.is(':checked'));
            update();
            $checkbox.on('click', update);
        }
        // Ldap cron settings end //
    });

        $(document).ready(function () {
$(".time-picker").datetimepicker({
        format: 'HH:ss',
        // useCurrent: false, //Important! See issue #1075
    });


        var checked = $("#cloud_fetching").is(':checked');
        check(checked, 'cloud_fetching');
        $("#cloud_fetching").on('click', function () {
            checked = $("#cloud_fetching").is(':checked');
            check(checked);
        });
        var command = $("#cloud-command").val();
        showDailyAt(command);
        $("#cloud-command").on('change', function () {
            command = $("#cloud-command").val();
            showDailyAt(command);
        });
        function check(checked, id) {
            if (checked) {
                $("#cloud").show();
            } else {
                $("#cloud").hide();
            }
        }
        function showDailyAt(command) {
            if (command === 'dailyAt') {
                $("#cloud-daily-at").show();
                // $("input").prop('required',true);
            } else {
                $("#cloud-daily-at").hide();
            }
        }


            function handleCommand(selectId, targetId) {
                const $select = $(`#${selectId}`);
                const $target = $(`#${targetId}`);
                const update = () => $target.toggle($select.val() === 'dailyAt');
                update();
                $select.on('change', update);
            }

            // Grouped config
            const config = [
                { checkbox: 'email_fetching', section: 'fetching', select: 'fetching-command', daily: 'fetching-daily-at' },
                { checkbox: 'cloud_fetching', section: 'cloud', select: 'cloud-command', daily: 'cloud-daily-at' },
                { checkbox: 'notification_cron', section: 'notification', select: 'notification-command', daily: 'notification-daily-at' },
                { checkbox: 'auto_close', section: 'workflow', select: 'workflow-command', daily: 'workflow-daily-at' },
                { checkbox: 'notification_cron1', section: 'notification1', select: 'notification-command1', daily: 'notification-daily-at1' },
                { checkbox: 'sub_fetching', section: 'subfetching', select: 'subfetching-command', daily: 'subfetching-daily-at' },
                { checkbox: 'postsub_fetching', section: 'postsubfetching', select: 'postsubfetching-command', daily: 'postsubfetching-daily-at' },
                { checkbox: 'invoice_fetching', section: 'invoice', select: 'invoice-command', daily: 'invoice-daily-at' },
                { checkbox: 'msg91_fetching', section: 'msg91', select: 'msg91-command', daily: 'msg91-daily-at' },
            ];

        config.forEach(({ checkbox, section, select, daily }) => {
            handleToggle(checkbox, section);
            handleCommand(select, daily);
        });

        // PHP Executable handling
        $('#phpExecutableList').on('change', function () {
            const val = $(this).val();
            if (val === 'Other') {
                $('#phpExecutableList').hide();
                $('#phpExecutableTextArea').show();
            }
        });

        $('#phpExecutableTextClear').on('click', function () {
            $('#phpExecutableList').val(0).show();
            $('#phpExecutableTextArea').hide();
        });

        // Verify PHP Executable & Copy Command
        $('#copyBtn').on('click', function () {
            const executablePath = ($('#phpExecutableList').val() === "Other")
                ? $('#phpExecutableText').val().trim()
                : $('#phpExecutableList').val().trim();

            const cronCommand = "* * * * * " + executablePath + " " + $(".copy-command2").text().trim();
            copyToClipboard(cronCommand);

            $.ajax({
                method: 'POST',
                url: "{{ route('verify-cron') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    path: executablePath
                },
                beforeSend: function () {
                    $("#loader").show();
                    $(".alert-danger, .alert-success, #copyBtn").hide();
                },
                success: function (result) {
                    $(".alert-success-message").html("{{ Lang::get('message.cron-command-copied') }} " + result.message);
                    $(".cron-success, #copyBtn").show();
                    $("#loader").hide();
                },
                error: function (xhr) {
                    $('#clearClipBoard').click();
                    $(".cron-danger, #copyBtn").show();
                    $("#loader").hide();
                    $(".alert-danger-message").html("{{ Lang::get('message.cron-command-not-copied') }} " + xhr.responseJSON.message);
                }
            });
        });

        // Utility: Copy text to clipboard
        function copyToClipboard(text = " ") {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
            } catch (err) {
                console.error('Copy failed', err);
            }
            document.body.removeChild(textArea);
        }
    });
    $(document).ready(function () {
        var checked = $("#auto_close").is(':checked');
        check(checked, 'auto_close');
        $("#auto_close").on('click', function () {
            checked = $("#auto_close").is(':checked');
            check(checked);
        });
        var command = $("#workflow-command").val();
        showDailyAt(command);
        $("#workflow-command").on('change', function () {
            command = $("#workflow-command").val();
            showDailyAt(command);
        });
        function check(checked, id) {
            if (checked) {
                $("#workflow").show();
            } else {
                $("#workflow").hide();
            }
        }
        function showDailyAt(command) {
            if (command == 'dailyAt') {
                $("#workflow-daily-at").show();
            } else {
                $("#workflow-daily-at").hide();
            }
        }
    });
//follow up
     $(document).ready(function () {
        var checked = $("#notification_cron1").is(':checked');
        check(checked, 'notification_cron1');
        $("#notification_cron1").on('click', function () {
            checked = $("#notification_cron1").is(':checked');
            check(checked);
        });
        var command = $("#notification-command1").val();
        showDailyAt(command);
        $("#notification-command1").on('change', function () {
            command = $("#notification-command1").val();
            showDailyAt(command);
        });
        function check(checked, id) {
            if (checked) {
                $("#notification1").show();
            } else {
                $("#notification1").hide();
            }
        }
        function showDailyAt(command) {
            if (command === 'dailyAt') {
                $("#notification-daily-at1").show();
            } else {
                $("#notification-daily-at1").hide();
            }
        }
    });


         $(document).ready(function () {
$(".time-picker").datetimepicker({
        format: 'HH:ss',
        // useCurrent: false, //Important! See issue #1075
    });



        var checked = $("#sub_fetching").is(':checked');
        check(checked, 'sub_fetching');
        $("#sub_fetching").on('click', function () {
            checked = $("#sub_fetching").is(':checked');
            check(checked);
        });
        var command = $("#subfetching-command").val();
        showDailyAt(command);
        $("#subfetching-command").on('change', function () {
            command = $("#subfetching-command").val();
            showDailyAt(command);
        });
        function check(checked, id) {
            if (checked) {
                $("#subfetching").show();
            } else {
                $("#subfetching").hide();
            }
        }
        function showDailyAt(command) {
            if (command === 'dailyAt') {
                $("#subfetching-daily-at").show();
                // $("input").prop('required',true);
            } else {
                $("#subfetching-daily-at").hide();
            }
        }





        // Ldap cron settings end //
    });



         $(document).ready(function () {
$(".time-picker").datetimepicker({
        format: 'HH:ss',
        // useCurrent: false, //Important! See issue #1075
    });



        var checked = $("#postsub_fetching").is(':checked');
        check(checked, 'postsub_fetching');
        $("#postsub_fetching").on('click', function () {
            checked = $("#postsub_fetching").is(':checked');
            check(checked);
        });
        var command = $("#postsubfetching-command").val();
        showDailyAt(command);
        $("#postsubfetching-command").on('change', function () {
            command = $("#postsubfetching-command").val();
            showDailyAt(command);
        });
        function check(checked, id) {
            if (checked) {
                $("#postsubfetching").show();
            } else {
                $("#postsubfetching").hide();
            }
        }
        function showDailyAt(command) {
            if (command === 'dailyAt') {
                $("#postsubfetching-daily-at").show();
                // $("input").prop('required',true);
            } else {
                $("#postsubfetching-daily-at").hide();
            }
        }





        // Ldap cron settings end //
    });


                 $(document).ready(function () {
$(".time-picker").datetimepicker({
        format: 'HH:ss',
        // useCurrent: false, //Important! See issue #1075
    });


        var checked = $("#invoice_fetching").is(':checked');
        check(checked, 'invoice_fetching');
        $("#invoice_fetching").on('click', function () {
            checked = $("#invoice_fetching").is(':checked');
            check(checked);
        });
        var command = $("#invoice-command").val();
        showDailyAt(command);
        $("#invoice-command").on('change', function () {
            command = $("#invoice-command").val();
            showDailyAt(command);
        });
        function check(checked, id) {
            if (checked) {
                $("#invoice").show();
            } else {
                $("#invoice").hide();
            }
        }
        function showDailyAt(command) {
            if (command === 'dailyAt') {
                $("#invoice-daily-at").show();
                // $("input").prop('required',true);
            } else {
                $("#invoice-daily-at").hide();
            }
        }





        // Ldap cron settings end //
    });

//-------------------------------------------------------------//

    function checksome(showtext = true)
    {
        if (!showtext) {
            $("#phpExecutableList").css('display', "block");
            $("#phpExecutableList").val(0)
            $("#phpExecutableTextArea").css('display', "none");
        } else if($("#phpExecutableList").val() == 'Other') {
            $("#phpExecutableList").css('display', "none");
            $("#phpExecutableTextArea").css('display', "block");
        }
    }

    function verifyPHPExecutableAndCopyCommand()
    {
        copy = false;
        var path = ($("#phpExecutableList").val()=="Other")? $("#phpExecutableText").val(): $("#phpExecutableList").val();
        var text = "* * * * * "+path.trim()+" "+$(".copy-command2").text().trim();
        copyToClipboard(text);

        $.ajax({
            'method': 'post',
            'url': "{{route('verify-cron')}}",
            data: {
                 "_token": "{{ csrf_token() }}",
                "path": path
            },
            beforeSend: function() {
                $("#loader").css("display", "block");
                $(".alert-danger, .alert-success, #copyBtn").css('display', 'none');
            },
            success: function (result,status,xhr) {
                $(".alert-success-message").html("{{Lang::get('message.cron-command-copied')}} "+result.message);
                $(".cron-success, #copyBtn").css('display', 'block');
                $("#loader").css("display", "none");
                copy = true
            },
            error: function(xhr,status,error) {
                $('#clearClipBoard').click();
                $(".cron-danger, #copyBtn").css('display', 'block');
                $("#loader").css("display", "none");
                $(".alert-danger-message").html("{{Lang::get('message.cron-command-not-copied')}} "+xhr.responseJSON.message);
            },
        });
    }

    function copyToClipboard(text = " ")
    {
        var textArea = document.createElement("textarea");
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            var successful = document.execCommand('copy');
            var msg = successful ? 'successful' : 'unsuccessful';
        } catch (err) {
        }
        console.log(msg);
        document.body.removeChild(textArea);
    }
</script>

