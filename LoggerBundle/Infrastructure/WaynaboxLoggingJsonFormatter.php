<?php

namespace Waynabox\LoggerBundle\Infrastructure;

use Monolog\Formatter\JsonFormatter;
use Waynabox\Domain\Common\Exceptions\WaynaboxLoggingFormatter;

class WaynaboxLoggingJsonFormatter extends JsonFormatter implements WaynaboxLoggingFormatter
{

}