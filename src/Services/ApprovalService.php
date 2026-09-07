<?php

namespace Larva\Fadada\Services;

use FddCloud\client\ApprovalClient;
use Larva\Fadada\AccessToken;

/**
 * 审批管理
 *
 * @see \FddCloud\client\ApprovalClient
 */
class ApprovalService extends BaseService
{
    public function __construct(ApprovalClient $client, AccessToken $accessToken)
    {
        parent::__construct($client, $accessToken);
    }
}
