<?php

namespace Larva\LaravelFadada\Services;

use FddCloud\client\SignTaskClient;
use Larva\LaravelFadada\AccessToken;

/**
 * 签署任务
 *
 * @see \FddCloud\client\SignTaskClient
 */
class SignTaskService extends BaseService
{
    public function __construct(SignTaskClient $client, AccessToken $accessToken)
    {
        parent::__construct($client, $accessToken);
    }
}
