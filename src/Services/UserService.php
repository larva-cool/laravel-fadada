<?php

namespace Larva\Fadada\Services;

use FddCloud\client\UserClient;
use Larva\Fadada\AccessToken;

/**
 * 个人用户认证授权管理
 *
 * @see \FddCloud\client\UserClient
 */
class UserService extends BaseService
{
    public function __construct(UserClient $client, AccessToken $accessToken)
    {
        parent::__construct($client, $accessToken);
    }
}
