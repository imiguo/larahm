<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\FakeUser;
use Illuminate\Database\Seeder;

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
                'id' => 1,
                'name' => 'admin',
                'username' => 'miadmin',
                'password' => bcrypt('miadmin'),
                'date_register' => Carbon::now(),
                'email' => 'miadmin@gmail.com',
                'status' => 'on',
                'identity' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ], [
                'id' => 2,
                'name' => 'test',
                'username' => 'test',
                'password' => bcrypt('test'),
                'date_register' => Carbon::now(),
                'email' => 'test@gmail.com',
                'status' => 'on',
                'identity' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ], [
                'id' => 3,
                'name' => 'monitor',
                'username' => 'monitor',
                'password' => bcrypt('monitor'),
                'date_register' => Carbon::now(),
                'email' => 'monitor@gmail.com',
                'status' => 'on',
                'identity' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ], [
                'id' => 4,
                'name' => 'empty',
                'username' => 'empty',
                'password' => bcrypt('empty'),
                'date_register' => Carbon::now(),
                'email' => 'empty@gmail.com',
                'status' => 'on',
                'identity' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        User::insert($users);
        $user = User::where(['id' => 2])->first();
        $user->payeer_account = config('ps.test_account.payeer');
        $user->perfectmoney_account = config('ps.test_account.perfectmoney');
        $user->save();

        Artisan::call('hm:deposit', [
            'username' => 'test',
            'amount' => 1000,
            '--ago' => 5,
            '--ps' => 1,
        ]);
        Artisan::call('hm:deposit', [
            'username' => 'test',
            'amount' => 1000,
            '--ago' => 5,
            '--ps' => 2,
        ]);

        $fakeUsers = require 'fake_users.php';
        $fakeUsers = collect($fakeUsers)->map(function ($fakeUser) use ($now) {
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
