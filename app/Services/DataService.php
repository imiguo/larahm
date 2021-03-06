<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\History;
use App\Models\FakeUser;
use App\Models\FakeHistory;

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
                    'timestamp' => strtotime($item->created_at),
                ];
            })->toBase();
        $histories = History::select(['user_id', 'amount', 'ec', 'date'])
            ->where('type', 'deposit')
            ->orderBy('date', 'desc')
            ->take(20)
            ->get()
            ->transform(function ($item) {
                return [
                    'username' => $item->user->username ?? 'EmmNisen',
                    'amount' => number_format(abs($item->amount), 2),
                    'payment' => self::paymentMap[$item->ec],
                    'time' => $item->date,
                    'timestamp' => strtotime($item->date),
                ];
            });

        return $fakes->merge($histories->toArray())->take($limit)->sort(function ($a, $b) {
            return $a['timestamp'] > $b['timestamp'];
        });
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
                    'timestamp' => strtotime($item->created_at),
                ];
            })->toBase();
        $histories = History::select(['user_id', 'amount', 'ec', 'date'])
            ->where('type', 'withdrawal')
            ->orderBy('date', 'desc')
            ->take(20)
            ->get()
            ->transform(function ($item) {
                return [
                    'username' => $item->user->username,
                    'amount' => number_format(abs($item->amount), 2),
                    'payment' => self::paymentMap[$item->ec],
                    'time' => $item->date,
                    'timestamp' => strtotime($item->date),
                ];
            });

        return $fakes->union($histories)->take($limit)->sort(function ($a, $b) {
            return $a['timestamp'] > $b['timestamp'];
        });
    }

    public function fakeDeposit($amount = 0, $ps = 0)
    {
        $user = FakeUser::inRandomOrder()->first();
        $history = FakeHistory::create([
            'user_id' => $user->id,
            'amount' => $amount ?: $this->generateAmount(),
            'payment' => $ps ?: ($user->payment ?: $this->generatePayment()),
            'type' => 1,
            'created_at' => Carbon::now()->addSeconds(mt_rand(0, 59)),
        ]);
        $user->payment = $history->payment;
        $user->amount += $history->amount;
        $user->save();

        return $history->toArray();
    }

    public function fakePayout($amount = 0, $ps = 0)
    {
        if (! env('ENBLE_FAKE_PAYOUT')) {
            return;
        }
        $user = FakeUser::where('amount', '>', 0)->inRandomOrder()->first();
        if (! $user) {
            return;
        }
        $amount = $amount ?: retry(10, function () use ($user) {
            $amount = $this->generateAmount();
            if ($amount < $user->amount * 1.5) {
                return $amount;
            }
            throw new Exception('generate amount fail');
        });
        $history = FakeHistory::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'payment' => $ps ?: $user->payment,
            'type' => 2,
            'created_at' => Carbon::now()->addSeconds(mt_rand(0, 59)),
        ]);
        $user->amount = $user->amount - $history->amount;
        $user->save();

        return $history->toArray();
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
