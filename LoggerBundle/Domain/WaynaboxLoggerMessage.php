<?php

namespace Waynabox\LoggerBundle\Domain;

abstract class WaynaboxLoggerMessage
{
    abstract public function getLogType(): string;

    abstract public function getMessage(array $record): string ;

    abstract protected function getExtraData(array $record): array;

    public function processRecord(array $record): array
    {
        $extra = $this->getExtraData($record);

        return $extra;
    }
}