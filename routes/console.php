<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:prune-unpaid-orders')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('app:auto-complete-order-reviews')
    ->everyMinute()
    ->withoutOverlapping();
