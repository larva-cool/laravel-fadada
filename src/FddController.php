<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 */

namespace Larva\Fadada;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Larva\Fadada\Events\ApprovalChange;
use Larva\Fadada\Events\ApprovalCreate;
use Larva\Fadada\Events\BillPaid;
use Larva\Fadada\Events\CorpAuthorize;
use Larva\Fadada\Events\CorpCancelAuthorization;
use Larva\Fadada\Events\EntityManage;
use Larva\Fadada\Events\FaceRecognition;
use Larva\Fadada\Events\FddEvent;
use Larva\Fadada\Events\OrgMemberActive;
use Larva\Fadada\Events\OrgMemberCreate;
use Larva\Fadada\Events\OrgMemberDelete;
use Larva\Fadada\Events\OrgMemberDisable;
use Larva\Fadada\Events\OrgMemberEnable;
use Larva\Fadada\Events\OrgMemberModifyDept;
use Larva\Fadada\Events\OrgMemberModifyInfo;
use Larva\Fadada\Events\OrganizationDeptCreate;
use Larva\Fadada\Events\OrganizationDeptDelete;
use Larva\Fadada\Events\OrganizationDeptModify;
use Larva\Fadada\Events\PerformanceRemind;
use Larva\Fadada\Events\PersonalSealAuthorizeFreeSign;
use Larva\Fadada\Events\PersonalSealAuthorizeFreeSignCancel;
use Larva\Fadada\Events\PersonalSealAuthorizeFreeSignDueCancel;
use Larva\Fadada\Events\PersonalSealCreate;
use Larva\Fadada\Events\PersonalSealDelete;
use Larva\Fadada\Events\SealAuthorizeFreeSign;
use Larva\Fadada\Events\SealAuthorizeFreeSignCancel;
use Larva\Fadada\Events\SealAuthorizeFreeSignDueCancel;
use Larva\Fadada\Events\SealAuthorizeMember;
use Larva\Fadada\Events\SealAuthorizeMemberCancel;
use Larva\Fadada\Events\SealCancellation;
use Larva\Fadada\Events\SealCreate;
use Larva\Fadada\Events\SealDelete;
use Larva\Fadada\Events\SealDisable;
use Larva\Fadada\Events\SealEnable;
use Larva\Fadada\Events\SealModifyInfo;
use Larva\Fadada\Events\SealVerifyCancel;
use Larva\Fadada\Events\SealVerifyFailed;
use Larva\Fadada\Events\SealVerifySuccessed;
use Larva\Fadada\Events\SignTaskAbolish;
use Larva\Fadada\Events\SignTaskCanceled;
use Larva\Fadada\Events\SignTaskCreated;
use Larva\Fadada\Events\SignTaskDownload;
use Larva\Fadada\Events\SignTaskExpire;
use Larva\Fadada\Events\SignTaskExtension;
use Larva\Fadada\Events\SignTaskFillRejected;
use Larva\Fadada\Events\SignTaskFilled;
use Larva\Fadada\Events\SignTaskFinalize;
use Larva\Fadada\Events\SignTaskFinished;
use Larva\Fadada\Events\SignTaskIgnore;
use Larva\Fadada\Events\SignTaskJoinFailed;
use Larva\Fadada\Events\SignTaskJoined;
use Larva\Fadada\Events\SignTaskPending;
use Larva\Fadada\Events\SignTaskRead;
use Larva\Fadada\Events\SignTaskSignFailed;
use Larva\Fadada\Events\SignTaskSignRejected;
use Larva\Fadada\Events\SignTaskSigned;
use Larva\Fadada\Events\SignTaskStart;
use Larva\Fadada\Events\TemplateCreate;
use Larva\Fadada\Events\TemplateDelete;
use Larva\Fadada\Events\TemplateDisable;
use Larva\Fadada\Events\TemplateEnable;
use Larva\Fadada\Events\UserAuthorize;
use Larva\Fadada\Events\UserCancelAuthorization;
use Larva\Fadada\Events\UserFourElementVerify;
use Larva\Fadada\Events\UserThreeElementVerify;
use Larva\Fadada\Facades\Fadada;

/**
 * 法大大回调
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class FddController
{
    /**
     * 事件 ID 与事件类的映射表。
     *
     * 回调到达后根据 X-FASC-Event 头中的事件 ID 查找对应的事件类并派发。
     * 未在映射表中的事件将使用兜底的 FddEvent 类派发。
     */
    protected array $eventMap = [
        // 签署任务事件
        'sign-task-created'           => SignTaskCreated::class,
        'sign-task-start'             => SignTaskStart::class,
        'sign-task-signed'             => SignTaskSigned::class,
        'sign-task-filled'             => SignTaskFilled::class,
        'sign-task-fill-rejected'      => SignTaskFillRejected::class,
        'sign-task-finalize'           => SignTaskFinalize::class,
        'sign-task-read'               => SignTaskRead::class,
        'sign-task-joined'             => SignTaskJoined::class,
        'sign-task-join-failed'        => SignTaskJoinFailed::class,
        'sign-task-sign-failed'        => SignTaskSignFailed::class,
        'sign-task-sign-rejected'      => SignTaskSignRejected::class,
        'sign-task-ignore'             => SignTaskIgnore::class,
        'sign-task-pending'            => SignTaskPending::class,
        'sign-task-download'           => SignTaskDownload::class,
        'sign-task-extension'          => SignTaskExtension::class,
        'sign-task-finished'           => SignTaskFinished::class,
        'sign-task-canceled'           => SignTaskCanceled::class,
        'sign-task-abolish'            => SignTaskAbolish::class,
        'sign-task-expire'             => SignTaskExpire::class,

        // 认证授权事件
        'user-authorize'               => UserAuthorize::class,
        'corp-authorize'               => CorpAuthorize::class,
        'user-cancel-authorization'    => UserCancelAuthorization::class,
        'corp-cancel-authorization'    => CorpCancelAuthorization::class,
        'user-three-element-verify'    => UserThreeElementVerify::class,
        'user-four-element-verify'     => UserFourElementVerify::class,

        // 印章管理事件
        'seal-create'                  => SealCreate::class,
        'seal-delete'                  => SealDelete::class,
        'seal-enable'                  => SealEnable::class,
        'seal-disable'                 => SealDisable::class,
        'seal-modify-info'             => SealModifyInfo::class,
        'seal-cancellation'            => SealCancellation::class,
        'seal-authorize-member'        => SealAuthorizeMember::class,
        'seal-authorize-member-cancel' => SealAuthorizeMemberCancel::class,
        'seal-authorize-free-sign'      => SealAuthorizeFreeSign::class,
        'seal-authorize-free-sign-cancel' => SealAuthorizeFreeSignCancel::class,
        'seal-authorize-free-sign-due-cancel' => SealAuthorizeFreeSignDueCancel::class,
        'seal-verify-successed'        => SealVerifySuccessed::class,
        'seal-verify-failed'           => SealVerifyFailed::class,
        'seal-verify-cancel'           => SealVerifyCancel::class,

        // 个人签名事件
        'personal-seal-create'         => PersonalSealCreate::class,
        'personal-seal-delete'         => PersonalSealDelete::class,
        'personal-seal-authorize-free-sign' => PersonalSealAuthorizeFreeSign::class,
        'personal-seal-authorize-free-sign-cancel' => PersonalSealAuthorizeFreeSignCancel::class,
        'personal-seal-authorize-free-sign-due-cancel' => PersonalSealAuthorizeFreeSignDueCancel::class,

        // 组织管理事件
        'organization-dept-create'     => OrganizationDeptCreate::class,
        'organization-dept-delete'     => OrganizationDeptDelete::class,
        'organization-dept-modify'     => OrganizationDeptModify::class,
        'organization-member-create'   => OrgMemberCreate::class,
        'organization-member-delete'   => OrgMemberDelete::class,
        'organization-member-active'   => OrgMemberActive::class,
        'organization-member-disable'  => OrgMemberDisable::class,
        'organization-member-enable'   => OrgMemberEnable::class,
        'organization-member-modify-dept' => OrgMemberModifyDept::class,
        'organization-member-modify-info' => OrgMemberModifyInfo::class,
        'entity-manage'                => EntityManage::class,

        // 模板事件
        'template-create'              => TemplateCreate::class,
        'template-delete'              => TemplateDelete::class,
        'template-enable'              => TemplateEnable::class,
        'template-disable'             => TemplateDisable::class,

        // 审批事件
        'approval-create'              => ApprovalCreate::class,
        'approval-change'              => ApprovalChange::class,

        // 账单事件
        'billing-order-payed'          => BillPaid::class,

        // 归档履约事件
        'performance-remind'           => PerformanceRemind::class,

        // 人脸核身事件
        'face-recognition'             => FaceRecognition::class,
    ];

    /**
     * 回调处理
     * @param  Request  $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function callback(Request $request)
    {
        try {
            $result = Fadada::callback()->handleCallback(
                $request->headers->all(),
                $request->input('bizContent', '')
            );
            // $result['event']   => 事件ID，如 sign-task-finished
            // $result['data']    => bizContent 解码后的业务数据

            $eventClass = $this->eventMap[$result['event']] ?? FddEvent::class;
            Event::dispatch(new $eventClass($result['data']));

            return response(Fadada::callback()->successResponse(), 200)
                ->header('Content-Type', 'application/json');
        } catch (\RuntimeException $e) {
            // 验签或时间戳校验失败，返回非 success
            return response()->json(['msg' => 'fail'], 200);
        }
    }
}
