<?php
declare (strict_types=1);

namespace app\common\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Configure extends Model {
    protected $table = 'info_config';
    const TABLE_NAME = 'info_config';
    protected $pk = 'id';
    // 状态
    const STATUS_NORMAL    = 0; // 正常
    const STATUS_FORBIDDEN = 1; // 禁用
}
