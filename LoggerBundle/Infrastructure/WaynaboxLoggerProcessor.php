<?php

namespace Waynabox\LoggerBundle\Infrastructure;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Waynabox\LoggerBundle\Domain\WaynaboxLoggerMessage;

class WaynaboxLoggerProcessor
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var string
     */
    private $token;

    /**
     * @var RequestStack
     */
    private $request;

    /**
     * @var array
     */
    private $requestInformation;

    /**
     * @var WaynaboxLoggerMessage
     */
    private $loggerMessage;

    public function __construct(Session $session, RequestStack $request, WaynaboxLoggerMessage $loggerMessage)
    {
        $this->session = $session;
        $this->request = $request;
        $this->loggerMessage = $loggerMessage;
    }

    public function processRecord(array $record)
    {
        $this->initializeRequestData();

        $processedRecord = $record;

        $processedRecord['message'] = $this->loggerMessage->getMessage($record);
        $processedRecord['log_type'] = $this->loggerMessage->getLogType();
        $processedRecord['extra'] = $this->loggerMessage->processRecord($record);

        $processedRecord['request_id']['token'] = $this->token;
        $processedRecord['request_id']['session_id'] = $this->sessionId;
        $processedRecord['request_information'] = $this->requestInformation;

        $this->isConsoleCommand() ? $processedRecord['request_type'] = 'command' : $processedRecord['request_type'] = 'http';

        return $processedRecord;
    }

    private function initializeRequestData()
    {
        global $kernel;
        if($this->isTheFirstExceptionInThisExecution()) {
            $this->sessionId = $this->getExecutionSessionId();
            $this->token .= substr($this->sessionId, 0, 8) . '-' . $this->session->getMetadataBag()->getLastUsed();

            if($this->isConsoleCommand()) {
                $this->requestInformation = $this->buildConsoleRequestInformation();
            } else {
                $this->requestInformation = $this->buildHttpRequestInformation();
            }

            $this->requestInformation['environment'] = $kernel->getEnvironment();
            $this->requestInformation['hostname'] = $kernel->getContainer()->getParameter('hostname');
        }
    }

    private function isTheFirstExceptionInThisExecution()
    {
        return null === $this->token;
    }

    private function isConsoleCommand()
    {
        return 'cli' === php_sapi_name();
    }

    private function getExecutionSessionId()
    {
        if ($this->isConsoleCommand()) {
            $sessionId = getmypid();
        } else {
            try {
                $this->session->start();
                $sessionId = $this->session->getId();
            } catch (\RuntimeException $e) {
                $sessionId = '????????';
            }
        }

        return $sessionId;
    }

    private function buildConsoleRequestInformation()
    {
        global $input;

        $commandArguments = $input->getArguments();
        $requestInformation = [];

        foreach ($commandArguments as $argumentName => $argument) {
            $requestInformation[$argumentName] = $argument;
        }

        return $requestInformation;
    }

    private function buildHttpRequestInformation()
    {
        $requestInformation = [
            'request' => $this->request->getCurrentRequest()->getUri(),
            'verb' => $this->request->getCurrentRequest()->getMethod(),
            'agent' => $this->request->getCurrentRequest()->server->get('HTTP_USER_AGENT'),
            'referer' => $this->request->getCurrentRequest()->headers->get('referer'),
            'fwd_for' => $this->request->getCurrentRequest()->headers->get('HTTP_X_FORWARDED_FOR'),
            'client_ip' => $this->request->getCurrentRequest()->getClientIps(),
            'locale' => $this->request->getCurrentRequest()->getLocale()
        ];

        $requestInformation['parameters'] = [];
        $parameters = $this->request->getCurrentRequest()->request->all();

        foreach ($parameters as $parameterName => $parameter) {
            $requestInformation['parameters'][$parameterName] = $parameter;
        }

        return $requestInformation;
    }
}