<?php

namespace App\Exceptions;

/**
 * Class EmptyException
 *
 * @package \App\Exceptions
 */
class EmptyException extends HmException
{
    public function __construct()
    {
        parent::__construct(null);
    }
}
