<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DepositCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hm:deposit {username} {amount} {ago=0} {plan_id=1} {ps=1} {batch?} {account?} {compound=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add deposit';

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
        $username = $this->argument('username');
        $amount = $this->argument('amount');
        $plan_id = $this->argument('plan_id');
        $ps = $this->argument('ps');
        $batch = $this->argument('batch') ?: time().mt_rand(00, 99);
        $account = $this->argument('account') ?: app('Faker\Generator')->name;
        $ago = $this->argument('ago');
        $compound = $this->argument('compound');
        $userId = User::where('username', $username)->value('id');
        if (! $userId) {
            $this->info('找不到这个用户');
        }
        $ret = add_deposit($ps, $userId, $amount, $batch, $account, $plan_id, $compound, $ago);
        if ($ret) {
            dump(compact('ps', 'userId', 'amount', 'batch', 'account', 'plan_id', 'compound', 'ago'));
            return;
        }
        $this->info('the deposit not success');
    }
}