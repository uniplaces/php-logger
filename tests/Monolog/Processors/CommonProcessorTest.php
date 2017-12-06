<?php

namespace tests\Monolog\Processors;

use Symfony\Component\HttpFoundation\Request;
use phpLogger\Monolog\Processors\CommonProcessor;
use Symfony\Component\HttpFoundation\RequestStack;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * CommonProcessorTest
 */
class CommonProcessorTest extends TestCase
{
    const APP_ID = 'uniplaces-core';
    const GIT_HASH = '1e9461e43de8efake2f73d4ba27G853/503Ac9c0';
    const ENV = 'staging';

    public function testProcessRecordConsole()
    {
        Carbon::setTestNow(Carbon::now());
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn(null);

        /** @var RequestStack $requestStack */
        $processor = new CommonProcessor($requestStack, static::APP_ID, static::GIT_HASH, static::ENV);
        $actualLogEntry = $processor->processRecord(
            [
                'message' => 'some message',
                'channel' => 'app',
                'datetime' => '',
                'level_name' => 'DEBUG',
                'level' => 0
            ]
        );

        $this->assertEquals(
            [
                'type' => 'command',
                'time' => Carbon::now()->toRfc3339String(),
                'app-id' => 'uniplaces-core',
                'msg' => 'some message',
                'git-hash' => '1e9461e43de8efake2f73d4ba27G853/503Ac9c0',
                'level_name' => 'DEBUG',
                'env' => 'staging',
                'level' => 0
            ],
            $actualLogEntry
        );
        Carbon::setTestNow();
    }

    public function testProcessRecordHttp()
    {
        Carbon::setTestNow(Carbon::now());
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack
            ->expects($this->exactly(2))
            ->method('getCurrentRequest')
            ->willReturn(Request::create('/path'));

        /** @var RequestStack $requestStack */
        $processor = new CommonProcessor($requestStack, static::APP_ID, static::GIT_HASH, static::ENV);
        $actualLogEntry = $processor->processRecord(
            [
                'message' => 'some message',
                'channel' => 'app',
                'datetime' => '',
                'level_name' => 'DEBUG',
                'level' => 0
            ]
        );

        $this->assertEquals(
            [
                'type' => 'symfony2/http',
                'method' => 'GET',
                'path' => 'http://localhost/path',
                'content-type' => null,
                'client-ip' => '127.0.0.1',
                'user_agent' => 'Symfony/3.X',
                'hostname' => 'localhost',
                'referrer' => null,
                'time' => Carbon::now()->toRfc3339String(),
                'app-id' => 'uniplaces-core',
                'msg' => 'some message',
                'git-hash' => '1e9461e43de8efake2f73d4ba27G853/503Ac9c0',
                'level_name' => 'DEBUG',
                'env' => 'staging',
                'level' => 0
            ],
            $actualLogEntry
        );
        Carbon::setTestNow();
    }
}
