<?php

namespace Waynabox\LoggerBundle\Domain;

class WaynaboxLoggerBasicLogMessage extends WaynaboxLoggerMessage
{
    public function getLogType()
    {
        return 'log';
    }

    public function processMessage(array $record)
    {
        return $record;
    }
}