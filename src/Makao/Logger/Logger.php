<?php

namespace Makao\Logger;

interface Logger
{
    /**
     * @param string $message
     */
    public function log(string $message) : void;
}