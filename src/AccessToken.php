<?php

namespace Larva\LaravelFadada;

use FddCloud\client\ServiceClient;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * 法大大 AccessToken 管理器
 *
 * - 自动从缓存中读取 / 写入 AccessToken
 * - 缓存过期时通过 ServiceClient 重新申请
 * - 支持手动 flush
 */
class AccessToken
{
    /**
     * @var ServiceClient
     */
    protected $serviceClient;

    /**
     * @var CacheRepository
     */
    protected $cache;

    /**
     * @var array
     */
    protected $config;

    public function __construct(ServiceClient $serviceClient, CacheRepository $cache, array $config)
    {
        $this->serviceClient = $serviceClient;
        $this->cache = $cache;
        $this->config = $config;
    }

    /**
     * 获取 AccessToken（自动缓存与续期）
     *
     * @param bool $forceRefresh 是否强制刷新
     * @return string
     */
    public function getToken(bool $forceRefresh = false): string
    {
        $key = $this->cacheKey();

        if (! $forceRefresh && $this->cache->has($key)) {
            return (string) $this->cache->get($key);
        }

        $response = $this->serviceClient->getAccessToken();
        $payload  = $this->parseResponse($response);

        $token = (string) ($payload['access_token'] ?? $payload['accessToken'] ?? '');

        if ($token === '') {
            throw new \RuntimeException(
                '法大大 AccessToken 申请失败：响应中未找到 access_token。原始响应: ' . $response
            );
        }

        $this->cache->put($key, $token, $this->ttl());

        return $token;
    }

    /**
     * 主动清除已缓存的 AccessToken
     */
    public function flush(): void
    {
        $this->cache->forget($this->cacheKey());
    }

    protected function cacheKey(): string
    {
        return ($this->config['cache_prefix'] ?? 'fadada:access_token:') . ($this->config['app_id'] ?? 'default');
    }

    protected function ttl(): int
    {
        return (int) ($this->config['ttl'] ?? 7000);
    }

    /**
     * 解析 ServiceClient 返回的 JSON 响应。
     * SDK 返回的可能是 JSON 字符串，也可能是错误字符串。
     */
    protected function parseResponse(string $response): array
    {
        $decoded = json_decode($response, true);

        if (is_array($decoded)) {
            // 法大大返回格式示例: {"data":{"accessToken":"xxx","expiresIn":7200}, "code":1, ...}
            if (isset($decoded['data']) && is_array($decoded['data'])) {
                return $decoded['data'];
            }

            return $decoded;
        }

        return [];
    }
}
