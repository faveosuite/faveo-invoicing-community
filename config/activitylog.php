<?php

declare(strict_types=1);

use Spatie\Activitylog\Models\Activity;

return [

    /*
     * If set to false, no activities will be saved to the database.
     */
    'enabled' => env('ACTIVITYLOG_ENABLED', default: true),

    /*
     * Fallback age for activitylog:clean. The scheduler passes the admin's
     * "delete logs after N days" setting as --days, which takes precedence.
     */
    'clean_after_days' => 365,

    /*
     * If no log name is passed to the activity() helper
     * we use this default log name.
     */
    'default_log_name' => 'default',

    /*
     * You can specify an auth driver here that gets user models.
     * If this is null we'll use the default Laravel auth driver.
     */
    'default_auth_driver' => null,

    /*
     * If set to true, the subject returns soft deleted models.
     */
    'include_soft_deleted_subjects' => false,

    /*
     * This model will be used to log activity. The only requirement is that
     * it should be or extend the Spatie\Activitylog\Models\Activity model.
     */
    'activity_model' => Activity::class,

    /*
     * Attributes that are never logged, for every model.
     */
    'default_except_attributes' => [],
];
