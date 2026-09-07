<?php

namespace Larva\Fadada\Services;

use FddCloud\client\EUIClient;
use Larva\Fadada\AccessToken;

/**
 * 计费管理（计费链接获取）
 *
 * @see \FddCloud\client\EUIClient
 */
class EUIService extends BaseService
{
    public function __construct(EUIClient $client, AccessToken $accessToken)
    {
        parent::__construct($client, $accessToken);
    }
}
