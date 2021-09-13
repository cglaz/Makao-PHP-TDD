<?php

namespace Makao\Exception;

use Throwable;

class GameException extends \RuntimeException
{
    public function __construct($message = "", Throwable $previous = null)
    {
        if (!is_null($previous)) {
            $message .= ' Issue: ' . $previous->getMessage();
        }
        parent::__construct($message, 0, $previous);
    }
}