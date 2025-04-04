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




    {!! Form::model($status,['url' => 'post-scheduler', 'method' => 'PATCH','id'=>'Form']) !!}
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
                        <option value="Other">Other</option>
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

                            {!! Form::label('email_fetching', Lang::get('message.expiry_mail') . ' <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="This cron is to trigger email which are sent out to users before product expiry reminding them to renew the product. This email is send out only to those who have not enabled auto renewal"></i>', [], false) !!}<br>
                            {!! Form::checkbox('expiry_cron',1,$condition->checkActiveJob()['expiryMail'],['id'=>'email_fetching']) !!}&nbsp;{{Lang::get('message.enable_expiry-cron')}}
                        </div>

                    </div>
                    <div class="col-md-6" id="fetching">
                        {!! Form::select('expiry-commands',$commands,$condition->getConditionValue('expiryMail')['condition'],['class'=>'form-control','id'=>'fetching-command']) !!}
                          <div id='fetching-daily-at'>
                            {!! Form::text('expiry-dailyAt',$condition->getConditionValue('expiryMail')['at'],['class'=>'form-control time-picker',"placeholder" => "HH:MM"]) !!}

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
                            {!! Form::label('activity',Lang::get('message.delete_activity')) !!}<br>
                            {!! Form::checkbox('activity',1,$condition->checkActiveJob()['deleteLogs'],['id'=>'auto_close']) !!}
                                   {{Lang::get('message.enable_activity_clean')}}
                        </div>
                    </div>
                    <div class="col-md-6" id="workflow">
                        {!! Form::select('activity-commands',$commands,$condition->getConditionValue('deleteLogs')['condition'],['class'=>'form-control','id'=>'workflow-command']) !!}
                         <div id='workflow-daily-at'>
                            {!! Form::text('activity-dailyAt',$condition->getConditionValue('deleteLogs')['at'],['class'=>'form-control time-picker',"placeholder" => "HH:MM"]) !!}

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

                            {!! Form::label('sub_fetching', Lang::get('Subscription renewal reminder - Auto payment') . ' <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="This cron is to trigger email which are sent out to users before product expiry reminding them product will be renewed automatically. This email is send out only to those who have enabled auto renewal"></i>', [], false) !!}<br>
                            {!! Form::checkbox('subs_expirymail',1,$condition->checkActiveJob()['subsExpirymail'],['id'=>'sub_fetching']) !!}&nbsp;{{Lang::get('message.enable_expiry-cron')}}
                            <!-- <input type="checkbox" name="subs_expirymail" value="1"> -->
                        </div>

                    </div>
                    <div class="col-md-6" id="subfetching">
                        {!! Form::select('subexpiry-commands',$commands,$condition->getConditionValue('subsExpirymail')['condition'],['class'=>'form-control','id'=>'subfetching-command']) !!}
                          <div id='subfetching-daily-at'>
                        {!! Form::text('subexpiry-dailyAt',$condition->getConditionValue('subsExpirymail')['at'],['class'=>'form-control time-picker',"placeholder" => "HH:MM"]) !!}

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

                            {!! Form::label('postsub_fetching', Lang::get('Subscription expired') . ' <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="This cron is to trigger email which are sent out to users after product expiry reminding them to renew the product. This email is send out to all users using auto renewal or are using manual payment method. For self hosted and cloud both"></i>', [], false) !!}<br>
                            {!! Form::checkbox('postsubs_expirymail',1,$condition->checkActiveJob()['postExpirymail'],['id'=>'postsub_fetching']) !!}&nbsp;{{Lang::get('message.enable_expiry-cron')}}
                        </div>

                    </div>
                    <div class="col-md-6" id="postsubfetching">
                        {!! Form::select('postsubexpiry-commands',$commands,$condition->getConditionValue('postExpirymail')['condition'],['class'=>'form-control','id'=>'postsubfetching-command']) !!}
                          <div id='postsubfetching-daily-at'>
                            {!! Form::text('postsubexpiry-dailyAt',$condition->getConditionValue('postExpirymail')['at'],['class'=>'form-control time-picker',"placeholder" => "HH:MM"]) !!}

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

                           {!! Form::label('cloud_fetching', Lang::get('Cloud subscription deletion') . ' <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="This cron is to trigger email which are sent out to users after product expiry & on cloud instance deletion. This email is send out to all users using auto renewal or are using manual payment method. For cloud instance only"></i>', [], false) !!}<br>
                            {!! Form::checkbox('cloud_cron',1,$condition->checkActiveJob()['cloud'],['id'=>'cloud_fetching']) !!}&nbsp;{{Lang::get('Enable Faveo Cloud')}}
                        </div>

                    </div>
                         <div class="col-md-6" id="cloud">
                        {!! Form::select('cloud-commands',$commands,$condition->getConditionValue('cloud')['condition'],['class'=>'form-control','id'=>'cloud-command']) !!}
                          <div id='cloud-daily-at'>
                            {!! Form::text('cloud-dailyAt',$condition->getConditionValue('cloud')['at'],['class'=>'form-control time-picker',"placeholder" => "HH:MM"]) !!}
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

                           {!! Form::label('invoice_fetching', Lang::get('Invoice deletion') . ' <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="This cron is to trigger deletion of the old unpaid invoices that are not linked to any orders."></i>', [], false) !!}<br>
                            {!! Form::checkbox('invoice_cron',1,$condition->checkActiveJob()['invoice'],['id'=>'invoice_fetching']) !!}&nbsp;{{Lang::get('Enable Invoice Deletion')}}
                        </div>

                    </div>
                         <div class="col-md-6" id="invoice">
                        {!! Form::select('invoice-commands',$commands,$condition->getConditionValue('invoice')['condition'],['class'=>'form-control','id'=>'invoice-command']) !!}
                          <div id='invoice-daily-at'>
                            {!! Form::text('invoice-dailyAt',$condition->getConditionValue('invoice')['at'],['class'=>'form-control time-picker',"placeholder" => "HH:MM"]) !!}
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

                            {!! Form::label('msg91_report', 'Msg 91 Reports Deletion' . ' <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="top" title="This cron is to trigger deletion of the old Msg91 Reports."></i>', [], false) !!}
                            <br>
                            {!! Form::checkbox('msg91_cron',1,$condition->checkActiveJob()['msg91Reports'],['id'=>'msg91_fetching']) !!}
                            &nbsp;{{Lang::get('message.enable_msg_cron')}}
                        </div>

                    </div>
                    <div class="col-md-6" id="invoice">
                        {!! Form::select('msg91-commands',$commands,$condition->getConditionValue('msg91Reports')['condition'],['class'=>'form-control','id'=>'msg91-command']) !!}
                        <div id='msg91-daily-at'>
                            {!! Form::text('msg91-dailyAt',$condition->getConditionValue('msg91Reports')['at'],['class'=>'form-control time-picker',"placeholder" => "HH:MM"]) !!}
                        </div>
                    </div>


                </div>
            </div><!-- /.info-box-content -->

        </div><!-- /.info-box -->
    </div>
    <h4><button type="submit" class="btn btn-primary pull-right" id="submit" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'>&nbsp;</i> Saving..."><i class="fa fa-sync-alt">&nbsp;&nbsp;</i>{!!Lang::get('message.update')!!}</button></h4>
        </div>
    </div>

 {!! Form::close() !!}



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

        // Toggle daily-at section by command type
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
</script>

