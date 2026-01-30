<?php

use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is for defining custom Artisan commands.
| Scheduled tasks are defined in bootstrap/app.php using withSchedule().
|
| Production crontab entry:
| * * * * * cd /var/www/hqms && php artisan schedule:run >> /dev/null 2>&1
|
*/

Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');
