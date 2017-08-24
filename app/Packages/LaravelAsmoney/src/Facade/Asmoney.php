<?php

namespace entimm\LaravelPayeer\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @see \entimm\LaravelPayeer\Asmoney
 */
class Asmoney extends Facade
{
    /**
     * Get the binding in the IoC container
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'asmoney';
    }
}
