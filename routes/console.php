<?php

use App\Model\Common\Setting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('preinstall:check', function () {
    try {
        $check_for_pre_installation = Setting::select('id')->first();
        if ($check_for_pre_installation) {
            throw new \Exception('The data in database already exist. Please provide fresh database', 100);
        }
    } catch (\Exception $ex) {
        if ($ex->getCode() == 100) {
            $this->call('droptables');
        }
        //throw new \Exception($ex->getMessage());
    }
    $this->info('Preinstall has checked successfully');
})->purpose('check for the pre installation');
