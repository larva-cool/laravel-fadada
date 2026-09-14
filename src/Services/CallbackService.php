<?php

namespace Larva\Fadada\Services;

use FddCloud\client\CallbackClient;
use Larva\Fadada\AccessToken;

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

    /**
     * 查询回调列表。
     *
     * @param  int  $pageNo   页码（从 1 起）
     * @param  int  $pageSize 每页数量
     * @param  array<string, mixed>  $filters  其他过滤条件
     * @return array<string, mixed>
     */
    public function getCallbacks(int $pageNo = 1, int $pageSize = 20, array $filters = []): array
    {
        $payload = array_merge($filters, [
            'openCorpId' => $this->getInitiator()['openId'],
            'pageNo'     => $pageNo,
            'pageSize'   => $pageSize,
        ]);

        return $this->getCallbackList($payload);
    }
}
