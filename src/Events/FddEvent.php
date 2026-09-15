<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 */

namespace Larva\Fadada\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * 签署任务事件
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class FddEvent
{
    use Dispatchable, SerializesModels;

    public array $data;

    /**
     * Create a new event instance.
     *
     * @param  array  $data  解码后的 bizContent 数据
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }
}