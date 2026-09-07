<?php

namespace Larva\Fadada\Services;

use FddCloud\client\CorpClient;
use Larva\Fadada\AccessToken;

/**
 * 企业认证授权管理
 *
 * @see \FddCloud\client\CorpClient
 */
class CorpService extends BaseService
{
    public function __construct(CorpClient $client, AccessToken $accessToken)
    {
        parent::__construct($client, $accessToken);
    }
}
