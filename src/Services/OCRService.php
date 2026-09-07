<?php

namespace Larva\LaravelFadada\Services;

use FddCloud\client\OCRClient;
use Larva\LaravelFadada\AccessToken;

/**
 * 智能审查和智能比对
 *
 * @see \FddCloud\client\OCRClient
 */
class OCRService extends BaseService
{
    public function __construct(OCRClient $client, AccessToken $accessToken)
    {
        parent::__construct($client, $accessToken);
    }
}
