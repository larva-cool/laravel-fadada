<?php

namespace Larva\Fadada\Services;

use FddCloud\client\SealClient;
use Larva\Fadada\AccessToken;

/**
 * 印章管理（企业印章、个人签名）
 *
 * @see \FddCloud\client\SealClient
 */
class SealService extends BaseService
{
    public function __construct(SealClient $client, AccessToken $accessToken)
    {
        parent::__construct($client, $accessToken);
    }

    /**
     * 快捷：获取印章管理链接。
     *
     * 原 SDK: SealClient::getSealManageUrl(accessToken, GetSealManageUrlReq)
     * 会从 Initiator 自动合并 clientCorpId（企业调用方时）。
     *
     * @param array $data
     * @return array
     */
    public function quickManageUrl(array $data = []): array
    {
        return $this->getSealManageUrl($this->mergeCorpContext($data));
    }

    /**
     * 快捷：获取印章创建链接。
     *
     * 原 SDK: SealClient::getSealCreateUrl(accessToken, GetSealCreateUrlReq)
     *
     * @param array $data
     * @return array
     */
    public function quickCreateUrl(array $data = []): array
    {
        return $this->getSealCreateUrl($this->mergeCorpContext($data));
    }

    /**
     * 快捷：获取设置用印员链接。
     *
     * 原 SDK: SealClient::getSealGrantUrl(accessToken, GetSealGrantUrlReq)
     *
     * @param string $sealId
     * @param array  $extra
     * @return array
     */
    public function quickGrantUrl(string $sealId, array $extra = []): array
    {
        return $this->getSealGrantUrl($this->mergeCorpContext(array_merge($extra, [
            'sealId' => $sealId,
        ])));
    }

    /**
     * 快捷：解除印章授权。
     *
     * 原 SDK: SealClient::sealGrantCancel(accessToken, SealGrantCancelReq)
     *
     * @param string $sealId
     * @param array  $extra
     * @return array
     */
    public function quickGrantCancel(string $sealId, array $extra = []): array
    {
        return $this->sealGrantCancel($this->mergeCorpContext(array_merge($extra, [
            'sealId' => $sealId,
        ])));
    }

    /**
     * 合并企业上下文：把 Initiator 中的 openId 作为 clientCorpId 注入。
     * 调用方已显式传入时优先使用调用方值。
     */
    protected function mergeCorpContext(array $data): array
    {
        if (isset($data['clientCorpId']) && $data['clientCorpId'] !== '') {
            return $data;
        }

        $initiator = $this->getInitiator();
        if (($initiator['idType'] ?? '') === 'corp' && ($initiator['openId'] ?? '') !== '') {
            $data['clientCorpId'] = $initiator['openId'];
        }

        return $data;
    }
}
