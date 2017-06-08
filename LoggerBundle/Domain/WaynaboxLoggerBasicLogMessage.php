<?php

namespace Waynabox\LoggerBundle\Domain;

class WaynaboxLoggerBasicLogMessage extends WaynaboxLoggerMessage
{
    public function getLogType(): string
    {
        return 'log';
    }

    public function getExtraData(array $record): array
    {
        return [];
    }

    public function getMessage(array $record): string
    {
        return $record['message'];
    }
}