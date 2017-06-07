<?php

namespace Waynabox\LoggerBundle\Domain;

class WaynaboxLoggerExceptionMessage extends WaynaboxLoggerMessage
{
    public function getLogType()
    {
        return 'exception';
    }

    public function processMessage(array $record)
    {
        $record = $this->reformatExceptionMessage($record);

        return $record;
    }

    private function reformatExceptionMessage(array $record)
    {
        $chainedExceptions = $this->getChainedExceptions($record);

        $recordStackTrace = $this->buildExceptionStackTrace($chainedExceptions);
        $record['exception_trace'] = $recordStackTrace;
        $record['message'] = $recordStackTrace['message'];

        return $record;
    }

    private function getChainedExceptions(array $record)
    {
        return array_reverse(explode("\n\n", $record['message']));
    }

    private function buildExceptionStackTrace(array &$stackTrace): array
    {
        $exceptionStackTrace = array_shift($stackTrace);
        $exceptionStackTrace = explode("\n", $exceptionStackTrace);

        $stackTraceMessage['message'] = $this->cleanExceptionMessage(array_shift($exceptionStackTrace));
        $this->removeStackTraceItem($exceptionStackTrace);
        $stackTraceMessage['stackTrace'] = [];
        $stackTraceMessageIndex = 0;

        while( count( $exceptionStackTrace ) ) {
            $message = explode(' ', array_shift($exceptionStackTrace), 2);

            $stackTraceMessage['stackTrace'][$stackTraceMessageIndex] = $message[1] ?? $message[0];
            $stackTraceMessageIndex++;
        }

        if($this->isAPreviousException($stackTrace)) {
            $stackTraceMessage['previous'] = $this->buildExceptionStackTrace( $stackTrace );
        }

        return $stackTraceMessage;
    }

    private function cleanExceptionMessage($message)
    {
        $stringNextToClean = 'Next ';

        if(strpos($message, $stringNextToClean) !== false) {
            $message = substr($message, strlen($stringNextToClean));
        }

        return $message;
    }

    private function removeStackTraceItem(array &$stackTrace)
    {
        array_shift($stackTrace);
    }

    private function isAPreviousException(array $message): int
    {
        return count($message) > 0;
    }
}