<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class AppChannel extends Model
{
    // 状态
    const STATUS_WAITING = 0; // 审核中
    const STATUS_PASS = 1; // 审核通过
}
