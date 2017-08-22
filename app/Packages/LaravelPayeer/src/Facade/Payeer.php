<?php

namespace entimm\LaravelPayeer\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @see \entimm\LaravelPayeer\Payeer
 */
class Payeer extends Facade
{
    /**
     * Get the binding in the IoC container
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'payeer';
    }
}
