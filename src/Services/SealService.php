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
     * 获取印章对模板的免验签授权链接。
     *
     * 新模板接入免验签（甲方自动盖章）前，需企业印章对模板完成授权：
     * 将返回的 H5 链接交给企业管理员打开并确认后，sealId 对 templateId 的
     * 免验签授权关系即生效（与按 business_id 场景码授权的 getSealFreeSignUrl 互补）。
     * 已授权关系可通过 SealService::getFreeSignToTemplateList 查询。
     *
     * @param  array<int, string>  $templateIds  签署模板 ID 列表
     * @param  array<int, string>  $sealIds  印章 ID 列表；默认取 config fadada.seal_id
     * @param  string  $redirectUrl  授权完成后的跳转地址
     * @return array<string, mixed>
     */
    public function getFreeSignToTemplateUrl(array $templateIds, array $sealIds = [], string $redirectUrl = ''): array
    {
        if ($sealIds === []) {
            $sealId = (string) $this->config('fadada.seal_id', '');
            $sealIds = $sealId !== '' ? [$sealId] : [];
        }

        $initiator = $this->getInitiator();
        $payload = [
            'openCorpId' => $initiator['openId'] ?? '',
            'sealIds' => $sealIds,
            'templateIds' => $templateIds,
        ];
        if ($redirectUrl !== '') {
            $payload['redirectUrl'] = $redirectUrl;
        }

        return $this->getFreeSignToTemplateUrl($payload);
    }

    /**
     * 获取印章免验证签链接（按业务场景 business_id 授权）。
     *
     * 与 getFreeSignToTemplateUrl（按 templateId 授权）互补，业务在创建
     * 签署任务前先引导管理员完成授权，授权后才能发起免验签的签署任务。
     *
     * @param  string  $businessId  免验签场景码（法大大业务方提供）
     * @param  string  $sealId  印章 ID；默认取 config fadada.seal_id
     * @param  string  $redirectUrl  授权完成后的跳转地址
     * @return array<string, mixed>
     */
    public function getSealFreeSignUrlByBusinessId(string $businessId, string $sealId = '', string $redirectUrl = ''): array
    {
        if ($sealId === '') {
            $sealId = (string) $this->config('fadada.seal_id', '');
        }

        $initiator = $this->getInitiator();
        $payload = [
            'openCorpId' => $initiator['openId'] ?? '',
            'sealId' => $sealId,
            'businessId' => $businessId,
        ];
        if ($redirectUrl !== '') {
            $payload['redirectUrl'] = $redirectUrl;
        }

        return $this->getSealFreeSignUrl($payload);
    }

    /**
     * 获取设置用印员链接（管理员在 H5 页面选择用印员并确认授权）。
     *
     * @param  string  $sealId  印章 ID；默认取 config fadada.seal_id
     * @param  string  $redirectUrl  授权完成后的跳转地址
     * @return array<string, mixed>
     */
    public function getSealGrantUrlByDefault(string $sealId = '', string $redirectUrl = ''): array
    {
        if ($sealId === '') {
            $sealId = (string) $this->config('fadada.seal_id', '');
        }

        $initiator = $this->getInitiator();
        $payload = [
            'openCorpId' => $initiator['openId'] ?? '',
            'sealId' => $sealId,
        ];
        if ($redirectUrl !== '') {
            $payload['redirectUrl'] = $redirectUrl;
        }

        return $this->getSealGrantUrl($payload);
    }

    /**
     * 解除印章授权（取消指定的用印员）。
     *
     * @param  string  $sealId  印章 ID；默认取 config fadada.seal_id
     * @param  string  $clientUserId  被解除授权的用印员 openUserId
     * @return array<string, mixed>
     */
    public function sealGrantCancelByDefault(string $sealId = '', string $clientUserId = ''): array
    {
        if ($sealId === '') {
            $sealId = (string) $this->config('fadada.seal_id', '');
        }

        $initiator = $this->getInitiator();
        $payload = [
            'openCorpId' => $initiator['openId'] ?? '',
            'sealId' => $sealId,
        ];
        if ($clientUserId !== '') {
            $payload['clientUserId'] = $clientUserId;
        }

        return $this->sealGrantCancel($payload);
    }
}
