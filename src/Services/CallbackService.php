<?php

namespace Larva\LaravelFadada\Services;

use FddCloud\client\CallbackClient;
use Larva\LaravelFadada\AccessToken;

/**
 * 回调管理
 *
 * @see \FddCloud\client\CallbackClient
 */
class CallbackService extends BaseService
{
    public function __construct(CallbackClient $client, AccessToken $accessToken)
    {
        parent::__construct($client, $accessToken);
    }
}
