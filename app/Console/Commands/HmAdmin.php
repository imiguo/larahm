<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class HmAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hm:admin {name=miadmin} {password=admin8866}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change admin';

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
        $name = $this->argument('name');
        $password = $this->argument('password');
        $user = User::where('id', 1)->first();
        if ($user) {
            $user->username = $name;
            $user->password = bcrypt($password);
            $user->save();
            $this->info('reset admin success');
        } else {
            $now = Carbon::now();
            User::create([
                'name' => 'admin',
                'username' => $name,
                'password' => $password,
                'date_register' => Carbon::now(),
                'email' => 'miadmin@gmail.com',
                'status' => 'on',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->info('change admin success');
        }

    }
}
