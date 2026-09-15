<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 */

namespace Larva\Fadada;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Larva\Fadada\Events\FddEvent;
use Larva\Fadada\Events\SignTaskAbolish;
use Larva\Fadada\Events\SignTaskCanceled;
use Larva\Fadada\Events\SignTaskCreated;
use Larva\Fadada\Events\SignTaskExtension;
use Larva\Fadada\Events\SignTaskFinished;
use Larva\Fadada\Facades\Fadada;

/**
 * 法大大回调
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class FddController
{
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

            switch ($result['event']) {
                case 'sign-task-created'://签署任务创建事件
                    Event::dispatch(new SignTaskCreated($result['data']));
                    break;
                case 'sign-task-canceled'://签署任务撤销
                    Event::dispatch(new SignTaskCanceled($result['data']));
                    break;
                case 'sign-task-extension':// 签署任务延期
                    Event::dispatch(new SignTaskExtension($result['data']));
                    break;
                case 'sign-task-finished'://签署任务已经完成
                    Event::dispatch(new SignTaskFinished($result['data']));
                    break;
                case 'sign-task-abolish'://签署任务作废
                    Event::dispatch(new SignTaskAbolish($result['data']));
                    break;
                default:
                    Event::dispatch(new FddEvent($result['data']));
                    break;
            }

            return response(Fadada::callback()->successResponse(), 200)
                ->header('Content-Type', 'application/json');
        } catch (\RuntimeException $e) {
            // 验签或时间戳校验失败，返回非 success
            return response()->json(['msg' => 'fail'], 200);
        }
    }
}