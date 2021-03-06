<?php declare(strict_types=1);

namespace WyriHaximus\Tests\PSR3\Filter;

use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\LoggerInterfaceTest;
use WyriHaximus\PSR3\Filter\LogLevelFilterLogger;
use function WyriHaximus\PSR3\checkCorrectLogLevel;
use function WyriHaximus\PSR3\processPlaceHolders;

final class LogLevelFilterLoggerTest extends LoggerInterfaceTest
{
    /**
     * @var array
     */
    public $logs = [];

    public function getLogger()
    {
        $that = $this;
        $logger = $this->prophesize(LoggerInterface::class);
        $logger->log(
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->will(function ($args) use ($that) {
            list($level, $message, $context) = $args;
            $message = (string)$message;
            checkCorrectLogLevel($level);
            $message = processPlaceHolders($message, $context);
            $that->logs[] = $level . ' ' . $message;

            return true;
        });

        return new LogLevelFilterLogger([], $logger->reveal());
    }

    public function getLogs()
    {
        return $this->logs;
    }

    public function testFilter()
    {
        $logger = $this->prophesize(LoggerInterface::class);
        $logger->log('info', 'faa bor', [])->shouldBeCalled();

        $filter = new LogLevelFilterLogger(['error'], $logger->reveal());
        $filter->log('error', 'fOo');
        $filter->log('error', 'bar');
        $filter->log('info', 'faa bor');
    }
}
