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

回调处理后会根据事件类型自动派发以下事件，所有事件均继承 `FddEvent`，通过 `$event->data` 可获取业务数据。`FddController` 内部通过 `$eventMap` 映射表将事件 ID 路由到对应的事件类，未在映射表中的事件将使用兜底的 `FddEvent` 类派发。

#### 签署任务事件

| 事件类 | 事件 ID | 说明 |
| --- | --- | --- |
| `SignTaskCreated` | `sign-task-created` | 签署任务创建 |
| `SignTaskStart` | `sign-task-start` | 签署任务提交（启动） |
| `SignTaskSigned` | `sign-task-signed` | 参与方签署成功 |
| `SignTaskFilled` | `sign-task-filled` | 参与方填写完成 |
| `SignTaskFillRejected` | `sign-task-fill-rejected` | 参与方拒填 |
| `SignTaskFinalize` | `sign-task-finalize` | 签署任务定稿 |
| `SignTaskRead` | `sign-task-read` | 参与方 / 抄送方阅读 |
| `SignTaskJoined` | `sign-task-joined` | 参与方加入 |
| `SignTaskJoinFailed` | `sign-task-join-failed` | 参与方加入失败 |
| `SignTaskSignFailed` | `sign-task-sign-failed` | 签署失败（免验证签署） |
| `SignTaskSignRejected` | `sign-task-sign-rejected` | 参与方拒签 |
| `SignTaskIgnore` | `sign-task-ignore` | 驳回填写（需手动定稿时） |
| `SignTaskPending` | `sign-task-pending` | 待处理（3.0 任务专属） |
| `SignTaskDownload` | `sign-task-download` | 批量下载文档压缩包就绪 |
| `SignTaskExtension` | `sign-task-extension` | 签署任务延期 |
| `SignTaskFinished` | `sign-task-finished` | 签署任务完成 |
| `SignTaskCanceled` | `sign-task-canceled` | 签署任务撤销 |
| `SignTaskAbolish` | `sign-task-abolish` | 签署任务作废 |
| `SignTaskExpire` | `sign-task-expire` | 签署任务过期 |

#### 认证授权事件

| 事件类 | 事件 ID | 说明 |
| --- | --- | --- |
| `UserAuthorize` | `user-authorize` | 个人用户授权 |
| `CorpAuthorize` | `corp-authorize` | 企业用户授权 |
| `UserCancelAuthorization` | `user-cancel-authorization` | 个人用户解除授权 |
| `CorpCancelAuthorization` | `corp-cancel-authorization` | 企业用户解除授权 |
| `UserThreeElementVerify` | `user-three-element-verify` | 个人三要素校验 |
| `UserFourElementVerify` | `user-four-element-verify` | 个人四要素校验 |

#### 印章管理事件

| 事件类 | 事件 ID | 说明 |
| --- | --- | --- |
| `SealCreate` | `seal-create` | 印章创建 |
| `SealDelete` | `seal-delete` | 印章删除 |
| `SealEnable` | `seal-enable` | 印章启用 |
| `SealDisable` | `seal-disable` | 印章停用 |
| `SealModifyInfo` | `seal-modify-info` | 印章基本信息修改 |
| `SealCancellation` | `seal-cancellation` | 印章注销 |
| `SealAuthorizeMember` | `seal-authorize-member` | 印章授权成员 |
| `SealAuthorizeMemberCancel` | `seal-authorize-member-cancel` | 印章取消授权成员 |
| `SealAuthorizeFreeSign` | `seal-authorize-free-sign` | 印章授权免验证签 |
| `SealAuthorizeFreeSignCancel` | `seal-authorize-free-sign-cancel` | 印章免验证签解除 |
| `SealAuthorizeFreeSignDueCancel` | `seal-authorize-free-sign-due-cancel` | 印章免验证签即将到期 |
| `SealVerifySuccessed` | `seal-verify-successed` | 印章审核通过 |
| `SealVerifyFailed` | `seal-verify-failed` | 印章审核不通过 |
| `SealVerifyCancel` | `seal-verify-cancel` | 印章审核撤销 |

#### 个人签名事件

| 事件类 | 事件 ID | 说明 |
| --- | --- | --- |
| `PersonalSealCreate` | `personal-seal-create` | 签名创建 |
| `PersonalSealDelete` | `personal-seal-delete` | 签名删除 |
| `PersonalSealAuthorizeFreeSign` | `personal-seal-authorize-free-sign` | 个人签名授权免验证签 |
| `PersonalSealAuthorizeFreeSignCancel` | `personal-seal-authorize-free-sign-cancel` | 个人签名免验证签解除 |
| `PersonalSealAuthorizeFreeSignDueCancel` | `personal-seal-authorize-free-sign-due-cancel` | 签名免验证签即将到期 |

#### 组织管理事件

| 事件类 | 事件 ID | 说明 |
| --- | --- | --- |
| `OrganizationDeptCreate` | `organization-dept-create` | 部门创建 |
| `OrganizationDeptDelete` | `organization-dept-delete` | 部门删除 |
| `OrganizationDeptModify` | `organization-dept-modify` | 部门信息修改 |
| `OrgMemberCreate` | `organization-member-create` | 成员创建 |
| `OrgMemberDelete` | `organization-member-delete` | 成员删除 |
| `OrgMemberActive` | `organization-member-active` | 成员激活 |
| `OrgMemberDisable` | `organization-member-disable` | 成员禁用 |
| `OrgMemberEnable` | `organization-member-enable` | 成员启用 |
| `OrgMemberModifyDept` | `organization-member-modify-dept` | 成员所属部门修改 |
| `OrgMemberModifyInfo` | `organization-member-modify-info` | 成员基本信息修改 |
| `EntityManage` | `entity-manage` | 成员企业管理 |

#### 模板事件

| 事件类 | 事件 ID | 说明 |
| --- | --- | --- |
| `TemplateCreate` | `template-create` | 模板创建 |
| `TemplateDelete` | `template-delete` | 模板删除 |
| `TemplateEnable` | `template-enable` | 模板启用 |
| `TemplateDisable` | `template-disable` | 模板停用 |

#### 审批事件

| 事件类 | 事件 ID | 说明 |
| --- | --- | --- |
| `ApprovalCreate` | `approval-create` | 审批发起 |
| `ApprovalChange` | `approval-change` | 审批变更 |

#### 其他事件

| 事件类 | 事件 ID | 说明 |
| --- | --- | --- |
| `BillPaid` | `billing-order-payed` | 订单支付完成 |
| `PerformanceRemind` | `performance-remind` | 履约提醒 |
| `FaceRecognition` | `face-recognition` | 人脸核身完成 |
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

## 可选合同模型（FddContract）

本包提供一个**可选的** Eloquent 模型设计方案，用于在宿主应用本地持久化法大大签署任务的状态和事件轨迹。模型为可选组件——不使用它，SDK 的 Service / Event / Callback 机制照常工作；使用它，则获得本地状态追踪能力。

### 数据表设计：`fdd_contracts`

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| `id` | bigint (PK) | 本地主键 |
| `initiator_type` | string | 多态发起方类型（如 `App\Models\User`、`App\Models\Corp`） |
| `initiator_id` | bigint | 多态发起方 ID |
| `sign_task_id` | string (nullable) | 法大大签署任务 ID（创建后回填） |
| `sign_template_id` | string (nullable) | 签署模板 ID |
| `subject` | string | 签署任务主题 / 合同名称 |
| `status` | string | 签署状态枚举（见下文） |
| `actors` | json (nullable) | 参与方列表快照 |
| `options` | json (nullable) | 创建时的扩展参数快照 |
| `last_event` | string (nullable) | 最后一次回调事件名（如 `sign-task-finished`） |
| `last_event_at` | timestamp (nullable) | 最后一次回调时间 |
| `last_event_data` | json (nullable) | 最后一次回调原始 bizContent |
| `finished_at` | timestamp (nullable) | 完成时间 |
| `canceled_at` | timestamp (nullable) | 撤销时间 |
| `abolished_at` | timestamp (nullable) | 作废时间 |
| `expired_at` | timestamp (nullable) | 过期 / 延期时间 |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### 状态枚举

| 常量 | 值 | 说明 |
| --- | --- | --- |
| `STATUS_DRAFT` | `draft` | 草稿（本地创建，尚未提交法大大） |
| `STATUS_PENDING` | `pending` | 待签署（sign-task-created 回调后） |
| `STATUS_SIGNING` | `signing` | 签署中（查询详情发现有人已签署但未全部完成） |
| `STATUS_COMPLETED` | `completed` | 已完成（sign-task-finished） |
| `STATUS_CANCELED` | `canceled` | 已撤销（sign-task-canceled） |
| `STATUS_ABOLISHED` | `abolished` | 已作废（sign-task-abolish） |
| `STATUS_EXPIRED` | `expired` | 已过期（sign-task-extension 触发，或签署截止日已过） |

状态流转：

```
draft → pending → signing → completed
                  ↓
               canceled / abolished / expired
```

### 多态发起方

利用 Laravel 多态关联 `morphTo`，宿主应用中任意模型均可作为发起方：

```php
// FddContract 模型
public function initiator(): MorphTo
{
    return $this->morphTo();
}

// 使用示例
$contract = FddContract::create([
    'initiator_type' => User::class,
    'initiator_id'   => $user->id,
    'subject'        => '采购合同',
    'status'         => 'draft',
]);

$contract->initiator; // → App\Models\User 实例
```

### 事件联动

现有 5 个事件各自携带 `data` 数组，其中包含 `signTaskId`。事件与状态映射关系：

| 事件 | 目标状态 | 时间字段 |
| --- | --- | --- |
| `SignTaskCreated` | `pending` | — |
| `SignTaskExtension` | `expired`（或保持原状态仅记录延期） | `expired_at` |
| `SignTaskFinished` | `completed` | `finished_at` |
| `SignTaskCanceled` | `canceled` | `canceled_at` |
| `SignTaskAbolish` | `abolished` | `abolished_at` |

在宿主应用的 Event Listener 中监听事件并更新合同状态：

```php
use Illuminate\Support\Facades\Event;
use Larva\Fadada\Events\SignTaskFinished;

Event::listen(SignTaskFinished::class, function (SignTaskFinished $event) {
    FddContract::where('sign_task_id', $event->data['signTaskId'])
        ->update([
            'status'          => FddContract::STATUS_COMPLETED,
            'last_event'      => 'sign-task-finished',
            'last_event_at'   => now(),
            'last_event_data' => $event->data,
            'finished_at'     => now(),
        ]);
});
```

### 模型关键方法

```php
class FddContract extends Model
{
    // 事件 → 状态映射
    const EVENT_STATUS_MAP = [
        'sign-task-created'   => self::STATUS_PENDING,
        'sign-task-finished'  => self::STATUS_COMPLETED,
        'sign-task-canceled'  => self::STATUS_CANCELED,
        'sign-task-abolish'   => self::STATUS_ABOLISHED,
        'sign-task-extension' => self::STATUS_EXPIRED,
    ];

    // 多态关联
    public function initiator(): MorphTo;

    // 状态查询作用域
    public function scopePending(Builder $q): Builder;
    public function scopeCompleted(Builder $q): Builder;
    public function scopeActive(Builder $q): Builder; // 未完成且未终止

    // 从回调事件更新状态
    public static function updateFromEvent(string $event, array $data): ?static;
}
```

### 文件结构

```
src/
├── Models/
│   └── FddContract.php          # 合同模型（含多态关联、状态作用域、事件映射常量）
├── Database/
│   └── Migrations/
│       └── create_fdd_contracts_table.php  # 迁移文件
```

### 可选性保障

- 模型不依赖任何 Service，Service 也不依赖模型
- 迁移文件通过 `publishes` 发布，宿主应用自行决定是否运行
- 不修改现有 `FadadaServiceProvider` 的注册逻辑
- 在 `FadadaServiceProvider::boot()` 中增加可选的迁移发布标签 `fadada-migrations`

## 协议

MIT
