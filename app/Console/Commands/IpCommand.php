<?php

namespace App\Console\Commands;

use App\Models\Ip;
use Illuminate\Console\Command;
use App\Services\IpService;

class IpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hm:ip';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fix ip info';

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
        $ips = Ip::where('country', '{')->pluck('ip');
        dump($ips);
        foreach ($ips as $ip) {
            app(IpService::class)->requestInfo($ip);
        }
    }
}
