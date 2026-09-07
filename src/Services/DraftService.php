<?php

namespace Larva\Fadada\Services;

use FddCloud\client\DraftClient;
use Larva\Fadada\AccessToken;

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
