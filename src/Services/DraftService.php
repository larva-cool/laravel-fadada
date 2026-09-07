<?php

namespace Larva\LaravelFadada\Services;

use FddCloud\client\DraftClient;
use Larva\LaravelFadada\AccessToken;

/**
 * 合同起草（合同协商、定稿）
 *
 * @see \FddCloud\client\DraftClient
 */
class DraftService extends BaseService
{
    public function __construct(DraftClient $client, AccessToken $accessToken)
    {
        parent::__construct($client, $accessToken);
    }
}
