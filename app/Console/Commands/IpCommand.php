<?php

namespace App\Console\Commands;

use App\Models\Ip;
use App\Services\IpService;
use Illuminate\Console\Command;

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
        $ips = Ip::whereNull('country')->orWhere('country', '')->pluck('ip');
        dump($ips);
        foreach ($ips as $ip) {
            app(IpService::class)->requestInfo($ip);
        }
    }
}
