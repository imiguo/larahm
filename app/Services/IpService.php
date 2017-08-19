<?php

namespace App\Services;

use App\Models\Ip;
use App\Traits\HttpRequest;

class IpService
{
    use HttpRequest;

    public function resolve($ip)
    {
        $longIp = ip2long($ip);
        $country = Ip::where('ip', $longIp)->value('country');
        if (! $country) {
            $country = retry(3, function() use ($ip) {
                return $this->requestInfo($ip);
            });
            Ip::create(['ip' => $longIp, 'country' => $country]);
        }
        return $country;
    }

    public function requestInfo($ip)
    {
        $gateWay = array_random($this->gateWays());
        $url = sprintf($gateWay['url'], $ip);
        $info = $this->get($url);
        return call_user_func($gateWay['callback'], $info);
    }

    protected function gateWays()
    {
        return [
            [
                'url' => 'https://ipapi.co/%s/country/',
                'callback' => function($info) {
                    return $info;
                },
            ],
            [
                'url' => 'http://ip-api.com/json/%s',
                'callback' => function($info) {
                    return $info['countryCode'];
                },
            ],
            [
                'url' => 'http://www.geoplugin.net/json.gp?ip=%s',
                'callback' => function($info) {
                    $info = json_decode($info, true);
                    return $info['geoplugin_countryCode'];
                },
            ],
            [
                'url' => 'https://freegeoip.net/json/%s',
                'callback' => function($info) {
                    return $info['country_code'];
                },
            ],
            [
                'url' => 'http://api.db-ip.com/addrinfo?addr=%s&api_key=bc2ab711d740d7cfa6fcb0ca8822cb327e38844f',
                'callback' => function($info) {
                    return $info['SG'];
                },
            ],
            [
                'url' => 'http://geoip.nekudo.com/api/%s',
                'callback' => function($info) {
                    return $info['country']['code'];
                },
            ],
            [
                'url' => 'https://ipapi.co/%s/json',
                'callback' => function($info) {
                    return $info['country'];
                },
            ],
        ];
    }
}
