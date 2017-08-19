<?php

use App\Models\FakeUser;
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
        $now = Carbon::now();
        $users = [
            [
                'name' => 'admin',
                'username' => 'miadmin',
                'password' => bcrypt('miadmin'),
                'date_register' => Carbon::now(),
                'email' => 'miadmin@gmail.com',
                'status' => 'on',
                'created_at' => $now,
                'updated_at' => $now,
            ], [
                'name' => 'test',
                'username' => 'test',
                'password' => bcrypt('test'),
                'date_register' => Carbon::now(),
                'email' => 'test@gmail.com',
                'status' => 'on',
                'created_at' => $now,
                'updated_at' => $now,
            ], [
                'name' => 'hm',
                'username' => 'hm',
                'password' => bcrypt('hm'),
                'date_register' => Carbon::now(),
                'email' => 'hm@gmail.com',
                'status' => 'on',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];
        User::insert($users);

        $fakeUsers = require 'fake_users.php';
        $fakeUsers = collect($fakeUsers)->map(function($fakeUser) use ($now) {
            return [
                'username' => $fakeUser,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();
        FakeUser::insert($fakeUsers);

        $file = __DIR__.'/import.sql';
        DB::unprepared(File::get($file));
    }
}
