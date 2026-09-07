<?php

namespace Larva\LaravelFadada\Services;

use FddCloud\client\OrgClient;
use Larva\LaravelFadada\AccessToken;

/**
 * 组织管理（部门、成员、相对方）
 *
 * @see \FddCloud\client\OrgClient
 */
class OrgService extends BaseService
{
    public function __construct(OrgClient $client, AccessToken $accessToken)
    {
        parent::__construct($client, $accessToken);
    }
}
