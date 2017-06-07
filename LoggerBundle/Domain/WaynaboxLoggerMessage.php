<?php

namespace Waynabox\LoggerBundle\Domain;

abstract class WaynaboxLoggerMessage
{
    abstract function getLogType();

    abstract function processMessage(array $record);

    public function processRecord(array $record)
    {
        $record['log_type'] = $this->getLogType();
        $record = $this->processMessage($record);

        return $record;
    }
}