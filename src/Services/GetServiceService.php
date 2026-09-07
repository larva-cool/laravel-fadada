<?php

namespace Larva\Fadada\Services;

use FddCloud\client\ServiceClient;
use Larva\Fadada\AccessToken;

/**
 * 服务访问凭证
 *
 * @method string getAccessToken() 获取服务访问凭证
 * @method string getAppAccessTicket() 获取应用级资源访问凭证
 * @method string getUserAccessTicket() 获取用户级资源访问凭证
 */
class GetServiceService extends TokenlessService
{
    /**
     * @var AccessToken
     */
    protected AccessToken $accessToken;

    public function __construct(ServiceClient $client, AccessToken $accessToken)
    {
        parent::__construct($client);
        $this->accessToken = $accessToken;
    }

    /**
     * 主动刷新并返回 AccessToken。
     */
    public function refreshAccessToken(): string
    {
        $this->accessToken->flush();
        return $this->accessToken->getToken(true);
    }
}
