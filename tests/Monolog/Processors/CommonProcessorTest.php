<?php

namespace tests\Monolog\Processors;

use Symfony\Component\HttpFoundation\Request;
use Uniplaces\Monolog\Processors\CommonProcessor;
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

        $this->assertEquals('command', $actualLogEntry['type']);
        $this->assertEquals('uniplaces-core', $actualLogEntry['app-id']);
        $this->assertEquals('some message', $actualLogEntry['msg']);
        $this->assertEquals('1e9461e43de8efake2f73d4ba27G853/503Ac9c0', $actualLogEntry['git-hash']);
        $this->assertEquals('DEBUG', $actualLogEntry['level_name']);
        $this->assertEquals('staging', $actualLogEntry['env']);
        $this->assertEquals(0, $actualLogEntry['level']);
        $this->assertArrayHasKey('time', $actualLogEntry);
    }

    public function testProcessRecordHttp()
    {
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

        $this->assertEquals('symfony2/http', $actualLogEntry['type']);
        $this->assertEquals('GET', $actualLogEntry['method']);
        $this->assertEquals('http://localhost/path', $actualLogEntry['path']);
        $this->assertEquals(null, $actualLogEntry['content-type']);
        $this->assertEquals('127.0.0.1', $actualLogEntry['client-ip']);
        $this->assertEquals('Symfony/3.X', $actualLogEntry['user_agent']);
        $this->assertEquals('localhost', $actualLogEntry['hostname']);
        $this->assertEquals(null, $actualLogEntry['referrer']);
        $this->assertEquals('uniplaces-core', $actualLogEntry['app-id']);
        $this->assertEquals('some message', $actualLogEntry['msg']);
        $this->assertEquals('1e9461e43de8efake2f73d4ba27G853/503Ac9c0', $actualLogEntry['git-hash']);
        $this->assertEquals('DEBUG', $actualLogEntry['level_name']);
        $this->assertEquals('staging', $actualLogEntry['env']);
        $this->assertEquals(0, $actualLogEntry['level']);
        $this->assertArrayHasKey('time', $actualLogEntry);
    }
}
