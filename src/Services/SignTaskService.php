<?php

namespace Larva\Fadada\Services;

use FddCloud\client\SignTaskClient;
use Larva\Fadada\AccessToken;

/**
 * 签署任务
 *
 * @see \FddCloud\client\SignTaskClient
 */
class SignTaskService extends BaseService
{
    /**
     * 签署任务服务构造函数。
     */
    public function __construct(SignTaskClient $client, AccessToken $accessToken)
    {
        parent::__construct($client, $accessToken);
    }

    /**
     * 基于模板快速创建签署任务（默认自动提交、签完自动结束）。
     *
     * @param  string  $signTemplateId  签署模板 ID
     * @param  string  $subject  签署任务主题
     * @param  array<int, array<string, mixed>>  $actors  参与方列表
     * @param  array<string, mixed>  $options  扩展参数
     * @return array{signTaskId: string}
     */
    public function createSignWithTemplate(string $signTemplateId, string $subject, array $actors, array $options = []): array
    {
        $payload = array_merge([
            'initiator' => $this->getInitiator(),
            'signTemplateId' => $signTemplateId,
            'signTaskSubject' => $subject,
            'freeSignType' => 'template',
            'autoStart' => true,
            'autoFinish' => true,
            'actors' => $actors,
        ], $options);

        return $this->createWithTemplate($payload);
    }

    /**
     * 查询签署任务详情。
     *
     * @return array<string, mixed>
     */
    public function getSignTaskDetail(string $signTaskId): array
    {
        return $this->getDetail(['signTaskId' => $signTaskId]);
    }

    /**
     * 获取签署参与方专属签署链接。
     *
     * @param  string  $signTaskId  签署任务 ID
     * @param  string  $actorId  参与方 ID（模板中的 actorId，如"甲方"/"乙方"）
     * @param  string  $redirectUrl  签署完成后 H5 跳转地址
     * @return array{actorSignTaskUrl: string, actorSignTaskEmbedUrl: string}
     */
    public function getActorSignUrl(string $signTaskId, string $actorId, string $redirectUrl = ''): array
    {
        $payload = [
            'signTaskId' => $signTaskId,
            'actorId' => $actorId,
        ];
        if ($redirectUrl !== '') {
            $payload['redirectUrl'] = $redirectUrl;
        }

        return $this->actorGetUrl($payload);
    }

    /**
     * 撤销签署任务。
     */
    public function cancelSignTask(string $signTaskId, string $terminationNote = ''): array
    {
        $payload = ['signTaskId' => $signTaskId];
        if ($terminationNote !== '') {
            $payload['terminationNote'] = $terminationNote;
        }

        return $this->cancel($payload);
    }

    /**
     * 获取签署完成后的文件下载地址。
     */
    public function getSignTaskDownloadUrl(string $signTaskId): array
    {
        return $this->getOwnerDownloadUrl(['signTaskId' => $signTaskId]);
    }

    /**
     * 发件人（发起方）批量填写文档控件（适用于模板中"发件人填写"的字段）。
     *
     * 注意：必须在创建签署任务时设置 autoStart=false，填完后再调用 start() 启动。
     *
     * @param  array<int, array{docId: string, fieldId: string, fieldValue: string}>  $docFieldValues
     */
    public function fillSenderFields(string $signTaskId, array $docFieldValues): array
    {
        return $this->fillFieldsValue([
            'signTaskId' => $signTaskId,
            'docFieldValues' => $docFieldValues,
        ]);
    }

    /**
     * 启动签署任务（fillSenderFields 之后调用）。
     */
    public function startSignTask(string $signTaskId): array
    {
        return $this->start(['signTaskId' => $signTaskId]);
    }
}
