<?php

namespace Waynabox\LoggerBundle\Domain;

class WaynaboxLoggerExceptionMessage extends WaynaboxLoggerMessage
{
    public function getLogType(): string
    {
        return 'exception';
    }

    protected function getExtraData(array $record): array
    {
        return $this->reformatExceptionMessage($record);
    }

    public function getMessage(array $record): string
    {
        $chainedExceptions = $this->getChainedExceptions($record);
        $recordStackTrace = $this->buildExceptionStackTrace($chainedExceptions);

        return $recordStackTrace['message'];
    }

    private function reformatExceptionMessage(array $record)
    {
        $chainedExceptions = $this->getChainedExceptions($record);

        $recordStackTrace = $this->buildExceptionStackTrace($chainedExceptions);

        return ['exception_trace' => $recordStackTrace];
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