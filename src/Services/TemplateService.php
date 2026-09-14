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

    /**
     * 查询签署模板列表。
     *
     * @param  int  $pageNo   页码（从 1 起）
     * @param  int  $pageSize 每页数量
     * @param  array<string, mixed>  $filters  其他过滤条件（如 signTemplateName）
     * @return array<string, mixed>
     */
    public function getSignTemplates(int $pageNo = 1, int $pageSize = 20, array $filters = []): array
    {
        $payload = array_merge($filters, [
            'openCorpId' => $this->getInitiator()['openId'],
            'pageNo'     => $pageNo,
            'pageSize'   => $pageSize,
        ]);

        return $this->getSignTemplateList($payload);
    }

    /**
     * 获取模板新增链接（管理员在 H5 页面新增模板）。
     *
     * @param  string  $redirectUrl  新增完成后的跳转地址
     * @return array<string, mixed>
     */
    public function getCreateUrl(string $redirectUrl = ''): array
    {
        $payload = ['openCorpId' => $this->getInitiator()['openId']];
        if ($redirectUrl !== '') {
            $payload['redirectUrl'] = $redirectUrl;
        }

        return $this->templateCreateGetUrl($payload);
    }

    /**
     * 获取模板编辑链接（管理员在 H5 页面编辑指定模板）。
     *
     * @param  string  $templateId
     * @param  string  $redirectUrl  编辑完成后的跳转地址
     * @return array<string, mixed>
     */
    public function getEditUrl(string $templateId, string $redirectUrl = ''): array
    {
        $payload = [
            'openCorpId' => $this->getInitiator()['openId'],
            'templateId' => $templateId,
        ];
        if ($redirectUrl !== '') {
            $payload['redirectUrl'] = $redirectUrl;
        }

        return $this->templateEditGetUrl($payload);
    }

    /**
     * 获取模板预览链接。
     *
     * @param  string  $templateId
     * @return array<string, mixed>
     */
    public function getPreviewUrl(string $templateId): array
    {
        return $this->templatePreviewGetUrl([
            'openCorpId' => $this->getInitiator()['openId'],
            'templateId' => $templateId,
        ]);
    }

    /**
     * 获取模板管理链接（H5 后台模板列表页）。
     *
     * @param  string  $redirectUrl  管理完成后的跳转地址
     * @return array<string, mixed>
     */
    public function getManageUrl(string $redirectUrl = ''): array
    {
        $payload = ['openCorpId' => $this->getInitiator()['openId']];
        if ($redirectUrl !== '') {
            $payload['redirectUrl'] = $redirectUrl;
        }

        return $this->templateManageGetUrl($payload);
    }
}
