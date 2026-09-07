<?php

namespace Larva\Fadada;

use Larva\Fadada\Services\AppTemplateService;
use Larva\Fadada\Services\ApprovalService;
use Larva\Fadada\Services\ArchivesPerformanceService;
use Larva\Fadada\Services\CallbackService;
use Larva\Fadada\Services\CorpService;
use Larva\Fadada\Services\DocService;
use Larva\Fadada\Services\DraftService;
use Larva\Fadada\Services\EUIService;
use Larva\Fadada\Services\GetServiceService;
use Larva\Fadada\Services\OCRService;
use Larva\Fadada\Services\OrgService;
use Larva\Fadada\Services\SealService;
use Larva\Fadada\Services\SignTaskService;
use Larva\Fadada\Services\TemplateService;
use Larva\Fadada\Services\ToolService;
use Larva\Fadada\Services\UserService;

/**
 * Fadada 外观根
 *
 * 通过 \Fadada::user()、\Fadada::signTask() 等方式拿到对应的业务 Service。
 * 也可通过 \Fadada::accessToken() 获取 AccessToken 管理器。
 */
class FadadaManager
{
    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $app;

    public function __construct(\Illuminate\Contracts\Container\Container $app)
    {
        $this->app = $app;
    }

    public function user(): UserService
    {
        return $this->app->make(UserService::class);
    }

    public function corp(): CorpService
    {
        return $this->app->make(CorpService::class);
    }

    public function org(): OrgService
    {
        return $this->app->make(OrgService::class);
    }

    public function seal(): SealService
    {
        return $this->app->make(SealService::class);
    }

    public function template(): TemplateService
    {
        return $this->app->make(TemplateService::class);
    }

    public function appTemplate(): AppTemplateService
    {
        return $this->app->make(AppTemplateService::class);
    }

    public function doc(): DocService
    {
        return $this->app->make(DocService::class);
    }

    public function signTask(): SignTaskService
    {
        return $this->app->make(SignTaskService::class);
    }

    public function eui(): EUIService
    {
        return $this->app->make(EUIService::class);
    }

    public function approval(): ApprovalService
    {
        return $this->app->make(ApprovalService::class);
    }

    public function draft(): DraftService
    {
        return $this->app->make(DraftService::class);
    }

    public function archivesPerformance(): ArchivesPerformanceService
    {
        return $this->app->make(ArchivesPerformanceService::class);
    }

    public function ocr(): OCRService
    {
        return $this->app->make(OCRService::class);
    }

    public function tool(): ToolService
    {
        return $this->app->make(ToolService::class);
    }

    public function callback(): CallbackService
    {
        return $this->app->make(CallbackService::class);
    }

    public function getService(): GetServiceService
    {
        return $this->app->make(GetServiceService::class);
    }

    public function accessToken(): AccessToken
    {
        return $this->app->make(AccessToken::class);
    }

    /**
     * 直接获取原 SDK 的底层 Client。
     */
    public function client(): FadadaClient
    {
        return $this->app->make(FadadaClient::class);
    }
}
