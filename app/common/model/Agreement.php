<?php
declare (strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Agreement extends Model
{
    // 状态
    const STATUS_NORMAL = 0; // 正常
    const STATUS_DELETED = 1; // 删除
    const STATUS_FORBIDDEN = 2; // 禁用
}
