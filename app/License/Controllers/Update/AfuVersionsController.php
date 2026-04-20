<?php

namespace App\License\Controllers\Update;

use App\Http\Controllers\Controller;
use App\License\Models\Installation;
use App\License\Models\VersionCallback;
use App\License\Requests\VersionRequest;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use FilesystemIterator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use RecursiveIteratorIterator;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

class AfuVersionsController extends Controller
{
    public function __construct()
    {
        if (null !== request()->server('REMOTE_ADDR')) {
            $this->ip_address = request()->server('REMOTE_ADDR');
        } else {
            $this->ip_address = request()->ip();
        }
    }

    /**
     *Add a new version from billing and also update manager.
     *
     * @returns a success response telling you have added the version successfully
     */
    public function versionAdd(VersionRequest $request)
    {
        $action_success = 0; //will be changed to 1 later only if everything OK
        $error_detected = 0; //will be changed to 1 later if error occurs
        $error_details = ''; //will be filled with errors (if any)
        $api_error_detected = 0;
        $api_error_details = '';
        $logged_admin_id = 0;
        //used for compatibility with createReport function in the same file in /aus_admin directory. since admin is not logged in when API is called, $logged_admin_id must be 0
        $this->ensureVersionDirectories();

        $product_id = $request->get('product_id');
        $api_key_secret = $request->get('api_key_secret');
        $version_number = $request->get('version_number');
        $version_status = $request->get('version_status');
        $version_install_file = $request->input('version_install_file');
        $version_install_query = $request->input('version_install_query');
        $version_raw_install_query = $request->get('version_raw_install_query');
        $version_upgrade_file = $request->input('version_upgrade_file');
        $version_upgrade_query = $request->input('version_upgrade_query');
        $version_raw_upgrade_query = $request->get('version_raw_upgrade_query');
        $version_install_limit = $request->get('version_install_limit');
        $version_upgrade_limit = $request->get('version_upgrade_limit');
        $version_changelog = $request->get('version_change_log');
        $version_expire_date = $request->get('version_expire_date');
        $version_comments = $request->get('version_comments');
        $api_action_success = 1;

        if ($api_action_success == '1') { //code between {} tags is identical in files with the same name in /aus_admin and /aus_api directories
            $optional_api_parameters_array = ['version_install_file', 'version_install_query', 'version_raw_install_query', 'version_upgrade_file', 'version_upgrade_query', 'version_raw_upgrade_query', 'version_install_limit', 'version_upgrade_limit', 'version_changelog', 'version_expire_date', 'version_comments']; //optional API parameters for this page
            foreach ($optional_api_parameters_array as $optional_api_parameter) { //in case some required parameter was not submitted, set its value empty to prevent "undefined variable" errors
                if (! isset($$optional_api_parameter)) {
                    $$optional_api_parameter = '';
                }
            }

            if (LicenseHelper::validateIntegerValue($product_id) && ! empty($version_number) && LicenseHelper::validateIntegerValue($version_status, 0, 2)) {
                if (! empty($_FILES['version_install_file']['tmp_name']) && ! validateFile($_FILES['version_install_file']['tmp_name'], $_FILES['version_install_file']['name'], ['application/zip'], ['zip'], 104857600)) {
                    $error_detected = 1;
                    $error_details .= 'Invalid installation archive format or size (ZIP archive, 100 MB max).<br>';
                }

                if (! empty($_FILES['version_upgrade_file']['tmp_name']) && ! validateFile($_FILES['version_upgrade_file']['tmp_name'], $_FILES['version_upgrade_file']['name'], ['application/zip'], ['zip'], 104857600)) {
                    $error_detected = 1;
                    $error_details .= 'Invalid upgrade archive format or size (ZIP archive, 100 MB max).<br>';
                }

                if (! empty($_FILES['version_install_query']['tmp_name']) && ! validateFile($_FILES['version_install_query']['tmp_name'], $_FILES['version_install_query']['name'], ['application/zip'], ['zip'], 1048576)) {
                    $error_detected = 1;
                    $error_details .= 'Invalid installation query format or size (ZIP archive, 1 MB max).<br>';
                }

                if (! empty($_FILES['version_upgrade_query']['tmp_name']) && ! validateFile($_FILES['version_upgrade_query']['tmp_name'], $_FILES['version_upgrade_query']['name'], ['application/zip'], ['zip'], 1048576)) {
                    $error_detected = 1;
                    $error_details .= 'Invalid upgrade query format or size (ZIP archive, 1 MB max).<br>';
                }

                if (! empty($version_install_limit) && ! LicenseHelper::validateIntegerValue($version_install_limit)) {
                    $error_detected = 1;
                    $error_details .= 'Invalid version installations limit.<br>';
                }

                if (! empty($version_upgrade_limit) && ! LicenseHelper::validateIntegerValue($version_upgrade_limit)) {
                    $error_detected = 1;
                    $error_details .= 'Invalid version upgrades limit.<br>';
                }

                if (! empty($version_expire_date) && ! LicenseHelper::verifyDateTime($version_expire_date, 'Y-m-d')) {
                    $error_detected = 1;
                    $error_details .= 'Invalid version expiration date.<br>';
                }

                if ($error_detected != 1) {
                    $version_date = date('Y-m-d');

                    $product_array = Product::where('product_id', $product_id)->get()->toArray(); //fetch product details to be used in file names and reports
                    foreach ($product_array as $product) {
                        extract($product);
                    }

                    if (! empty($_FILES['version_install_file']['tmp_name'])) { //format version_install_file like product-title-version-number-installation-archive-random-string.extension
                        $version_install_file = generateFileName(ARCHIVES_DIRECTORY, slugifyText("$product_title-$version_number-installation-archive-".generateRandomString(8)).'.'.pathinfo($_FILES['version_install_file']['name'], PATHINFO_EXTENSION));
                    } else {
                        $version_install_file = '';
                    }
                    if (! empty($_FILES['version_upgrade_file']['tmp_name'])) { //format version_upgrade_file like product-title-version-number-upgrade-archive-random-string.extension
                        $version_upgrade_file = generateFileName(ARCHIVES_DIRECTORY, slugifyText("$product_title-$version_number-upgrade-archive-".generateRandomString(8)).'.'.pathinfo($_FILES['version_upgrade_file']['name'], PATHINFO_EXTENSION));
                    } else {
                        //$version_upgrade_file="";
                        $version_upgrade_file = $version_upgrade_file;
                    }

                    if (! empty($_FILES['version_install_query']['tmp_name'])) { //format version_install_query like product-title-version-number-install-query-random-string.extension
                        $version_install_query = generateFileName(QUERIES_DIRECTORY, slugifyText("$product_title-$version_number-installation-query-".generateRandomString(8)).'.'.pathinfo($_FILES['version_install_query']['name'], PATHINFO_EXTENSION));
                    } else {
                        $version_install_query = '';
                    }

                    if (! empty($_FILES['version_upgrade_query']['tmp_name'])) { //format version_upgrade_query like product-title-version-number-upgrade-query-random-string.extension
                        $version_upgrade_query = generateFileName(QUERIES_DIRECTORY, slugifyText("$product_title-$version_number-upgrade-query-".generateRandomString(8)).'.'.pathinfo($_FILES['version_upgrade_query']['name'], PATHINFO_EXTENSION));
                    } else {
                        $version_upgrade_query = '';
                    }
                    $added_record = ProductUpload::updateOrCreate(
                        ['version' => $version_number, 'product_id' => $product_id],
                        [
                            'version' => $version_number,
                            'product_id' => $product_id,
                            'title' => $version_number,
                            'description' => $version_changelog,
                            'file' => $version_install_file,
                            'version_expire_date' => $version_expire_date,
                            'version_install_count' => 0,
                            'status' => $version_status,
                        ]
                    );
                    $added_records = empty($added_record) ? 0 : 1;
                    if (! LicenseHelper::validateIntegerValue($added_records)) {
                        $error_detected = 1;
                        $error_details .= 'Invalid record details, duplicated data, or database error.<br>';
                    } else {
                        $action_success = 1;

//                        if (! empty($version_install_file)) { //move uploaded version_install_file
//                            move_uploaded_file($_FILES['version_install_file']['tmp_name'], ARCHIVES_DIRECTORY."/$version_install_file");
//                        }
//
//                        if (! empty($version_upgrade_file)) { //move uploaded version_upgrade_file
//                            move_uploaded_file($_FILES['version_upgrade_file']['tmp_name'], ARCHIVES_DIRECTORY."/$version_upgrade_file");
//                        }
//
//                        if (! empty($version_install_query)) { //move uploaded version_install_query
//                            move_uploaded_file($_FILES['version_install_query']['tmp_name'], QUERIES_DIRECTORY."/$version_install_query");
//                        }
//
//                        if (! empty($version_upgrade_query)) { //move uploaded version_upgrade_query
//                            move_uploaded_file($_FILES['version_upgrade_query']['tmp_name'], QUERIES_DIRECTORY."/$version_upgrade_query");
//                        }

                        $this->disableOldVersion($product_id, $product_max_active_versions, $version_number, $version_comments); //disable a specific number of old versions if needed
                    }
                }
            } else {
                $error_detected = 1;
                $error_details .= 'Invalid product, version number, or status.<br>';
            }

            if ($action_success == 1) { //everything OK
                $page_message = "$product_title version $version_number added.";
            } else { //display error message
                $page_message = "Version could not be added because of this reason: <br><br>$error_details";
            }

            LicenseHelper::logAdminReport(strip_tags($page_message), $logged_admin_id, 1, $action_success);

            /*$optional_api_parameters_array = array("version_install_file", "version_install_query", "version_raw_install_query", "version_upgrade_file", "version_upgrade_query", "version_raw_upgrade_query", "version_install_limit", "version_upgrade_limit", "version_changelog", "version_expire_date", "version_comments"); //optional API parameters for this page
            foreach ($optional_api_parameters_array as $optional_api_parameter) //in case some required parameter was not submitted, set its value empty to prevent "undefined variable" errors
            {
                if (!isset($$optional_api_parameter)) {
                    $$optional_api_parameter = "";
                }
            }

            if (LicenseHelper::validateIntegerValue($product_id) && !empty($version_number) && LicenseHelper::validateIntegerValue($version_status, 0, 2)) {
                $version_file_check = $this->versionFileCheck($version_install_file, $version_upgrade_file, $version_install_query, $version_upgrade_query, $version_install_limit, $version_upgrade_limit, $version_expire_date, $error_detected = 0, $error_details = "");
                extract($version_file_check);
                if (!empty($version_expire_date) && !LicenseHelper::verifyDateTime($version_expire_date, "Y-m-d")) {
                    $error_detected = 1;
                    $error_details .= "Invalid version expiration date.";
                }

                if ($error_detected != 1) {
                    $version_date = date("Y-m-d");
                    $product_array = Product::where('product_id', $product_id)->get()->toArray(); //fetch product details to be used in file names and reports
                    foreach ($product_array as $product) {
                        extract((array)$product);
                    }
                    $temp=$this->generateTempName($version_install_file, $version_upgrade_file, $version_install_query, $version_upgrade_query);
                    $version_file_name_update = $this->formatFile($version_install_file, $version_upgrade_file, $version_install_query, $version_upgrade_query, $product_title, $version_number);
                    extract($version_file_name_update);
                    $added_records = DB::table('afu_versions')->insertOrIgnore([
                        'product_id' => $product_id,
                        'version_number' => $version_number,
                        'version_install_file' => $version_install_file,
                        'version_install_query' => $version_install_file,
                        'version_raw_install_query' => $version_raw_install_query,
                        'version_upgrade_file' => $version_upgrade_file,
                        'version_upgrade_query' => $version_upgrade_query,
                        'version_raw_upgrade_query' => $version_raw_upgrade_query,
                        'version_install_limit' => $version_install_limit,
                        'version_upgrade_limit' => $version_upgrade_limit,
                        'version_changelog' => $version_changelog,
                        'version_date' => $version_date,
                        'version_expire_date' => $version_expire_date,
                        'version_comments' => $version_comments,
                        'version_status' => $version_status
                    ]);

                    if (!LicenseHelper::validateIntegerValue($added_records)) {
                        $error_detected = 1;
                        $error_details .= "Invalid record details, duplicated data, or database error.";

                    } else {
                        $action_success = 1;
                        $this->moveFile($temp,$version_install_file, $version_upgrade_file, $version_install_query, $version_upgrade_query, 1);

                        $this->disableOldVersion($product_id, $product_max_active_versions, $version_number, $version_comments); //disable a specific number of old versions if needed
                    }
                }
            }else {
                $error_detected = 1;
                $error_details .= "Invalid product, version number, or status.";
            }

            if ($action_success == 1) //everything OK
            {
                $page_message = "$product_title version $version_number added.";
            } else //display error message
            {
                $page_message = "Version could not be added because of this reason:$error_details";
            }

            LicenseHelper::logAdminReport(strip_tags($page_message), $logged_admin_id, 1, $action_success);*/
        } else { //display error message
            $page_message = 'The action could not be completed because of this reason: Your api key has failed';
        }

        $api_response_array = ['api_action_success' => $api_action_success, 'api_error_detected' => $api_error_detected, 'action_success' => $action_success, 'error_detected' => $error_detected, 'page_message' => $page_message]; //make array with response data

        return json_encode($api_response_array);
    }

    /**
     *Update an existing version from billing and also update manager.
     *
     * @returns a success response telling you have updated the version successfully
     */
    public function versionUpdate(Request $request)
    {
        $removed_records = 0;
        $action_success = 0; //will be changed to 1 later only if everything OK
        $error_detected = 0; //will be changed to 1 later if error occurs
        $error_details = ''; //will be filled with errors (if any)
        $updated_records = 0;
        $api_error_detected = 0;
        $logged_admin_id = 0; //used for compatibility with createReport function in the same file in /aus_admin directory. since admin is not logged in when API is called, $logged_admin_id must be 0

        $this->ensureVersionDirectories();
        $version_id = $request->get('version_id');
        $product_id = $request->get('product_id');
        $api_key_secret = $request->get('api_key_secret');
        $version_number = $request->get('version_number');
        $version_status = $request->get('version_status');
        $version_install_file = $request->input('version_install_file');
        $version_install_query = $request->input('version_install_query');
        $version_raw_install_query = $request->get('version_raw_install_query');
        $version_upgrade_file = $request->input('version_upgrade_file');
        $version_upgrade_query = $request->input('version_upgrade_query');
        $version_raw_upgrade_query = $request->get('version_raw_upgrade_query');
        $version_install_limit = $request->get('version_install_limit');
        $version_upgrade_limit = $request->get('version_upgrade_limit');
        $version_changelog = $request->get('version_change_log');
        $version_expire_date = $request->get('version_expire_date');
        $version_comments = $request->get('version_comments');
        $product_title = $request->get('product_title');

        if (empty($version_id) || ! LicenseHelper::validateIntegerValue($version_id) || empty($rows_array = ProductUpload::where('id', $version_id)->get()->toArray())) { //invalid record
            return errorResponse(Lang::get('lang.invalid'), 404);
        }
        $api_action_success = 1;
        if ($api_action_success == 1 & $api_error_detected == 0) { //code between {} tags is identical in files with the same name in /aus_admin and /aus_api directories, EXCEPT redirectInvalidRecord($script_name); line
            $optional_api_parameters_array = ['version_install_file', 'version_install_query', 'version_raw_install_query', 'version_upgrade_file', 'version_upgrade_query', 'version_raw_upgrade_query', 'version_install_limit', 'version_upgrade_limit', 'version_changelog', 'version_expire_date', 'version_comments']; //optional API parameters for this page
            foreach ($optional_api_parameters_array as $optional_api_parameter) { //in case some required parameter was not submitted, set its value empty to prevent "undefined variable" errors
                if (! isset($$optional_api_parameter)) {
                    $$optional_api_parameter = '';
                }
            }

            if (! empty($delete_record) && $delete_record == 1) {
                $removed_records += $this->deleteVersion($version_id);
                if ($removed_records > 0) {
                    $action_success = 1;

                    $page_message = "Deleted $removed_records version(s).";
                    LicenseHelper::logAdminReport(strip_tags($page_message), $logged_admin_id, 1, $action_success);
                    echo $page_message; //THIS LINE IS CUSTOM IN API. ADMINISTRATION DASHBOARD CODE CONTAINS redirectInvalidRecord($script_name);
                    exit;
                } else {
                    $error_detected = 1;
                    $error_details .= 'Invalid record or database error.<br>';
                }
            }

            if (LicenseHelper::validateIntegerValue($product_id) && ! empty($version_number) && LicenseHelper::validateIntegerValue($version_status, 0, 2)) {
                if (! empty($_FILES['version_install_file']['tmp_name']) && ! validateFile($_FILES['version_install_file']['tmp_name'], $_FILES['version_install_file']['name'], ['application/zip'], ['zip'], 104857600)) {
                    $error_detected = 1;
                    $error_details .= 'Invalid installation archive format or size (ZIP archive, 100 MB max).<br>';
                }

                if (! empty($_FILES['version_upgrade_file']['tmp_name']) && ! validateFile($_FILES['version_upgrade_file']['tmp_name'], $_FILES['version_upgrade_file']['name'], ['application/zip'], ['zip'], 104857600)) {
                    $error_detected = 1;
                    $error_details .= 'Invalid upgrade archive format or size (ZIP archive, 100 MB max).<br>';
                }

                if (! empty($_FILES['version_install_query']['tmp_name']) && ! validateFile($_FILES['version_install_query']['tmp_name'], $_FILES['version_install_query']['name'], ['application/zip'], ['zip'], 1048576)) {
                    $error_detected = 1;
                    $error_details .= 'Invalid installation query format or size (ZIP archive, 1 MB max).<br>';
                }

                if (! empty($_FILES['version_upgrade_query']['tmp_name']) && ! validateFile($_FILES['version_upgrade_query']['tmp_name'], $_FILES['version_upgrade_query']['name'], ['application/zip'], ['zip'], 1048576)) {
                    $error_detected = 1;
                    $error_details .= 'Invalid upgrade query format or size (ZIP archive, 1 MB max).<br>';
                }

                if (! empty($version_install_limit) && ! LicenseHelper::validateIntegerValue($version_install_limit)) {
                    $error_detected = 1;
                    $error_details .= 'Invalid version installations limit.<br>';
                }

                if (! empty($version_upgrade_limit) && ! LicenseHelper::validateIntegerValue($version_upgrade_limit)) {
                    $error_detected = 1;
                    $error_details .= 'Invalid version upgrades limit.<br>';
                }

                if (! empty($version_expire_date) && ! LicenseHelper::verifyDateTime($version_expire_date, 'Y-m-d')) {
                    $error_detected = 1;
                    $error_details .= 'Invalid version expiration date.<br>';
                }

                if ($error_detected != 1) {
                    $product_array = Product::where('product_id', $product_id)->get()->toArray(); //fetch product details to be used in file names and reports
                    foreach ($product_array as $product) {
                        extract($product);
                    }

                    if (! empty($_FILES['version_install_file']['tmp_name'])) { //format version_install_file like product-title-version-number-installation-archive-random-string.extension
                        $version_install_file = generateFileName(ARCHIVES_DIRECTORY, slugifyText("$product_title-$version_number-installation-archive-".generateRandomString(8)).'.'.pathinfo($_FILES['version_install_file']['name'], PATHINFO_EXTENSION));
                    } else {
                        $version_install_file = '';
                    }

                    if (! empty($_FILES['version_upgrade_file']['tmp_name'])) { //format version_upgrade_file like product-title-version-number-upgrade-archive-random-string.extension
                        $version_upgrade_file = generateFileName(ARCHIVES_DIRECTORY, slugifyText("$product_title-$version_number-upgrade-archive-".generateRandomString(8)).'.'.pathinfo($_FILES['version_upgrade_file']['name'], PATHINFO_EXTENSION));
                    } else {
                        $version_upgrade_file = '';
                    }

                    if (! empty($_FILES['version_install_query']['tmp_name'])) { //format version_install_query like product-title-version-number-install-query-random-string.extension
                        $version_install_query = generateFileName(QUERIES_DIRECTORY, slugifyText("$product_title-$version_number-installation-query-".generateRandomString(8)).'.'.pathinfo($_FILES['version_install_query']['name'], PATHINFO_EXTENSION));
                    } else {
                        $version_install_query = '';
                    }

                    if (! empty($_FILES['version_upgrade_query']['tmp_name'])) { //format version_upgrade_query like product-title-version-number-upgrade-query-random-string.extension
                        $version_upgrade_query = generateFileName(QUERIES_DIRECTORY, slugifyText("$product_title-$version_number-upgrade-query-".generateRandomString(8)).'.'.pathinfo($_FILES['version_upgrade_query']['name'], PATHINFO_EXTENSION));
                    } else {
                        $version_upgrade_query = '';
                    }

                    if (empty($version_install_file)) {
                        $version_install_file = $rows_array[0]['version_install_file']; //use old value when no new version_install_file uploaded
                    }

                    if (empty($version_upgrade_file)) {
                        $version_upgrade_file = $rows_array[0]['version_upgrade_file']; //use old value when no new version_upgrade_file uploaded
                    }

                    if (empty($version_install_query)) {
                        $version_install_query = $rows_array[0]['version_install_query']; //use old value when no new version_install_query uploaded
                    }

                    if (empty($version_upgrade_query)) {
                        $version_upgrade_query = $rows_array[0]['version_upgrade_query']; //use old value when no new version_upgrade_query uploaded
                    }

                    if (! empty($delete_version_install_file) && $delete_version_install_file == 1) {
                        $this->deleteFileDirectory(ARCHIVES_DIRECTORY, [$rows_array[0]['version_install_file']]); //delete old version_install_file (if any)
                        $version_install_file = '';
                    }

                    if (! empty($delete_version_upgrade_file) && $delete_version_upgrade_file == 1) {
                        $this->deleteFileDirectory(ARCHIVES_DIRECTORY, [$rows_array[0]['version_upgrade_file']]); //delete old version_upgrade_file (if any)
                        $version_upgrade_file = '';
                    }

                    if (! empty($delete_version_install_query) && $delete_version_install_query == 1) {
                        $this->deleteFileDirectory(QUERIES_DIRECTORY, [$rows_array[0]['version_install_query']]); //delete old version_install_query (if any)
                        $version_install_query = '';
                    }

                    if (! empty($delete_version_upgrade_query) && $delete_version_upgrade_query == 1) {
                        $this->deleteFileDirectory(QUERIES_DIRECTORY, [$rows_array[0]['version_upgrade_query']]); //delete old version_upgrade_query (if any)
                        $version_upgrade_query = '';
                    }

                    if (! empty($reset_install_count) && $reset_install_count == 1) {
                        $version_install_count = '';
                    } else {
                        $version_install_count = $rows_array[0]['version_install_count']; //use old value when no reset is needed
                    }

                    if (! empty($reset_upgrade_count) && $reset_upgrade_count == 1) {
                        $version_upgrade_count = '';
                    } else {
                        $version_upgrade_count = $rows_array[0]['version_upgrade_count']; //use old value when no reset is needed
                    }
                    $updated_records = ProductUpload::updateOrCreate(
                        ['id' => $version_id],
                        [
                            'file' => $version_install_file,
                            'description' => $version_changelog,
                            'version_install_count' => $version_install_count,
                            'version_expire_date' => $version_expire_date,
                            'status' => $version_status,
                        ]
                    );
                    $updated_records = empty($updated_records) ? 0 : 1;
                    if (! LicenseHelper::validateIntegerValue($updated_records)) {
                        $error_detected = 1;
                        $error_details .= 'Invalid record details, duplicated data, or database error.<br>';
                    } else {
                        $action_success = 1;
//
//                        if (! empty($_FILES['version_install_file']['tmp_name'])) { //move uploaded version_install_file
//                            move_uploaded_file($_FILES['version_install_file']['tmp_name'], ARCHIVES_DIRECTORY."/$version_install_file");
//                            $this->deleteFileDirectory(ARCHIVES_DIRECTORY, [$rows_array[0]['version_install_file']]); //delete old version_install_file (if any)
//                        }
//
//                        if (! empty($_FILES['version_upgrade_file']['tmp_name'])) { //move uploaded version_upgrade_file
//                            move_uploaded_file($_FILES['version_upgrade_file']['tmp_name'], ARCHIVES_DIRECTORY."/$version_upgrade_file");
//                            $this->deleteFileDirectory(ARCHIVES_DIRECTORY, [$rows_array[0]['version_upgrade_file']]); //delete old version_upgrade_file (if any)
//                        }
//
//                        if (! empty($_FILES['version_install_query']['tmp_name'])) { //move uploaded version_install_query
//                            move_uploaded_file($_FILES['version_install_query']['tmp_name'], QUERIES_DIRECTORY."/$version_install_query");
//                            $this->deleteFileDirectory(QUERIES_DIRECTORY, [$rows_array[0]['version_install_query']]); //delete old version_install_query (if any)
//                        }
//
//                        if (! empty($_FILES['version_upgrade_query']['tmp_name'])) { //move uploaded version_upgrade_query
//                            move_uploaded_file($_FILES['version_upgrade_query']['tmp_name'], QUERIES_DIRECTORY."/$version_upgrade_query");
//                            $this->deleteFileDirectory(QUERIES_DIRECTORY, [$rows_array[0]['version_upgrade_query']]); //delete old version_upgrade_query (if any)
//                        }
                    }
                }
            } else {
                $error_detected = 1;
                $error_details .= 'Invalid product, version number or status.<br>';
            }

            if ($action_success == 1) { //everything OK
                $page_message = "$product_title version $version_number updated.";
            } else { //display error message
                $page_message = "Version could not be updated because of this reason: <br><br>$error_details";
            }

            LicenseHelper::logAdminReport(strip_tags($page_message), $logged_admin_id, 1, $action_success);
            /*if (!empty($delete_record) && $delete_record == 1) {
                $removed_records += $this->deleteVersion($version_id);
                if ($removed_records > 0) {
                    $action_success = 1;
                    $page_message = "Deleted $removed_records version(s).";
                    LicenseHelper::logAdminReport(strip_tags($page_message), $logged_admin_id, 1, $action_success);
                    echo $page_message; //THIS LINE IS CUSTOM IN API. ADMINISTRATION DASHBOARD CODE CONTAINS redirectInvalidRecord($script_name);
                    exit();
                } else {
                    $error_detected = 1;
                    $error_details .= "Invalid record or database error.<br>";
                }
            }*/
            /*if (LicenseHelper::validateIntegerValue($product_id) && !empty($version_number) && LicenseHelper::validateIntegerValue($version_status, 0, 2)) {
                $version_file_check=$this->versionFileCheck($version_install_file,$version_upgrade_file,$version_install_query,$version_upgrade_query,$version_install_limit,$version_upgrade_limit,$version_expire_date,$error_detected=0,$error_details="");
                extract($version_file_check);
                if ($error_detected != 1) {
                    $product_array = Product::where('product_id', $product_id)->get()->toArray();//fetchRow("SELECT * FROM aus_products WHERE product_id=?", array($product_id), array("i")); //fetch product details to be used in file names and reports
                    foreach ($product_array as $product) {
                        extract((array)$product);
                    }
                    $temp=$this->generateTempName($version_install_file, $version_upgrade_file, $version_install_query, $version_upgrade_query);
                    $format_file=$this->formatFile($version_install_file,$version_upgrade_file,$version_install_query,$version_upgrade_query,$product_title,$version_number);
                    extract($format_file);
                    if (empty($version_install_file)) {
                        $version_install_file = $rows_array[0]['version_install_file']; //use old value when no new version_install_file uploaded
                    }
                    if (empty($version_upgrade_file)) {
                        $version_upgrade_file = $rows_array[0]['version_upgrade_file']; //use old value when no new version_upgrade_file uploaded
                    }
                    if (empty($version_install_query)) {
                        $version_install_query = $rows_array[0]['version_install_query']; //use old value when no new version_install_query uploaded
                    }

                    if (empty($version_upgrade_query)) {
                        $version_upgrade_query = $rows_array[0]['version_upgrade_query']; //use old value when no new version_upgrade_query uploaded
                    }

                    if (!empty($delete_version_install_file) && $delete_version_install_file == 1) {
                        $this->deleteFileDirectory(ARCHIVES_DIRECTORY, array($rows_array[0]['version_install_file'])); //delete old version_install_file (if any)
                        $version_install_file = "";
                    }

                    if (!empty($delete_version_upgrade_file) && $delete_version_upgrade_file == 1) {
                        $this->deleteFileDirectory(ARCHIVES_DIRECTORY, array($rows_array[0]['version_upgrade_file'])); //delete old version_upgrade_file (if any)
                        $version_upgrade_file = "";
                    }

                    if (!empty($delete_version_install_query) && $delete_version_install_query == 1) {
                        $this->deleteFileDirectory(QUERIES_DIRECTORY, array($rows_array[0]['version_install_query'])); //delete old version_install_query (if any)
                        $version_install_query = "";
                    }

                    if (!empty($delete_version_upgrade_query) && $delete_version_upgrade_query == 1) {
                        $this->deleteFileDirectory(QUERIES_DIRECTORY, array($rows_array[0]['version_upgrade_query'])); //delete old version_upgrade_query (if any)
                        $version_upgrade_query = "";
                    }

                    if (!empty($reset_install_count) && $reset_install_count == 1) {
                        $version_install_count = "";
                    } else {
                        $version_install_count = $rows_array[0]['version_install_count']; //use old value when no reset is needed
                    }

                    if (!empty($reset_upgrade_count) && $reset_upgrade_count == 1) {
                        $version_upgrade_count = "";
                    } else {
                        $version_upgrade_count = $rows_array[0]['version_upgrade_count']; //use old value when no reset is needed
                    }
                    $updated_records += DB::table('afu_versions')->where('version_id', $version_id)
                        ->update([
                            'version_install_file' => $version_install_file,
                            'version_install_query' => $version_install_query,
                            'version_raw_install_query' => $version_raw_install_query,
                            'version_upgrade_file' => $version_upgrade_file,
                            'version_upgrade_query' => $version_upgrade_query,
                            'version_raw_upgrade_query' => $version_raw_upgrade_query,
                            'version_install_limit' => $version_install_limit,
                            'version_install_count' => $version_install_count,
                            'version_upgrade_limit' => $version_upgrade_limit,
                            'version_upgrade_count' => $version_upgrade_count,
                            'version_changelog' => $version_changelog,
                            'version_expire_date' => $version_expire_date,
                            'version_comments' => $version_comments,
                            'version_status' => $version_status
                        ]);
                    if (!LicenseHelper::validateIntegerValue($updated_records)) {
                        $error_detected = 1;
                        $error_details .= "Invalid record details, duplicated data, or database error.";
                    } else {
                        $action_success = 1;
                        $this->moveFile($temp,$version_install_file,$version_upgrade_file,$version_install_query,$version_upgrade_query,0,$rows_array);                        }
                }
            } else {
                $error_detected = 1;
                $error_details .= "Invalid product, version number or status.";
            }

            if ($action_success == 1) //everything OK
            {
                $page_message = "$product_title version $version_number updated.";
                $page_message_class = "alert alert-success";
            } else //display error message
            {
                $page_message = "Version could not be updated because of this reason: $error_details";
                $page_message_class = "alert alert-danger";
            }

            LicenseHelper::logAdminReport(strip_tags($page_message), $logged_admin_id, 1, $action_success);*/
        } else { //display error message
            $page_message = 'The action could not be completed because of this reason: Your api key has failed';
        }

        $api_response_array = ['api_action_success' => $api_action_success, 'api_error_detected' => $api_error_detected, 'action_success' => $action_success, 'error_detected' => $error_detected, 'page_message' => $page_message]; //make array with response data

        return json_encode($api_response_array);
    }

    /**
     *Deletes the version along with it's callbacks and installation details
     * return success if the deletion happened or rollbacks if even one error occurs.
     */
    public function deleteVersion(Request $request)
    {
        $removed_records = 0;
        $version_id = $request->get('version_id');
        $api_key_secret = $request->get('api_key_secret');
        $this->ensureVersionDirectories();
        $api_action_success = 1;

        if (LicenseHelper::validateIntegerValue($version_id) && $api_action_success == 1) {
            if (! empty($rows_array = ProductUpload::where('id', $version_id)->get()->toArray())) { //get file (if any) to remove from server
                foreach ($rows_array as $row) {
                    extract((array) $row);
                    try {
                        DB::beginTransaction();
                        $transaction_errors_array = [];

                        VersionCallback::where('version_id', $version_id)->delete();

                        $removed_records += ProductUpload::where('id', $version_id)->delete();

                        DB::commit();
                    } catch (Exception $e) {
                        $transaction_errors_array[] = $e->getMessage();
                    }
                    if (! empty(array_filter($transaction_errors_array))) { //one of queries failed, revert whole transaction
                        DB::rollBack();
                        $removed_records = 0;

                        return errorResponse(Lang::get('lang.invalid'), 404);
                    } else { //everything ok, delete obsolete files
                        $this->deleteFileDirectory(ARCHIVES_DIRECTORY, array_filter([$version_install_file ?? null, $version_upgrade_file ?? null]));
                        $this->deleteFileDirectory(QUERIES_DIRECTORY, array_filter([$version_install_query ?? null, $version_upgrade_query ?? null]));

                        return successResponse(Lang::get('lang.delete'), $removed_records, 200);
                    }
                }
            }
        }

        return errorResponse(Lang::get('lang.not_found'), 404);
    }

    /**
     * set old versions as expired when new version is added.
     *
     * @param  $product_id
     * @param  $product_max_active_versions
     * @param  $version_number
     * @param  $version_comments
     */
    public function disableOldVersion($product_id, $product_max_active_versions, $version_number, $version_comments)
    {
        if (LicenseHelper::validateIntegerValue($product_id) && LicenseHelper::validateIntegerValue($product_max_active_versions) && ! empty($version_number)) {
            $version_expire_date = date('Y-m-d');
            if (empty($version_comments)) {
                $version_comments = "$product_max_active_versions active versions supported - expired on $version_expire_date after adding version $version_number";
            } else {
                $version_comments .= "($product_max_active_versions active versions supported - expired on $version_expire_date after adding version $version_number)";
            }
            $versionId = DB::select(
                '(SELECT version_id
              FROM (SELECT version_id
                  FROM afu_versions
                  WHERE product_id=? ORDER BY version_id DESC LIMIT ?) temp_table)',
                [$product_id, $product_max_active_versions]);

            $versionId = json_decode(json_encode($versionId), true);

            DB::table('afu_versions')
                  ->whereNotIn('version_id', $versionId)
                  ->where('product_id', $product_id)
                  ->update(['version_expire_date' => $version_expire_date, 'version_comments' => $version_comments]);
        }
    }

    /**
     * delete files and directories from specified directory
     * ($files_array is an array of files and/or sub-directories to be deleted from $root_directory).
     *
     * @param  $root_directory
     * @param  array  $files_array
     */
    public function deleteFileDirectory($root_directory = __DIR__, $files_array = [])
    {
        $removed_records = 0;

        if (is_dir($root_directory)) { //specified directory exists
            if (empty($files_array)) { //get and delete all files from specified directory
                $files_array = scandir($root_directory);
            }

            $files_array = array_filter($files_array); //remove empty files (if any) from $files_array to prevent parent directory from being deleted too
            $files_array = array_diff($files_array, ['.', '..', '']); //remove dot files (if any) from $files_array to prevent parent directory from being deleted too when $files_array contains "."
            $files_array = array_values($files_array); //re-index array to prevent errors of undefined array indices

            if (! empty($files_array)) { //proceed deleting files/directories
                foreach ($files_array as $file) {
                    if (is_file("$root_directory/$file") && unlink("$root_directory/$file")) { //this is a file, delete
                        $removed_records++;
                    }

                    if (is_dir("$root_directory/$file")) { //this is a directory, enter it and delete all files inside first
                        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$root_directory/$file", FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                            $path->isDir() && ! $path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
                        }

                        if (rmdir("$root_directory/$file")) {
                            $removed_records++;
                        }
                    }
                }
            }
        }

        return $removed_records;
    }

    /**
     * checks for the type of the file wheather it's Zip and size is 100 MB max.
     *
     * @param  $version_install_file
     * @param  $version_upgrade_file
     * @param  $version_install_query
     * @param  $version_upgrade_query
     * @param  $version_install_limit
     * @param  $version_upgrade_limit
     * @param  $version_expire_date
     * @param  $error_detected
     * @param  $error_details
     *                        returns an array of error details and error detected =1 when an error occurs
     */
    protected function versionFileCheck($version_install_file, $version_upgrade_file, $version_install_query, $version_upgrade_query, $version_install_limit, $version_upgrade_limit, $version_expire_date, $error_detected = 0, $error_details = '')
    {
        if (! empty($version_install_file)) {
            if (! empty($version_install_file->getLinkTarget()) && ! validateFile($version_install_file->getLinkTarget(), $version_install_file->getClientOriginalName(), ['application/zip'], ['zip'], 104857600)) {
                $error_detected = 1;
                $error_details .= 'Invalid installation archive format or size (ZIP archive, 100 MB max).';
            }
        }
        if (! empty($version_upgrade_file)) {
            if (! empty($version_upgrade_file->getLinkTarget()) && ! validateFile($version_upgrade_file->getLinkTarget(), $version_upgrade_file->getClientOriginalName(), ['application/zip'], ['zip'], 104857600)) {
                $error_detected = 1;
                $error_details .= 'Invalid upgrade archive format or size (ZIP archive, 100 MB max).';
            }
        }
        if (! empty($version_install_query)) {
            if (! empty($version_install_query->getLinkTarget()) && ! validateFile($version_install_query->getLinkTarget(), $version_install_query->getClientOriginalName(), ['application/zip'], ['zip'], 1048576)) {
                $error_detected = 1;
                $error_details .= 'Invalid installation query format or size (ZIP archive, 1 MB max).';
            }
        }
        if (! empty($version_upgrade_query)) {
            if (! empty($version_upgrade_query->getLinkTarget()) && ! validateFile($version_upgrade_query->getLinkTarget(), $version_install_query->getClientOriginalName(), ['application/zip'], ['zip'], 1048576)) {
                $error_detected = 1;
                $error_details .= 'Invalid upgrade query format or size (ZIP archive, 1 MB max).';
            }
        }
        if (! empty($version_install_limit) && ! LicenseHelper::validateIntegerValue($version_install_limit)) {
            $error_detected = 1;
            $error_details .= 'Invalid version installations limit.';
        }

        if (! empty($version_upgrade_limit) && ! LicenseHelper::validateIntegerValue($version_upgrade_limit)) {
            $error_detected = 1;
            $error_details .= 'Invalid version upgrades limit.';
        }

        if (! empty($version_expire_date) && ! LicenseHelper::verifyDateTime($version_expire_date, 'Y-m-d')) {
            $error_detected = 1;
            $error_details .= 'Invalid version expiration date.';
        }

        return ['error_detected' => $error_detected, 'error_details' => $error_details];
    }

    /**
     * retrieves the original name and changes it to custom name.
     *
     * @param  $version_install_file
     * @param  $version_upgrade_file
     * @param  $version_install_query
     * @param  $version_upgrade_query
     * @param  $product_title
     * @param  $version_number
     *                         returns an array of changed file name depending on the action of license manager.
     */
    protected function formatFile($version_install_file, $version_upgrade_file, $version_install_query, $version_upgrade_query, $product_title, $version_number)
    {
        if (! empty($version_install_file)) {
            if (! empty($version_install_file->getLinkTarget())) { //format version_install_file like product-title-version-number-installation-archive-random-string.extension
                $version_install_file = generateFileName(ARCHIVES_DIRECTORY, slugifyText("$product_title-$version_number-installation-archive-".generateRandomString(8)).'.'.pathinfo($version_install_file->getClientOriginalName(), PATHINFO_EXTENSION));
            }
        } else {
            $version_install_file = '';
        }
        if (! empty($version_upgrade_file)) {
            if (! empty($version_upgrade_file->getLinkTarget())) { //format version_upgrade_file like product-title-version-number-upgrade-archive-random-string.extension
                $version_upgrade_file = generateFileName(ARCHIVES_DIRECTORY, slugifyText("$product_title-$version_number-upgrade-archive-".generateRandomString(8)).'.'.pathinfo($version_upgrade_file->getClientOriginalName(), PATHINFO_EXTENSION));
            }
        } else {
            $version_upgrade_file = '';
        }

        if (! empty($version_install_query)) {
            if (! empty($version_install_query->getLinkTarget())) { //format version_install_query like product-title-version-number-install-query-random-string.extension
                $version_install_query = generateFileName(QUERIES_DIRECTORY, slugifyText("$product_title-$version_number-installation-query-".generateRandomString(8)).'.'.pathinfo($version_install_query->getClientOriginalName(), PATHINFO_EXTENSION));
            }
        } else {
            $version_install_query = '';
        }

        if (! empty($version_upgrade_query)) {
            if (! empty($version_upgrade_query->getLinkTarget())) { //format version_upgrade_query like product-title-version-number-upgrade-query-random-string.extension
                $version_upgrade_query = generateFileName(QUERIES_DIRECTORY, slugifyText("$product_title-$version_number-upgrade-query-".generateRandomString(8)).'.'.pathinfo($version_upgrade_query->getClientOriginalName(), PATHINFO_EXTENSION));
            }
        } else {
            $version_upgrade_query = '';
        }

        return ['version_install_file' => $version_install_file, 'version_upgrade_file' => $version_upgrade_file,
            'version_install_query' => $version_install_query, 'version_upgrade_query' => $version_upgrade_query, ];
    }

    /**
     * This helps move the file to specific custom name and specified location in updatemanager
     * And deletes the existing one if add = 0, that means during update.
     *
     * @param  $temp
     * @param  $version_install_file
     * @param  $version_upgrade_file
     * @param  $version_install_query
     * @param  $version_upgrade_query
     * @param  $add
     * @param  $rows_array
     */
    protected function moveFile($temp, $version_install_file, $version_upgrade_file, $version_install_query, $version_upgrade_query, $add, $rows_array = null)
    {
        extract($temp);
        if ($add) {
            if (! empty($version_install_file)) { //move uploaded version_install_file
                move_uploaded_file($versionInstallTempName, ARCHIVES_DIRECTORY."/$version_install_file");
            }

            if (! empty($version_upgrade_file)) { //move uploaded version_upgrade_file
                move_uploaded_file($versionUpgradeTempName, ARCHIVES_DIRECTORY."/$version_upgrade_file");
            }

            if (! empty($version_install_query)) { //move uploaded version_install_query
                move_uploaded_file($versionInstallQueryTempName, QUERIES_DIRECTORY."/$version_install_query");
            }

            if (! empty($version_upgrade_query)) { //move uploaded version_upgrade_query
                move_uploaded_file($versionUpgradeQueryTempName, QUERIES_DIRECTORY."/$version_upgrade_query");
            }
        } else {
            if (! empty($version_install_file)) {
                if (! empty($versionInstallTempName)) { //move uploaded version_install_file
                    move_uploaded_file($versionInstallTempName, ARCHIVES_DIRECTORY."/$version_install_file");
                    $this->deleteFileDirectory(ARCHIVES_DIRECTORY, [$rows_array[0]['version_install_file']]); //delete old version_install_file (if any)
                }
            }
            if (! empty($version_upgrade_file)) {
                if (! empty($versionUpgradeTempName)) { //move uploaded version_upgrade_file
                    move_uploaded_file($versionUpgradeTempName, ARCHIVES_DIRECTORY."/$version_upgrade_file");
                    $this->deleteFileDirectory(ARCHIVES_DIRECTORY, [$rows_array[0]['version_upgrade_file']]); //delete old version_upgrade_file (if any)
                }
            }
            if (! empty($version_install_query)) {
                if (! empty($versionInstallQueryTempName)) { //move uploaded version_install_query
                    move_uploaded_file($versionInstallQueryTempName, QUERIES_DIRECTORY."/$version_install_query");
                    $this->deleteFileDirectory(QUERIES_DIRECTORY, [$rows_array[0]['version_install_query']]); //delete old version_install_query (if any)
                }
            }
            if (! empty($version_upgrade_query)) {
                if (! empty($versionUpgradeQueryTempName)) { //move uploaded version_upgrade_query
                    move_uploaded_file($versionUpgradeQueryTempName, QUERIES_DIRECTORY."/$version_upgrade_query");
                    $this->deleteFileDirectory(QUERIES_DIRECTORY, [$rows_array[0]['version_upgrade_query']]); //delete old version_upgrade_query (if any)
                }
            }
        }
    }

    /**
     *Helps generate and store temp name of files uploaded as zip.
     *
     * @param  $version_install_file
     * @param  $version_upgrade_file
     * @param  $version_install_query
     * @param  $version_upgrade_query
     *                                return array of temp names
     */
    protected function generateTempName($version_install_file, $version_upgrade_file, $version_install_query, $version_upgrade_query)
    {
        $versionInstallTempName = '';
        $versionUpgradeTempName = '';
        $versionInstallQueryTempName = '';
        $versionUpgradeQueryTempName = '';

        if (! empty($version_install_file)) {
            $versionInstallTempName = $version_install_file->getLinkTarget();
        }
        if (! empty($version_upgrade_file)) {
            $versionUpgradeTempName = $version_upgrade_file->getLinkTarget();
        }
        if (! empty($version_install_query)) {
            $versionInstallQueryTempName = $version_install_query->getLinkTarget();
        }
        if (! empty($version_upgrade_query)) {
            $versionUpgradeQueryTempName = $version_upgrade_query->getLinkTarget();
        }

        return ['versionInstallTempName' => $versionInstallTempName, 'versionUpgradeTempName' => $versionUpgradeTempName,
            'versionInstallQueryTempName' => $versionInstallQueryTempName, 'versionUpgradeQueryTempName' => $versionUpgradeQueryTempName, ];
    }

    private function ensureVersionDirectories(): void
    {
        $archives = storage_path('app/license/archives');
        $queries = storage_path('app/license/queries');
        if (! is_dir($archives)) {
            @mkdir($archives, 0755, true);
        }
        if (! is_dir($queries)) {
            @mkdir($queries, 0755, true);
        }
        if (! defined('SCRIPT_ROOT_DIRECTORY')) {
            define('SCRIPT_ROOT_DIRECTORY', __DIR__);
        }
        if (! defined('ARCHIVES_DIRECTORY')) {
            define('ARCHIVES_DIRECTORY', $archives);
        }
        if (! defined('QUERIES_DIRECTORY')) {
            define('QUERIES_DIRECTORY', $queries);
        }
    }
}
