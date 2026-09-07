<?php

namespace Larva\LaravelFadada\Services;

use FddCloud\client\ArchivesPerformanceClient;
use Larva\LaravelFadada\AccessToken;

/**
 * 合同归档 / 合同履约
 *
 * @see \FddCloud\client\ArchivesPerformanceClient
 */
class ArchivesPerformanceService extends BaseService
{
    public function __construct(ArchivesPerformanceClient $client, AccessToken $accessToken)
    {
        parent::__construct($client, $accessToken);
    }
}
