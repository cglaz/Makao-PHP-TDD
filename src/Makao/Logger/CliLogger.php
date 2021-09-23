<?php

namespace Makao\Logger;

/**
 * @codeCoverageIgnore
 */
class CliLogger implements Logger
{
    /**
     * @inheritdoc
     */
    public function log(string $message) : void
    {
        usleep(100000   );
        echo PHP_EOL . $message . PHP_EOL;
    }
}