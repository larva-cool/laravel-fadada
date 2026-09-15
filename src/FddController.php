<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 */

namespace Larva\Fadada;

use Illuminate\Http\Request;
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

            return response(Fadada::callback()->successResponse(), 200);
        } catch (\RuntimeException $e) {
            // 验签或时间戳校验失败，返回非 success
            return response(['msg' => 'fail'], 200);
        }
    }
}