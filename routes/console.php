<?php

use App\Models\ListeningParty;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function() {
    ListeningParty::where('end_time', '<=', now())->update(['is_active' => false]);
})->everyMinute();
