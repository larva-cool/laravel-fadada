<?php

namespace Larva\Fadada\Services;

use FddCloud\client\AppTemplateClient;
use Larva\Fadada\AccessToken;

/**
 * 应用模板管理
 *
 * @see \FddCloud\client\AppTemplateClient
 */
class AppTemplateService extends BaseService
{
    public function __construct(AppTemplateClient $client, AccessToken $accessToken)
    {
        parent::__construct($client, $accessToken);
    }
}
