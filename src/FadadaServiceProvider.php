<?php

namespace Larva\Fadada;

use FddCloud\client\ApprovalClient;
use FddCloud\client\AppTemplateClient;
use FddCloud\client\ArchivesPerformanceClient;
use FddCloud\client\CallbackClient;
use FddCloud\client\Client as SdkClient;
use FddCloud\client\CorpClient;
use FddCloud\client\DocClient;
use FddCloud\client\DraftClient;
use FddCloud\client\EUIClient;
use FddCloud\client\IClient;
use FddCloud\client\OCRClient;
use FddCloud\client\OrgClient;
use FddCloud\client\SealClient;
use FddCloud\client\ServiceClient;
use FddCloud\client\SignTaskClient;
use FddCloud\client\TemplateClient;
use FddCloud\client\ToolServiceClient;
use FddCloud\client\UserClient;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\ServiceProvider;
use Larva\Fadada\Services\ApprovalService;
use Larva\Fadada\Services\AppTemplateService;
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
 * 法大大 服务提供器
 */
class FadadaServiceProvider extends ServiceProvider
{
    /**
     * 各业务 Service 对应的原 SDK client 类。
     */
    protected $serviceBindings = [
        UserService::class => UserClient::class,
        CorpService::class => CorpClient::class,
        OrgService::class => OrgClient::class,
        SealService::class => SealClient::class,
        TemplateService::class => TemplateClient::class,
        AppTemplateService::class => AppTemplateClient::class,
        DocService::class => DocClient::class,
        SignTaskService::class => SignTaskClient::class,
        EUIService::class => EUIClient::class,
        ApprovalService::class => ApprovalClient::class,
        DraftService::class => DraftClient::class,
        ArchivesPerformanceService::class => ArchivesPerformanceClient::class,
        OCRService::class => OCRClient::class,
        ToolService::class => ToolServiceClient::class,
        CallbackService::class => CallbackClient::class,
    ];

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/fadada.php',
            'fadada'
        );

        // 原 SDK 底层 Client（带签名逻辑）
        $this->app->singleton(SdkClient::class, function ($app) {
            $config = $app['config']->get('fadada');
            return new SdkClient(
                $config['app_id'],
                $config['app_secret'],
                $config['service_url'],
                $config['timeout'],
                $config['debug']
            );
        });

        // 我们自己的包装类（实现 IClient）
        $this->app->singleton(FadadaClient::class, function ($app) {
            return new FadadaClient($app->make(SdkClient::class));
        });

        // IClient 别名：让所有依赖 IClient 的原 SDK Client
        // （以及 ServiceClient 等）解析到我们的包装类 FadadaClient。
        // Laravel 容器 alias() 签名为 alias($abstract, $alias)：
        //   $aliases[$alias] = $abstract;
        // 即第一个参数是「被指向的抽象」，第二个是「别名键」。
        $this->app->alias(FadadaClient::class, IClient::class);

        // FadadaManager：Facade 根
        $this->app->singleton(FadadaManager::class, function ($app) {
            return new FadadaManager($app);
        });

        // ServiceClient：用于获取 access token
        $this->app->singleton(ServiceClient::class, function ($app) {
            return new ServiceClient($app->make(FadadaClient::class));
        });

        // AccessToken 管理（带缓存）
        $this->app->singleton(AccessToken::class, function ($app) {
            $config = $app['config']->get('fadada');
            $store = $config['token']['cache_store'] ?? null;
            /** @var CacheRepository $cache */
            $cache = $store ? $app['cache']->store($store) : $app['cache']->store();

            return new AccessToken(
                $app->make(ServiceClient::class),
                $cache,
                array_merge(
                    ['app_id' => $config['app_id']],
                    $config['token'] ?? []
                )
            );
        });

        // 16 个业务 Service
        $this->app->singleton(GetServiceService::class, function ($app) {
            return new GetServiceService(
                $app->make(ServiceClient::class),
                $app->make(AccessToken::class)
            );
        });

        foreach ($this->serviceBindings as $serviceClass => $clientClass) {
            $this->app->singleton($serviceClass, function ($app) use ($serviceClass, $clientClass) {
                return new $serviceClass(
                    $app->make($clientClass),
                    $app->make(AccessToken::class)
                );
            });
        }
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/fadada.php' => config_path('fadada.php'),
            ], 'fadada-config');
        }
    }
}
