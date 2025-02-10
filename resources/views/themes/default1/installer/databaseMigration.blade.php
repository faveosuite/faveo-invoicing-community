@extends('themes.default1.installer.layout.installer')
@section('dbSetup')
    done
@stop

@section('database')
    active
@stop

<script type="text/javascript">
    window.history.forward(1);
</script>

@section('content')
    <div class="card">
        <div class="card-body">

            <p class="text-center lead text-bold">{{trans('installer_messages.database_setup')}}</p>

            <h6 class="mt-1 mb-3">{{trans('installer_messages.installation_check')}}</h6>

            <?php

            $default = Session::get('default');
            $host = Session::get('host');
            $username = Session::get('username');
            $password = Session::get('password');
            $databasename = Session::get('databasename');
//          $dummy_install = Session::get('dummy_data_installation');
            $port = Session::get('port');
            $sslKey = Session::get('db_ssl_key');
            $sslCert = Session::get('db_ssl_cert');
            $sslCa = Session::get('db_ssl_ca');
            $sslVerify = Session::get('db_ssl_verify');
            define('DB_DEFAULT', $default);
            define('DB_HOST', $host); // Address of your MySQL server (usually localhost)
            define('DB_USER', $username); // Username that is used to connect to the server
            define('DB_PASS', $password); // User's password
            define('DB_NAME', $databasename); // Name of the database you are connecting to
            define('DB_PORT', $port); // Name of the database you are connecting to
            define('DB_SSL_KEY', $sslKey);
            define('DB_SSL_CERT', $sslCert);
            define('DB_SSL_CA', $sslCa);
            define('DB_SSL_VERIFY_PEER_CERT', $sslVerify);
            define('PROBE_VERSION', '4.2');
            define('PROBE_FOR', 'HELPDESK 1.0 and Newer');
            define('STATUS_OK', 'Ok');
            define('STATUS_WARNING', 'Warning');
            define('STATUS_ERROR', 'Error');

            class TestResult
            {
                var $message;
                var $status;

                function __construct($message, $status = STATUS_OK)
                {
                    $this->message = $message;
                    $this->status = $status;
                }
            }

            /**
             * Method to check mininmum required version of MySql and MariaDB running on
             * the server.
             * Checking version as an integer value allows us to skip string operations to
             * check if DB is MySQL or MariaDB so we can focus on just to check compatible
             * version of MySQL and MariaDB instead of figuring out what DB server is running.
             * NOTE: This code snippet will work and will not require any modifications until
             *       MySQL releases version 10 which is unlikely to happen in near future.
             *
             * @param int $version MySQL/MariaDB version as in integer
             * @return bool true if $version satisfies minimum requirement else false
             */
            function compareMySqlAndMariDB(int $version): bool
            {
                /**
                 * MySql version less than 5.6 are not compatible so if version is
                 * between 5.6 and 8(including minor and major tags for 8) then we return true.
                 */
                if ($version >= 50600 && $version < 90000) {
                    return true;
                }

                /**
                 * MariaDB had directly released version 10 after 5.5 so if DB server is MariaDB
                 * then we need to check the version must be 10.3 or greater which is compatible
                 * with MySQL 5.6. and 5.7.
                 *
                 * @link https://mariadb.com/kb/en/library/mariadb-vs-mysql-compatibility/
                 * @link https://en.wikipedia.org/wiki/MariaDB
                 */
                if ($version >= 100300) {
                    return true;
                }

                return false;
            }

            /**
             * Method checks prerequisites for database for given mysqli $connection
             * - Checks if connection can access the database
             * - Checks if database version is compatible
             * - Checks if given database is empty or not.
             *
             * @param array $results variable linked for errors or success messages
             * @param bool $mysqli_ok variable linked for mysql status
             * @param object $connection
             * @return void
             *
             * @author Manish Verma <manish.verma@ladybirdweb.com>
             */
            function checkDBPrerequisites(array &$results, bool &$mysqli_ok, object $connection): void
            {
                if (mysqli_select_db($connection, DB_NAME)) {
                    $results[] = new TestResult(\Lang::get('installer_messages.database') . ' ' . DB_NAME . ' ' . \Lang::get('installer_messages.selected'), STATUS_OK);
                    $mysqli_version = mysqli_get_server_info($connection);
                    $dbVersion = mysqli_get_server_version($connection);
                    if (compareMySqlAndMariDB($dbVersion)) {
                        $results[] = new TestResult(\Lang::get('installer_messages.mysql_version_is') . ' ' . $mysqli_version, STATUS_OK);
                        $sql = 'SHOW TABLES FROM ' . DB_NAME;
                        $res = mysqli_query($connection, $sql);
                        if (mysqli_fetch_array($res) === null) {
                            $results[] = new TestResult(\Lang::get('installer_messages.database_empty'));
                            $mysqli_ok = true;
                        } else {
                            $results[] = new TestResult(\Lang::get('installer_messages.database_not_empty'), STATUS_ERROR);
                            $mysqli_ok = false;
                        }
                    } else {
                        $results[] = new TestResult(\Lang::get('installer_messages.mysql_version_is') . ' ' . $mysqli_version . ' ' . \Lang::get('installer_messages.mysql_version_required'), STATUS_ERROR);
                        $mysqli_ok = false;
                    }
                } else {
                    echo '<br><br><p id="fail">' . \Lang::get('installer_messages.database_connection_unsuccessful') . ' ' . mysqli_connect_error() . '</p>';
                    $mysqli_ok = false;
                }
            }

            /**
             * Sets up DB config for testing.
             *
             * @param string $dbUsername mysql username
             * @param string $dbPassword mysql password
             * @return null
             */
            function setupConfig($host, $dbUsername, $dbPassword, $port = '', $customOptions = [], $dbengine = '')
            {
                $options = array_merge([null, null, null, false], $customOptions);
                Config::set('app.env', 'development');
                Config::set('database.connections.mysql.host', $host);
                Config::set('database.connections.mysql.port', $port);
                Config::set('database.connections.mysql.database', null);
                Config::set('database.connections.mysql.username', $dbUsername);
                Config::set('database.connections.mysql.password', $dbPassword);
                Config::set('database.connections.mysql.engine', $dbengine);
                $optionsValue = array_filter([
                    PDO::MYSQL_ATTR_SSL_KEY => $options[0],
                    PDO::MYSQL_ATTR_SSL_CERT => $options[1],
                    PDO::MYSQL_ATTR_SSL_CA => $options[2],
                    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => $options[3],
                ]);
                Config::set('database.connections.mysql.options', $optionsValue);
                Config::set('database.install', 0);
            }

            /**
             * Method attempts database connection after setting connection configurations and
             * returns mysqli connection object.
             *
             * @return object connection object
             */
            function getDBConnection()
            {
                try {
                    $connection = mysqli_init();
                    mysqli_ssl_set($connection, DB_SSL_KEY, DB_SSL_CERT, DB_SSL_CA, null, null);
                    if (DB_PORT != '' && is_numeric(DB_PORT)) {
                        setupConfig(DB_HOST, DB_USER, DB_PASS, DB_PORT, [DB_SSL_KEY, DB_SSL_CERT, DB_SSL_CA, DB_SSL_VERIFY_PEER_CERT]);
                        if (!mysqli_real_connect($connection, DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT)) {
                            return false;
                        }

                        return $connection;
                    }
                    setupConfig(DB_HOST, DB_USER, DB_PASS, '', [DB_SSL_KEY, DB_SSL_CERT, DB_SSL_CA, DB_SSL_VERIFY_PEER_CERT]);
                    if (!mysqli_real_connect($connection, DB_HOST, DB_USER, DB_PASS, DB_NAME)) {
                        return false;
                    }

                    return $connection;
                } catch (Exception $e) {
                    return false;
                }
                return $connection;
            }

            if (DB_HOST && DB_USER && DB_NAME) {
                $mysqli_ok = true;
                $results = [];
                // error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE | E_ALL);
                error_reporting(0);
                try {
                    if (DB_DEFAULT == 'mysql') {
                        $connection = getDBConnection(); //first attempt assuming db exists
                        if (!$connection) {
                            /**
                             * if connection is not successful that may be because database does not exist so we will
                             * try to create one and reconnect.
                             */
                            createDB(DB_NAME);
                            $connection = getDBConnection(); //second attempt after db creation
                        }

                        if ($connection) {
                            $results[] = new TestResult(\Lang::get('installer_messages.connected_as') . ' ' . DB_USER . '@' . DB_HOST . DB_PORT, STATUS_OK);
                            checkDBPrerequisites($results, $mysqli_ok, $connection);
                        } else {
                            $mysqli_ok = false;
                            $results[] = new TestResult(\Lang::get('installer_messages.failed_connection') . ' ' . mysqli_connect_error(), STATUS_ERROR);
                        }
                    }
                } catch (Exception $e) {
                    $results[] = new TestResult(\Lang::get('installer_messages.failed_connection') . ' ' . $e->getMessage(), STATUS_ERROR);
                    $mysqli_ok = false;
                }


                function getIconAndBgClass($status)
                {
                    switch ($status) {
                        case 'Ok':
                            return ['iconClass' => 'fas fa-check', 'bgClass' => 'bg-success'];
                        case 'Error':
                            return ['iconClass' => 'fas fa-times', 'bgClass' => 'bg-danger'];
                        default:
                            return ['iconClass' => 'fas fa-spinner fa-spin', 'bgClass' => 'bg-info'];
                    }
                }

                ?>

            <div class="timeline timeline-inverse" id="timeline">
                    <?php foreach ($results as $result): ?>
                <div>
                    <i class="{{ getIconAndBgClass($result->status)['iconClass'] }} {{ getIconAndBgClass($result->status)['bgClass'] }}"
                       data-toggle="tooltip" title=""></i>
                    <div class="timeline-item">
                        <h3 class="timeline-header border-0">{{$result->message}}</h3>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php }
            else { ?>
            <br/>
            <ul>
                <li><p>{{trans('installer_messages.db_setup_error_7')}}</p></li>
            </ul>
            <p>{{trans('installer_messages.db_setup_error_8')}} <a
                        href="{{ URL::route('db-setup') }}">{{trans('installer_messages.click_here')}}</a> {{trans('installer_messages.continue_installation_process')}}
            </p>
                <?php $mysqli_ok = null; ?>
            <?php } ?>

            <?php if ($mysqli_ok !== null) { ?>
                <?php if ($mysqli_ok) { ?>


            <script src="{{ asset('admin/js/jquery.min.js') }}"></script>
            <script type="text/javascript"
                    src="//cdnjs.cloudflare.com/ajax/libs/jstimezonedetect/1.0.4/jstz.min.js"></script>
            <span id="wait"></span>
            <form id="form">
                @csrf
                {{-- <input type="hidden" name="_token" value="{{ csrf_token() }}"> --}}
                <!-- <b>default</b><br> -->
                <input type="hidden" name="default" value="{!! $default !!}"/>
                <!-- <b>Host</b><br> -->
                <input type="hidden" name="host" value="{!! $host !!}"/>
                <!-- <b>Database Name</b><br> -->
                <input type="hidden" name="databasename" value="{!! $databasename !!}"/>
                <!-- <b>User Name</b><br> -->
                <input type="hidden" name="username" value="{!! $username !!}"/>
                <!-- <b>User Password</b><br> -->
                <input type="hidden" name="password" value="{!! htmlspecialchars($password) !!}"/>
                <!-- <b>Port</b><br> -->
                <input type="hidden" name="port" value="{!! $port !!}"/>
                <input type="hidden" name="db_ssl_key" value="{!! $sslKey !!}"/>
                <input type="hidden" name="db_ssl_cert" value="{!! $sslCert !!}"/>
                <input type="hidden" name="db_ssl_ca" value="{!! $sslCa !!}"/>
                <input type="hidden" name="db_ssl_verify" value="{!! $sslVerify !!}"/>
                <!-- Dummy data installation -->
                {{--    <input type="hidden" name="dummy_install" value="{!! $dummy_install !!}"/>--}}
                <input type="submit" style="display:none;">

            </form>
            <div id="show" style="display:none;">
                <div class="row">
                    <div class="col-md-2">
                    </div>
                    <div class="col-md-9" style="text-align: center" id='loader'>
{{--                        <img src="{{assetLink('image','gifloader')}}"><br/><br/><br/>--}}
{{--                        <img src="{{ asset('installer/img/gifloader.gif') }}"><br/><br/><br/>--}}
                    </div>
                </div>
            </div>

            <br/>

            <script type="text/javascript">
                // submit a ticket
                $(document).ready(function () {
                    $('#submitme').attr('disabled', 'disabled');
                    var tz = jstz.determine(); // Determines the time zone of the browser client
                    var timezone = tz.name(); //'Asia/Kolkata' for Indian Time.
                    $('#tz').val(timezone);
                    submitFormDB();
                    //  event.preventDefault();
                });
                // function getIconAndBgClass(status) {
                //     switch (status) {
                //         case 'Ok':
                //             return { iconClass: 'fas fa-check', bgClass: 'bg-success' };
                //         case 'Error':
                //             return { iconClass: 'fas fa-times', bgClass: 'bg-danger' };
                //         default:
                //             return { iconClass: 'fas fa-spinner fa-spin', bgClass: 'bg-info' };
                //     }
                // }
                // Edit a ticket
                function submitFormDB() {
                    var formData = new FormData($('#form')[0]);
                    $.ajax({
                        type: "POST",
                        url: "{!! url('create/env') !!}",
                        data: formData,
                        dataType: "json",
                        contentType: false,
                        processData: false,
                        beforeSend: function () {
                            $("#conn").hide();
                            $("#previous").hide();
                            // $("#show").show();
                            // $("#wait").show();
                        },
                        success: function (response) {
                            console.log(response)
                            let timelineHtml = '';
                            var tz = jstz.determine(); // Determines the time zone of the browser client
                            var timezone = tz.name(); //'Asia/Kolkata' for Indian Time.
                            var data = response.result;
                            var message = data.success;
                            var next = data.next;
                            var api = data.api;
                            $('#submitme').attr('disabled', 'disabled');
                            timelineHtml += `
                    <div>
                        <i class="fas fa-check bg-success" data-toggle="tooltip" title=""></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header border-0">${response.result.success}</h3>
                        </div>
                    </div>
                `;
                            timelineHtml += `
                    <div>
                        <i class="fas fa-spinner fa-spin bg-info" data-toggle="tooltip" title=""></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header border-0">${response.result.next}</h3>
                        </div>
                    </div>
                `;
                            $('#timeline').append(timelineHtml);
                            callApi(api);
                        },
                        error: function (response) {
                            var data = response.responseJSON.result;
                            $('#wait').append('<ul><li style="color:red">' + data.error + '</li></ul>');
                            $('#loader').hide();
                            $('#next').find('#submitme').hide();
                            $('#retry').append('<input type="button" id="submitm" class="button-primary button button-large button-next" value="Retry" onclick="reload()">');
                            $("#previous").show();

                        }
                    })
                }

                function callApi(api) {
                    $.ajax({
                        type: "GET",
                        url: api,
                        dataType: "json",
                        data: $(this).serialize(),
                        success: function (response) {
                            var data = response.result;
                            var message = data.success;
                            var next = data.next;
                            var api = data.api;

                            const container = document.getElementById('timeline');
                            const existingLoader = container.querySelector('.fa-spinner');

                            if (existingLoader) {
                                existingLoader.parentElement.remove();
                            }

                            timelineHtml = `
                    <div>
                        <i class="fas fa-check bg-success" data-toggle="tooltip" title=""></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header border-0">${message}</h3>
                        </div>
                    </div>
                `;
                            timelineHtml += `
                    <div>
                        <i class="fas fa-spinner fa-spin bg-info" data-toggle="tooltip" title=""></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header border-0">${next}</h3>
                        </div>
                    </div>
                `;
                            $('#timeline').append(timelineHtml);
                            // $("#wait").find('.seco').remove();
                            // $('#wait ul').append('<li>'+message+'</li><li class="seco">'+next+'...</li>');
                            if (message == "{{ trans('installer_messages.database_setup_success') }}") {
                                const existingLoader = container.querySelector('.fa-spinner');

                                if (existingLoader) {
                                    existingLoader.parentElement.remove();
                                }

                                document.getElementById('continue').removeAttribute('disabled');
                            } else {
                                //show message
                                //show next
                                callApi(api);
                            }
                        },
                        error: function (response) {
                            console.log(response);
                            // var data=response.responseJSON.result;
                            // $('.seco').append('<p style="color:red">'+data.error+'</p>');
                            // $('#loader').hide();
                            // $('#next').find('#submitme').hide();
                            // $('#retry').append('<input type="button" id="submitm" class="button-primary button button-large button-next" value="Retry" onclick="reload()">');
                            // $("#previous").show();
                        }
                    });
                }

                function reload() {
                    $('#retry').find('#submitm').remove();
                    $('#loader').show();
                    $('#wait').find('ul').remove();
                    $.ajax({
                        type: "GET",
                        url: "{!! url('create/env') !!}",
                        dataType: "json",
                        data: $('#form').serialize(),
                        beforeSend: function () {
                            $("#conn").hide();
                            // $("#show").show();
                            // $("#wait").show();
                            $("#previous").hide();
                        },
                        success: function (response) {
                            var data = response.result;
                            var message = data.success;
                            var next = data.next;
                            var api = data.api;
                            // $('#submitme').attr('disabled','disabled');
                            // $('#wait').append('<ul><li>'+message+'</li><li class="seco">'+next+'...</li></ul>');
                            callApi(api);
                        },
                        error: function (response) {
                            var data = response.responseJSON.result;
                            $('#wait').append('<ul><li style="color:red">' + data.error + '</li></ul>');
                            $('#loader').hide();
                            $('#next').find('#submitme').hide();
                            $('#retry').append('<input type="button" id="submitm" class="button-primary button button-large button-next" value="Retry" onclick="reload()">');
                            $("#previous").show();

                        }
                    })

                }
                function proceed() {
                    window.location.href = '{{ URL::route('get-start') }}';
                }

            </script>

            <?php } else { ?>


            <p>{{trans('installer_messages.db_setup_error_1')}}</p>
            <ul>
                <li>{{trans('installer_messages.db_setup_error_2')}}</li>
                <li>{{trans('installer_messages.db_setup_error_3')}}</li>
                <li>{{trans('installer_messages.db_setup_error_4')}}</li>
                <li>{{trans('installer_messages.db_setup_error_5')}}</li>
                <li>{{trans('installer_messages.db_setup_error_6')}}</li>
            </ul>
            <p>{{trans('installer_messages.instruction')}} <a href="https://support.faveohelpdesk.com" target="_blank">Support</a>.
            </p>
            <br/><br/>
            <?php } // if  ?>
            <div id="legend">
                {{-- <ul> --}}
                <p class="setup-actions step">
                    <span class="ok text-success">{{trans('installer_messages.ok')}}</span> &mdash; {{trans('installer_messages.all_ok')}} <br/>
                    <span class="warning text-warning">{{trans('installer_messages.warning')}}</span>
                    &mdash; {{trans('installer_messages.instruction_2')}}<br/>
                    <span class="error text-danger">{{trans('installer_messages.error')}}</span>
                    &mdash; {{trans('installer_messages.instruction_3')}}<br/>
                </p>

                {{-- </ul> --}}
            </div>
        </div>
        <div class="card-footer">
            @if(Cache::has('step3'))
                    <?php Cache::forget('step3') ?>
            @endif
            <button class="btn btn-primary" id="previous" onclick="window.location.href='{{ URL::route('db-setup') }}'">
                <i class="fas fa-arrow-left"></i>&nbsp; {{ trans('installer_messages.previous') }}
            </button>

                <button class="btn btn-primary float-right" onclick="proceed('start')" id="continue" disabled>
                    {{ trans('installer_messages.continue') }}&nbsp; <i class="fas {{ app()->getLocale() === 'ar' ? 'fa-arrow-left' : 'fa-arrow-right' }}"></i>
                </button>


        </div>
    </div>
    <?php } ?>

@stop