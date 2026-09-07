<?php

namespace Larva\LaravelFadada\Services;

use FddCloud\client\TemplateClient;
use Larva\LaravelFadada\AccessToken;

/**
 * 模板管理
 *
 * @see \FddCloud\client\TemplateClient
 */
class TemplateService extends BaseService
{
    public function __construct(TemplateClient $client, AccessToken $accessToken)
    {
        parent::__construct($client, $accessToken);
    }
}
