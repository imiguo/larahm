<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\History;
use App\Models\FakeUser;
use App\Models\FakeHistory;
use Exception;

class DataService
{
    const paymentMap = [
        1 => 'pm',
        2 => 'payeer',
        3 => 'btc',
    ];

    public function deposits($limit = 20)
    {
        $fakes = FakeHistory::where('type', 1)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->transform(function ($item) {
                return [
                    'username' => $item->user->username,
                    'amount' => number_format($item->amount, 2),
                    'payment' => self::paymentMap[$item->payment],
                    'time' => $item->created_at,
                ];
            });
        $histories = History::select(['user_id', 'amount', 'ec', 'date'])
            ->where('type', 'deposit')
            ->orderBy('date', 'desc')
            ->take(20)
            ->get()
            ->transform(function ($item) {
                return [
                    'username' => $item->user->username ?? 'EmmNisen',
                    'amount' => $item->amount,
                    'payment' => $item->ec,
                    'time' => $item->date,
                ];
            });

        return $fakes->union($histories)->sortByDesc('time')->take($limit)->sort('time');
    }

    public function payouts($limit = 20)
    {
        $fakes = FakeHistory::where('type', 2)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->transform(function ($item) {
                return [
                    'username' => $item->user->username,
                    'amount' => number_format($item->amount, 2),
                    'payment' => self::paymentMap[$item->payment],
                    'time' => $item->created_at,
                ];
            });
        $histories = History::select(['user_id', 'amount', 'ec', 'date'])
            ->where('type', 'withdrawal')
            ->orderBy('date', 'desc')
            ->take(20)
            ->get()
            ->transform(function ($item) {
                return [
                    'username' => $item->user->username,
                    'amount' => $item->amount,
                    'payment' => $item->ec,
                    'time' => $item->date,
                ];
            });

        return $fakes->union($histories)->sortByDesc('time')->take($limit)->sort('time');
    }

    public function fakeDeposit()
    {
        $user = FakeUser::inRandomOrder()->first();
        $history = FakeHistory::create([
            'user_id' => $user->id,
            'amount' => $this->generateAmount(),
            'payment' => $user->payment ?: $this->generatePayment(),
            'type' => 1,
            'created_at' => Carbon::now(),
        ]);
        $user->payment = $history->payment;
        $user->amount += $history->amount;
        $user->save();
    }

    public function fakePayout()
    {
        if (FakeHistory::where('type', 1)->count() < 30) {
            return;
        }
        $user = FakeUser::where('amount', '>', 0)->inRandomOrder()->first();
        if (! $user) {
            return;
        }
        $amount = retry(10, function () use ($user) {
            $amount = $this->generateAmount();
            if ($amount < $user->amount * 1.5) {
                return $amount;
            }
            throw new Exception('generate amount fail');
        });
        $history = FakeHistory::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'payment' => $user->payment,
            'type' => 2,
            'created_at' => Carbon::now(),
        ]);
        $user->amount = $user->amount - $history->amount;
        $user->save();
    }

    public function generateAmount()
    {
        return collect([
            array_fill(0, 50, 1),
            array_fill(0, 50, 2),
            array_fill(0, 50, 3),
            array_fill(0, 50, 4),
            array_fill(0, 200, 5),
            array_fill(0, 2000, 10),
            array_fill(0, 1000, 20),
            array_fill(0, 200, 50),
            array_fill(0, 100, 100),
            array_fill(0, 20, 500),
            array_fill(0, 10, 1000),
        ])->flatten()->shuffle()->random(mt_rand(1, 10))->sum();
    }

    public function generatePayment()
    {
        return collect([
            array_fill(0, 10, 1),
            array_fill(0, 5, 2),
            array_fill(0, 2, 3),
        ])->flatten()->shuffle()->random();
    }
}
