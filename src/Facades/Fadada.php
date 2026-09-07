<?php

namespace Larva\LaravelFadada\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Larva\LaravelFadada\Services\UserService user()
 * @method static \Larva\LaravelFadada\Services\CorpService corp()
 * @method static \Larva\LaravelFadada\Services\OrgService org()
 * @method static \Larva\LaravelFadada\Services\SealService seal()
 * @method static \Larva\LaravelFadada\Services\TemplateService template()
 * @method static \Larva\LaravelFadada\Services\AppTemplateService appTemplate()
 * @method static \Larva\LaravelFadada\Services\DocService doc()
 * @method static \Larva\LaravelFadada\Services\SignTaskService signTask()
 * @method static \Larva\LaravelFadada\Services\EUIService eui()
 * @method static \Larva\LaravelFadada\Services\ApprovalService approval()
 * @method static \Larva\LaravelFadada\Services\DraftService draft()
 * @method static \Larva\LaravelFadada\Services\ArchivesPerformanceService archivesPerformance()
 * @method static \Larva\LaravelFadada\Services\OCRService ocr()
 * @method static \Larva\LaravelFadada\Services\ToolService tool()
 * @method static \Larva\LaravelFadada\Services\CallbackService callback()
 * @method static \Larva\LaravelFadada\Services\GetServiceService getService()
 * @method static \Larva\LaravelFadada\AccessToken accessToken()
 *
 * @see \Larva\LaravelFadada\FadadaManager
 */
class Fadada extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return FadadaManager::class;
    }
}
