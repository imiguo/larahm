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
            'email' => 'miadmin@gmail.com',
            'status' => 'on',
        ]);
        User::create([
            'name' => 'test',
            'username' => 'test',
            'password' => bcrypt('test'),
            'date_register' => Carbon::now(),
            'email' => 'test@gmail.com',
            'status' => 'on',
        ]);
        User::create([
            'name' => 'hm',
            'username' => 'hm',
            'password' => bcrypt('hm'),
            'date_register' => Carbon::now(),
            'email' => 'hm@gmail.com',
            'status' => 'on',
        ]);

        $file = __DIR__.'/import.sql';
        DB::unprepared(File::get($file));
    }
}
