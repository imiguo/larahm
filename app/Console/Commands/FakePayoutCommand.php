<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FakePayoutCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fake:payout {amount?} {ps?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fake payout';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $amount = $this->argument('amount') ?: 0;
        $ps = $this->argument('ps') ?: 0;
        $result = app('App\Services\DataService')->fakePayout($amount, $ps);
        dump($result);
    }
}
