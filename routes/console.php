<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('charges:generate-monthly')
    ->monthlyOn(1, '03:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/charges-generate.log'));

Schedule::command('expenses:generate-recurring')
    ->monthlyOn(1, '04:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/expenses-generate.log'));
