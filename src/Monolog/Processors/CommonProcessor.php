<?php

namespace Uniplaces\Monolog\Processors;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * CommonProcessor
 */
class CommonProcessor
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var string
     */
    protected $appId;

    /**
     * @var string
     */
    protected $gitHash;

    /**
     * @var string
     */
    protected $env;

    /**
     * @param RequestStack $requestStack
     * @param string       $appId
     * @param string       $gitHash
     * @param string       $env
     */
    public function __construct(RequestStack $requestStack, string $appId, string $gitHash, string $env)
    {
        $this->requestStack = $requestStack;
        $this->appId = $appId;
        $this->gitHash = $gitHash;
        $this->env = $env;
    }

    /**
     * @param array $record Each record represents one line entry
     *
     * @return array
     */
    public function processRecord(array $record): array
    {
        $record = $this->buildCommon($record);
        if ($this->hasHttpRequest()) {
            return $this->buildHttpLog($record, $this->requestStack->getCurrentRequest());
        }

        return $this->buildCommandLog($record);
    }

    /**
     * @param array $record
     *
     * @return array
     */
    protected function buildCommon(array $record): array
    {
        $commonFields = [
            'time' => time(),
            'app-id' => $this->appId,
            'env' => $this->env,
            'msg' => $record['message'],
            'level' => strtolower($record['level_name']),
            'git-hash' => $this->gitHash
        ];

        return array_merge($commonFields, $record);
    }

    /**
     * @param array $record
     *
     * @return array
     */
    protected function buildCommandLog(array $record): array
    {
        $extra = [
            'type' => 'command'
        ];

        return array_merge($extra, $record);
    }

    /**
     * @param array   $record
     * @param Request $request
     *
     * @return array
     */
    protected function buildHttpLog(array $record, Request $request): array
    {
        $httpFields = [
            'type' => 'symfony2/http',
            'method' => $request->getMethod(),
            'path' => $request->getUri(),
            'content-type' => $request->getContentType(),
            'client-ip' => $request->getClientIp(),
            'user_agent' => $request->headers->get('User-Agent'),
            'hostname' => $request->getHost(),
            'referrer' => $request->headers->get('referrer')
        ];

        return array_merge($httpFields, $record);
    }

    /**
     * @return bool
     */
    protected function hasHttpRequest(): bool
    {
        return $this->requestStack->getCurrentRequest() instanceof Request;
    }
}
