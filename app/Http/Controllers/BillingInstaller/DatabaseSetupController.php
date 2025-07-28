<?php

namespace App\Http\Controllers\BillingInstaller;

use App\Http\Controllers\Controller;
use Config;
use Exception;
use PDO;
use Session;

class DatabaseSetupController extends Controller
{
    public function __construct()
    {
        $default = Session::get('default');
        $host = Session::get('host');
        $username = Session::get('username');
        $password = Session::get('password');
        $databasename = Session::get('databasename');
        $dummy_install = Session::get('dummy_data_installation');
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
     * @param  int  $version  MySQL/MariaDB version as in integer
     * @return bool true if $version satisfies minimum requirement else false
     */
    private function compareMySqlAndMariDB(int $version): bool
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
     * @param  array  $results  variable linked for errors or success messages
     * @param  bool  $mysqli_ok  variable linked for mysql status
     * @param  object  $connection
     * @return void
     *
     * @author Manish Verma <manish.verma@ladybirdweb.com>
     */
    private function checkDBPrerequisites(array &$results, bool &$mysqli_ok, object $connection): void
    {
        if (mysqli_select_db($connection, DB_NAME)) {
            $results[] = new TestResult(\Lang::get('installer_messages.database').' '.DB_NAME.' '.\Lang::get('installer_messages.selected'), STATUS_OK);
            $mysqli_version = mysqli_get_server_info($connection);
            $dbVersion = mysqli_get_server_version($connection);
            if ($this->compareMySqlAndMariDB($dbVersion)) {
                $results[] = new TestResult(\Lang::get('installer_messages.mysql_version_is').' '.$mysqli_version, STATUS_OK);
                $sql = 'SHOW TABLES FROM '.DB_NAME;
                $res = mysqli_query($connection, $sql);
                if (mysqli_fetch_array($res) === null) {
                    $results[] = new TestResult(\Lang::get('installer_messages.database_empty'));
                    $mysqli_ok = true;
                } else {
                    $results[] = new TestResult(\Lang::get('installer_messages.database_not_empty'), STATUS_ERROR);
                    $mysqli_ok = false;
                }
            } else {
                $results[] = new TestResult(\Lang::get('installer_messages.mysql_version_is').' '.$mysqli_version.' '.\Lang::get('installer_messages.mysql_version_required'), STATUS_ERROR);
                $mysqli_ok = false;
            }
        } else {
            echo '<br><br><p id="fail">'.\Lang::get('installer_messages.database_connection_unsuccessful').' '.mysqli_connect_error().'</p>';
            $mysqli_ok = false;
        }
    }

    /**
     * Sets up DB config for testing.
     *
     * @param  string  $dbUsername  mysql username
     * @param  string  $dbPassword  mysql password
     * @return null
     */
    private function setupConfig($host, $dbUsername, $dbPassword, $port = '', $customOptions = [], $dbengine = '')
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
    private function getDBConnection()
    {
        try {
            $connection = mysqli_init();
            mysqli_ssl_set($connection, DB_SSL_KEY, DB_SSL_CERT, DB_SSL_CA, null, null);
            if (DB_PORT != '' && is_numeric(DB_PORT)) {
                $this->setupConfig(DB_HOST, DB_USER, DB_PASS, DB_PORT, [DB_SSL_KEY, DB_SSL_CERT, DB_SSL_CA, DB_SSL_VERIFY_PEER_CERT]);
                if (! mysqli_real_connect($connection, DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT)) {
                    return false;
                }

                return $connection;
            }
            $this->setupConfig(DB_HOST, DB_USER, DB_PASS, '', [DB_SSL_KEY, DB_SSL_CERT, DB_SSL_CA, DB_SSL_VERIFY_PEER_CERT]);
            if (! mysqli_real_connect($connection, DB_HOST, DB_USER, DB_PASS, DB_NAME)) {
                return false;
            }

            return $connection;
        } catch (Exception $e) {
            return false;
        }
    }

    public function testResult()
    {
        if (DB_HOST && DB_USER && DB_NAME) {
            $mysqli_ok = true;
            $results = [];
            // error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE | E_ALL);
            error_reporting(0);
            try {
                if (DB_DEFAULT == 'mysql') {
                    $connection = $this->getDBConnection(); //first attempt assuming db exists
                    if (! $connection) {
                        /**
                         * if connection is not successful that may be because database does not exist so we will
                         * try to create one and reconnect.
                         */
                        createDB(DB_NAME);
                        $connection = $this->getDBConnection(); //second attempt after db creation
                    }

                    if ($connection) {
                        $results[] = new TestResult(\Lang::get('installer_messages.connected_as').' '.DB_USER.'@'.DB_HOST.DB_PORT, STATUS_OK);
                        $this->checkDBPrerequisites($results, $mysqli_ok, $connection);
                    } else {
                        $mysqli_ok = false;
                        $results[] = new TestResult(\Lang::get('installer_messages.failed_connection').' '.mysqli_connect_error(), STATUS_ERROR);
                    }
                }
            } catch (Exception $e) {
                $results[] = new TestResult(\Lang::get('installer_messages.failed_connection').' '.$e->getMessage(), STATUS_ERROR);
                $mysqli_ok = false;
            }
        }

        return ['results' => $results, 'mysqli_ok' => $mysqli_ok];
    }
}
class TestResult
{
    public $message;
    public $status;

    public function __construct($message, $status = STATUS_OK)
    {
        $this->message = $message;
        $this->status = $status;
    }
}
