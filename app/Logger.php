<?php

namespace App;

use Monolog\Logger as MonologLogger;

class Logger extends MonologLogger{
    /*
    public function addRecord(int $level, string $message, array $context = array())
    {
        dd($level);
        $logsDisabled = false;

        if ($logsDisabled) {
            return false;
        }

        return parent::addRecord($level, $message, $context);
    }
    */
}
