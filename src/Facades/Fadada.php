<?php

namespace Larva\Fadada\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Larva\Fadada\Services\UserService user()
 * @method static \Larva\Fadada\Services\CorpService corp()
 * @method static \Larva\Fadada\Services\OrgService org()
 * @method static \Larva\Fadada\Services\SealService seal()
 * @method static \Larva\Fadada\Services\TemplateService template()
 * @method static \Larva\Fadada\Services\AppTemplateService appTemplate()
 * @method static \Larva\Fadada\Services\DocService doc()
 * @method static \Larva\Fadada\Services\SignTaskService signTask()
 * @method static \Larva\Fadada\Services\EUIService eui()
 * @method static \Larva\Fadada\Services\ApprovalService approval()
 * @method static \Larva\Fadada\Services\DraftService draft()
 * @method static \Larva\Fadada\Services\ArchivesPerformanceService archivesPerformance()
 * @method static \Larva\Fadada\Services\OCRService ocr()
 * @method static \Larva\Fadada\Services\ToolService tool()
 * @method static \Larva\Fadada\Services\CallbackService callback()
 * @method static \Larva\Fadada\Services\GetServiceService getService()
 * @method static \Larva\Fadada\AccessToken accessToken()
 *
 * @see \Larva\Fadada\FadadaManager
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
