<?php

namespace entimm\LaravelPerfectMoney\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @see \entimm\LaravelPerfectMoney\PerfectMoney
 */
class PerfectMoney extends Facade
{
    /**
     * Get the binding in the IoC container
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'perfectmoney';
    }
}