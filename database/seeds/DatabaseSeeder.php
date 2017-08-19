<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->truncate();
        User::create([
            'name' => 'admin',
            'username' => 'miadmin',
            'password' => bcrypt('miadmin'),
            'date_register' => Carbon::now(),
            'email' => 'midollaradm@gmail.com',
            'status' => 'on',
        ]);
        User::create([
            'name' => 'test',
            'username' => 'test',
            'password' => bcrypt('test'),
            'date_register' => Carbon::now(),
            'email' => '1194316669@qq.com',
            'status' => 'on',
        ]);

        $file = __DIR__.'/import.sql';
        DB::unprepared(File::get($file));
    }
}
