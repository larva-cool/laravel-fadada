<?php

namespace Larva\LaravelFadada\Services;

use FddCloud\client\ToolServiceClient;
use Larva\LaravelFadada\AccessToken;

/**
 * 工具能力服务（信息比对校验、证照 OCR、个人身份核验）
 *
 * @see \FddCloud\client\ToolServiceClient
 */
class ToolService extends BaseService
{
    public function __construct(ToolServiceClient $client, AccessToken $accessToken)
    {
        parent::__construct($client, $accessToken);
    }
}
