<?php

namespace Larva\LaravelFadada\Services;

use Larva\LaravelFadada\AccessToken;

/**
 * 不需要 accessToken 的业务模块基类
 * （如 GetService / ServiceClient 直接调用获取凭证）
 *
 * 子类直接覆盖构造，传入对应的原 SDK client 即可。
 */
abstract class TokenlessService
{
    /**
     * 原 SDK 的 Client
     */
    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function __call($method, $args)
    {
        if (! method_exists($this->client, $method)) {
            throw new \BadMethodCallException(sprintf(
                'Method [%s] does not exist on %s or its underlying SDK client.',
                $method,
                static::class
            ));
        }

        return $this->client->{$method}(...$args);
    }
}
