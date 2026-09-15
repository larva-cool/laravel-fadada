# laravel-fadada

Laravel 封装 [法大大 FASC OpenAPI PHP SDK](https://packagist.org/packages/fadada/fasc-openapi-php-sdk)（v5.1）。

> 法大大电子合同和电子签云服务开放平台（FASC OPEN API）配套工具。

## 特性

- ✅ 与 Laravel 12 / 13 兼容（illuminate/support ^12.0|^13.0，PHP >= 8.2）
- ✅ 通过 ServiceProvider 自动注册，开箱即用
- ✅ AccessToken 自动管理（缓存 + 过期前自动续期）
- ✅ 提供 `Fadada` Facade：`Fadada::user()`, `Fadada::signTask()` 等
- ✅ 全部 16 个业务模块（User/Corp/Org/Seal/Template/AppTemplate/Doc/SignTask/EUI/Approval/Draft/ArchivesPerformance/OCR/ToolService/Callback/GetService）完整覆盖
- ✅ req 参数支持数组 / stdClass / 原 SDK 对象三种入参方式（snake_case 自动转 camelCase）
- ✅ 配置可通过 .env 文件管理

## 安装

```bash
composer require larva/laravel-fadada
```

> 包已自动注册 ServiceProvider 与 Facade，无需在 `config/app.php` 中手动添加。

发布配置文件（可选）：

```bash
php artisan vendor:publish --tag=fadada-config
```

## 配置

在 `.env` 中填写：

```env
FADADA_APP_ID=你的app_id
FADADA_APP_SECRET=你的app_secret
FADADA_SERVICE_URL=https://api.fadada.com/api/v5/
FADADA_TIMEOUT=60
FADADA_DEBUG=false

# AccessToken 缓存相关（可选）
FADADA_TOKEN_STORE=               # 留空使用默认 cache store
FADADA_TOKEN_PREFIX=fadada:access_token:
FADADA_TOKEN_TTL=7000             # 法大大 token 有效期 7200 秒，建议提前 200 秒
```

也可以直接编辑 `config/fadada.php`：

```php
return [
    'app_id'     => env('FADADA_APP_ID', ''),
    'app_secret' => env('FADADA_APP_SECRET', ''),
    'service_url'=> env('FADADA_SERVICE_URL', 'https://api.fadada.com/api/v5/'),
    'timeout'    => 60,
    'debug'      => false,
    'token'      => [
        'cache_store' => null,
        'cache_prefix'=> 'fadada:access_token:',
        'ttl'         => 7000,
    ],
];
```

## 使用

### 通过 Facade

```php
use Larva\Fadada\Facades\Fadada;

// 个人用户授权链接
$response = Fadada::user()->getUserAuthUrl([
    'client_user_id' => 'user-001',
    'account_name'   => '13800000000',
    'auth_scopes'    => ['signtask_init', 'signtask_info', 'seal_info'],
    'redirect_url'   => 'https://your-app.com/callback',
]);

// 创建签署任务
$response = Fadada::signTask()->createSignTask([
    'client_user_id' => 'biz-001',
    // ... 其他字段
]);

// 获取 / 刷新 AccessToken
$token = Fadada::accessToken()->getToken();
$token = Fadada::getService()->refreshAccessToken();
```

### 通过依赖注入

```php
use Larva\Fadada\Services\SignTaskService;
use Larva\Fadada\Services\UserService;

class ContractController
{
    public function __construct(
        private UserService $user,
        private SignTaskService $signTask,
    ) {}

    public function auth()
    {
        return $this->user->getUserAuthUrl([
            'client_user_id' => 'user-001',
            'account_name'   => '13800000000',
        ]);
    }
}
```

### 手动传入原 SDK req 对象

如果你更喜欢类型安全的对象风格，原 SDK 的所有 req 类都可以直接传入：

```php
use FddCloud\bean\req\user\GetUserAuthUrlReq;

$req = new GetUserAuthUrlReq();
$req->setClientUserId('user-001');
$req->setAccountName('13800000000');

Fadada::user()->getUserAuthUrl($req);
```

## 可用的业务模块

通过 `Fadada` Facade 可访问：

| Facade 方法 | 对应原 SDK client | 说明 |
| --- | --- | --- |
| `Fadada::user()` | `UserClient` | 个人认证授权 |
| `Fadada::corp()` | `CorpClient` | 企业认证授权 |
| `Fadada::org()` | `OrgClient` | 组织管理（部门/成员/相对方） |
| `Fadada::seal()` | `SealClient` | 印章管理 |
| `Fadada::template()` | `TemplateClient` | 模板管理 |
| `Fadada::appTemplate()` | `AppTemplateClient` | 应用模板管理 |
| `Fadada::doc()` | `DocClient` | 文档处理 |
| `Fadada::signTask()` | `SignTaskClient` | 签署任务 |
| `Fadada::eui()` | `EUIClient` | 计费管理 |
| `Fadada::approval()` | `ApprovalClient` | 审批管理 |
| `Fadada::draft()` | `DraftClient` | 合同起草 |
| `Fadada::archivesPerformance()` | `ArchivesPerformanceClient` | 合同归档 / 履约 |
| `Fadada::ocr()` | `OCRClient` | 智能审查与比对 |
| `Fadada::tool()` | `ToolServiceClient` | 工具能力服务 |
| `Fadada::callback()` | `CallbackClient` | 回调管理 |
| `Fadada::getService()` | `ServiceClient` | 服务访问凭证 |
| `Fadada::accessToken()` | — | AccessToken 管理器 |
| `Fadada::client()` | — | 获取底层 FadadaClient |

所有业务 Service 都通过 `__call` 代理到原 SDK 客户端，因此原 SDK 上所有方法（包括未来升级新增的方法）都可直接调用，**不会因为版本升级而失效**。

## AccessToken

- 默认使用 Laravel `cache` 中的默认 store 存储
- 自动在过期前续期（默认 TTL = 7000 秒，比法大大 7200 秒有效期提前 200 秒）
- 可通过 `Fadada::accessToken()->flush()` 主动清除缓存
- 多 app 共用一套代码时，每个 `app_id` 会单独缓存 token

## 回调通知

法大大平台在签署任务状态变更时会向你的服务推送回调通知。本包已内置 `FddController` 处理回调验签和事件分发。

### 配置回调地址

在 `.env` 中配置回调地址：

```env
FADADA_CALLBACK_URL=https://your-app.com/fadada/callback
```

### 注册路由

在你的路由文件（如 `routes/web.php` 或 `routes/api.php`）中注册回调路由：

```php
use Larva\Fadada\FddController;

Route::post('/fadada/callback', [FddController::class, 'callback']);
```

> 如果使用 CSRF 中间件，需将该路由排除在 CSRF 验证之外。

### 回调处理流程

`FddController::callback()` 依次执行：

1. **时间戳校验**（防重放，±5 分钟容差）
2. **签名验证**（HMAC-SHA256，防篡改）
3. **bizContent JSON 解码**
4. **事件分发**（根据事件类型派发对应的 Laravel Event）
5. **返回响应**（验签通过返回 `{"msg":"success"}`，失败返回 `{"msg":"fail"}`）

### 事件列表

回调处理后会根据事件类型自动派发以下事件，所有事件均继承 `FddEvent`，通过 `$event->data` 可获取业务数据：

| 事件类 | 事件 ID | 说明 |
| --- | --- | --- |
| `SignTaskCreated` | `sign-task-created` | 签署任务创建 |
| `SignTaskCanceled` | `sign-task-canceled` | 签署任务撤销 |
| `SignTaskExtension` | `sign-task-extension` | 签署任务延期 |
| `SignTaskFinished` | `sign-task-finished` | 签署任务完成 |
| `SignTaskAbolish` | `sign-task-abolish` | 签署任务作废 |
| `FddEvent` | 其他 | 未识别的事件类型（兜底） |

### 监听事件

在 `AppServiceProvider` 的 `boot()` 方法中注册监听器，或使用 Laravel 的 Event Discovery：

```php
use Illuminate\Support\Facades\Event;
use Larva\Fadada\Events\SignTaskFinished;

Event::listen(SignTaskFinished::class, function (SignTaskFinished $event) {
    $data = $event->data;
    // 处理签署完成逻辑，如更新合同状态、发送通知等
});
```

或在 `app/Providers/EventServiceProvider.php` 中：

```php
protected $listen = [
    \Larva\Fadada\Events\SignTaskFinished::class => [
        \App\Listeners\HandleSignTaskFinished::class,
    ],
];
```

### 手动查询回调记录

除了接收推送回调外，也可主动查询平台上的回调推送记录：

```php
$callbacks = Fadada::callback()->getCallbacks(pageNo: 1, pageSize: 20);
```

## 协议

MIT
