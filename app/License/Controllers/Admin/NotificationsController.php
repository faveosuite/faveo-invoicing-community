<?php

namespace App\License\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\License\Models\LicenseNotification;
use App\License\Models\VersionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class NotificationsController extends Controller
{
    /**
     * Get license notifications.
     */
    public function showLicenseNotifications()
    {
        return successResponse('', LicenseNotification::first());
    }

    /**
     * Update license notifications.
     */
    public function updateLicenseNotifications(Request $request, $notification_id)
    {
        $fields = (new LicenseNotification)->getFillable();

        $validated = $request->validate(
            array_fill_keys($fields, 'required|string|max:250')
        );

        $notification = LicenseNotification::find($notification_id) ?? LicenseNotification::first();

        if ($notification) {
            $notification->update($validated);
        } else {
            $notification = LicenseNotification::create($validated);
        }

        return successResponse(Lang::get('license::lang.notifications_updated'), $notification);
    }

    /**
     * Get version/update notifications.
     */
    public function showUpdateNotifications()
    {
        return successResponse('', VersionNotification::first());
    }

    /**
     * Update version/update notifications.
     */
    public function updateUpdateNotifications(Request $request, $notification_id)
    {
        $fields = (new VersionNotification)->getFillable();

        $validated = $request->validate(
            array_fill_keys($fields, 'required|string|max:250')
        );

        $notification = VersionNotification::find($notification_id) ?? VersionNotification::first();

        if ($notification) {
            $notification->update($validated);
        } else {
            $notification = VersionNotification::create($validated);
        }

        return successResponse(Lang::get('license::lang.notifications_updated'), $notification);
    }
}
