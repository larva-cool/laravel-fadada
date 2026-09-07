<?php

namespace Larva\Fadada;

use FddCloud\client\IClient;

/**
 * 对原 FddCloud\client\Client 的轻量封装
 *
 * 业务 Service 通过容器拿到本类，由本类再委托给原 SDK Client。
 * 之所以不直接继承，是因为原 Client 的构造函数签名比较特殊，
 * 单独包装一层便于从 Laravel config 装配。
 */
class FadadaClient implements IClient
{
    /**
     * @var IClient
     */
    protected IClient $sdkClient;

    public function __construct(IClient $sdkClient)
    {
        $this->sdkClient = $sdkClient;
    }

    /**
     * {@inheritdoc}
     */
    public function request($accessToken, $bizContent, $path)
    {
        $result = $this->sdkClient->request($accessToken, $bizContent, $path);

        // 原 SDK 返回的是未解码的响应体字符串。
        if (! is_string($result)) {
            return $result;
        }

        $decoded = json_decode($result, true);

        // 非合法 JSON（如文件流等）原样返回，保持向后兼容。
        if (! is_array($decoded)) {
            return $result;
        }

        // 法大大成功码为 100000，成功时直接返回解析后的 data，
        // 业务层无需再关心 code/msg 外壳。失败则返回完整响应供调用方判断。
        if ((int) ($decoded['code'] ?? 0) === 100000) {
            return $decoded['data'] ?? null;
        }

        return $decoded;
    }

    /**
     * {@inheritdoc}
     */
    public function request_file($url, $filePath)
    {
        return $this->sdkClient->request_file($url, $filePath);
    }

    /**
     * {@inheritdoc}
     */
    public function downLoad_request($accessToken, $bizContent, $path)
    {
        return $this->sdkClient->downLoad_request($accessToken, $bizContent, $path);
    }

    /**
     * 暴露底层 SDK Client 以备高级场景使用。
     */
    public function getSdkClient(): IClient
    {
        return $this->sdkClient;
    }
}
