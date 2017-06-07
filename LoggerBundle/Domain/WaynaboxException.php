<?php

namespace Waynabox\LoggerBundle\Domain;

class WaynaboxException extends \Exception
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->logException();
    }

    /**
     * Logs the exception
     */
    private function logException()
    {
        global $kernel;

        /** @var WaynaboxLoggerExceptionMessage $logger */
        $logger = $kernel->getContainer()->get('waynabox_exception_logger');

        $logger->info($this);
    }
}