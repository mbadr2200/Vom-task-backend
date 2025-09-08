<?php

use Illuminate\Support\Facades\Schedule;


// Schedule news aggregation to run every minute just for testing purposes
Schedule::command('news:fetch')->everyMinute()->withoutOverlapping();

// Schedule cleanup to run daily at 2 AM
Schedule::command('news:fetch --cleanup')->dailyAt('02:00')->withoutOverlapping();
