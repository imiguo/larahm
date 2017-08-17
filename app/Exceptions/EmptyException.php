<?php

namespace App\Exceptions;

/**
 * Class EmptyException.
 */
class EmptyException extends HmException
{
    public function __construct()
    {
        parent::__construct(null);
    }
}
