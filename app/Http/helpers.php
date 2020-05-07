<?php

use App\Model\Product\ProductUpload;
use Carbon\Carbon;

function checkArray($key, $array)
{
    $value = '';
    if (is_array($array) && array_key_exists($key, $array)) {
        $value = $array[$key];
    }

    return $value;
}

function mime($type)
{
    if ($type == 'jpg' ||
            $type == 'png' ||
            $type == 'jpeg' ||
            $type == 'gif' ||
            starts_with($type, 'image')) {
        return 'image';
    }
}

function isInstall()
{
    $check = false;
    $env = base_path('.env');
    if (\File::exists($env) && env('DB_INSTALL') == 1) {
        $check = true;
    }

    return $check;
}

// For API response
/**
 * Format the error message into json error response.
 *
 * @param string|array $message    Error message
 * @param int          $statusCode
 *
 * @return HTTP json response
 */
function errorResponse($message, $statusCode = 400)
{
    return response()->json(['success' => false, 'message' => $message], $statusCode);
}

/**
 * Format success message/data into json success response.
 *
 * @param string       $message    Success message
 * @param array|string $data       Data of the response
 * @param int          $statusCode
 *
 * @return HTTP json response
 */
function successResponse($message = '', $data = '', $statusCode = 200)
{
    $response = ['success' => true];

    // if message given
    if (! empty($message)) {
        $response['message'] = $message;
    }

    // If data given
    if (! empty($data)) {
        $response['data'] = $data;
    }

    return response()->json($response, $statusCode);
}

/**
 * Gets time in logged in user's timezone.
 * @param string $dateTimeString
 * @param string $format
 * @return string
 */
function getTimeInLoggedInUserTimeZone(string $dateTimeString, $format = 'M j, Y, g:i a')
{
    // caching for 4 seconds so for consecutive queries, it will be readily available. And even if someone updates their
    // timezone, it will start showing the new timezone after 4 seconds
    $timezone = Cache::remember('timezone_'.Auth::user()->id, 5, function () {
        return Auth::user()->timezone->name;
    });

    return ((new DateTime($dateTimeString))->setTimezone(new DateTimeZone($timezone)))->format($format);
}

/**
 * Gets date in a formatted HTML.
 * @param string|null $dateTimeString
 * @return string
 */
function getDateHtml(string $dateTimeString = null)
{
    try {
        if (! $dateTimeString) {
            return '--';
        }

        $date = getTimeInLoggedInUserTimeZone($dateTimeString, 'M j, Y');
        $dateTime = getTimeInLoggedInUserTimeZone($dateTimeString);

        return "<label data-toggle='tooltip' style='font-weight:500;' data-placement='top' title='$dateTime'>
                         $date</label>";

    } catch (Exception $e) {
        return '--';
    }
}


function getExpiryLabel($expiryDate, $badge = 'label')
{
    if ($expiryDate < (new Carbon())->toDateTimeString()) {
        return getDateHtml($expiryDate).'<br>&nbsp;&nbsp;&nbsp;&nbsp;<span class='.'"'.$badge.' '.$badge.'-danger" style="padding-left:4px;" <label data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="Order has Expired">
                         </label>
             <i class="fa fa-exclamation"></i>&nbsp;Expired</span>';
    } else {
        return getDateHtml($expiryDate);
    }
}

function getVersionAndLabel($productVersion, $productId, $badge = 'label')
{
    $latestVersion = \Cache::remember('latest_'.$productId, 10, function () use ($productId) {
        return ProductUpload::where('product_id', $productId)->latest()->value('version');
    });
    if ($productVersion) {
        if ($productVersion < $latestVersion) {
            return '<span class='.'"'.$badge.' '.$badge.'-warning" <label data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="Outdated Version">
                 </label>'.$productVersion.'</span>';
        } else {
            return '<span class='.'"'.$badge.' '.$badge.'-success" <label data-toggle="tooltip" style="font-weight:500;" data-placement="top" title="Latest Version">
                 </label>'.$productVersion.'</span>';
        }
    }

}

