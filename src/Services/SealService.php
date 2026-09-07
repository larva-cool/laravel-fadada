<?php

namespace Larva\Fadada\Services;

use FddCloud\client\SealClient;
use Larva\Fadada\AccessToken;

/**
 * 印章管理（企业印章、个人签名）
 *
 * @see \FddCloud\client\SealClient
 */
class SealService extends BaseService
{
    public function __construct(SealClient $client, AccessToken $accessToken)
    {
        parent::__construct($client, $accessToken);
    }
}
