<?php

namespace App\Console;

use App\Console\Commands\BladeClearCommand;
use App\Services\DataService;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Cache;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        BladeClearCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            app(DataService::class)->fakeDeposit();
        })->when(function () {
            $time = Cache::sear('schedule.fakeDeposit', function () {
                return Carbon::now()->addMinutes(mt_rand(10, 60));
            });
            if (Carbon::now()->greaterThan($time)) {
                Cache::forget('schedule_fakeDeposit');
                return true;
            }
            return false;
        });
        $schedule->call(function () {
            app(DataService::class)->fakePayout();
        })->when(function () {
            $time = Cache::sear('schedule.fakePayout', function () {
                return Carbon::now()->addMinutes(mt_rand(30, 90));
            });
            if (Carbon::now()->greaterThan($time)) {
                Cache::forget('schedule_fakeDeposit');
                return true;
            }
            return false;
        });
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
