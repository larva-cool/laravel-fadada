<?php

namespace Larva\Fadada\Services;

use FddCloud\client\TemplateClient;
use Larva\Fadada\AccessToken;

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


    /**
     * 查询签署模板详情。
     *
     * @return array<string, mixed>
     */
    public function getDetail(string $signTemplateId): array
    {
        return $this->getSignTemplateDetail([
            'signTemplateId' => $signTemplateId,
            'ownerId' => $this->getInitiator(),
        ]);
    }

    /**
     * 获取模板下载地址。
     *
     * @return array<string, mixed>
     */
    public function getDownloadUrl(string $templateId): array
    {
        return $this->getTemplateDownloadUrl([
            'openCorpId' => $this->getInitiator()['openId'],
            'type' => 'sign',
            'templateId' => $templateId,
        ]);
    }
}
