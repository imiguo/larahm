<?php

namespace App\Services;

use Auth;
use App\Models\Ip;
use App\Traits\HttpRequest;

class IpService
{
    use HttpRequest;

    /**
     * @param $ip
     *
     * @return string
     */
    public function resolveCountry($ip)
    {
        if ($this->isPrivateIp($ip)) {
            return 'private';
        }
        $country = Ip::where('ip', $ip)->value('country');
        if (! $country) {
            $country = $this->requestInfo($ip);
        }

        return $country;
    }

    public function requestInfo($ip)
    {
        $gate = array_random($this->gates());
        $url = sprintf($gate['url'], $ip);
        $info = $this->get($url);
        $country = call_user_func($gate['callback'], $info);

        Ip::updateOrCreate(['ip' => $ip],
            [
                'country' => $country,
                'gate' => $gate['id'],
                'identity' => Auth::check() ? Auth::user()->identity : 0,
            ]
        );

        return $country;
    }

    protected function gates()
    {
        return [
            [
                'id' => 1,
                'url' => 'https://ipapi.co/%s/country/',
                'callback' => function ($info) {
                    return $info != 'Undefined' ? $info : '';
                },
            ],
            [
                'id' => 2,
                'url' => 'http://ip-api.com/json/%s',
                'callback' => function ($info) {
                    return $info['countryCode'];
                },
            ],
            [
                'id' => 3,
                'url' => 'http://www.geoplugin.net/json.gp?ip=%s',
                'callback' => function ($info) {
                    $info = json_decode($info, true);

                    return $info['geoplugin_countryCode'];
                },
            ],
            [
                'id' => 4,
                'url' => 'https://freegeoip.net/json/%s',
                'callback' => function ($info) {
                    return $info['country_code'];
                },
            ],
            [
                'id' => 5,
                'url' => 'http://api.db-ip.com/addrinfo?addr=%s&api_key=bc2ab711d740d7cfa6fcb0ca8822cb327e38844f',
                'callback' => function ($info) {
                    $info = json_decode($info, true);

                    return $info['country'];
                },
            ],
            [
                'id' => 6,
                'url' => 'http://geoip.nekudo.com/api/%s',
                'callback' => function ($info) {
                    return $info['country']['code'];
                },
            ],
            [
                'id' => 7,
                'url' => 'https://ipapi.co/%s/json',
                'callback' => function ($info) {
                    return $info['country'];
                },
            ],
        ];
    }

    protected function isPrivateIp($ip)
    {
        return ! filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }
}
