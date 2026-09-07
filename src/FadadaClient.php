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
     * @var \FddCloud\client\Client
     */
    protected $sdkClient;

    public function __construct(IClient $sdkClient)
    {
        $this->sdkClient = $sdkClient;
    }

    /**
     * {@inheritdoc}
     */
    public function request($accessToken, $bizContent, $path)
    {
        return $this->sdkClient->request($accessToken, $bizContent, $path);
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
    public function getSdkClient(): \FddCloud\client\Client
    {
        return $this->sdkClient;
    }
}
