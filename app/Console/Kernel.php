<?php

namespace App\Console;

use Carbon\Carbon;
use App\Services\DataService;
use App\Console\Commands\HmAdmin;
use Illuminate\Support\Facades\Cache;
use App\Console\Commands\DepositCommand;
use App\Console\Commands\IpCommand;
use App\Console\Commands\BladeClearCommand;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\SmartyClearCommand;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        BladeClearCommand::class,
        SmartyClearCommand::class,
        HmAdmin::class,
        DepositCommand::class,
        IpCommand::class,
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
            if (! config('hm.auto_fake')) {
                return false;
            }
            $time = Cache::sear('schedule.fakeDeposit', function () {
                return Carbon::now()->addMinutes(mt_rand(...explode(',', env('FAKE_DEPOSIT_INTERVAL'))));
            });
            if (Carbon::now()->greaterThan($time)) {
                Cache::forget('schedule.fakeDeposit');

                return true;
            }

            return false;
        });

        $schedule->call(function () {
            app(DataService::class)->fakePayout();
        })->when(function () {
            if (! config('hm.auto_fake')) {
                return false;
            }
            $time = Cache::sear('schedule.fakePayout', function () {
                return Carbon::now()->addMinutes(mt_rand(...explode(',', env('FAKE_PAYOUT_INTERVAL'))));
            });
            if (Carbon::now()->greaterThan($time)) {
                Cache::forget('schedule.fakePayout');

                return true;
            }

            return false;
        });
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
