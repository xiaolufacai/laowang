<?php

namespace app\index\service;

use app\common\model\AppVip;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;
use think\model\Collection;

class VipService {

    /**
     *  查询包VIP信息
     *
     * @param $appId
     * @return \think\Collection|Collection
     */
    public static function appVips($appId): Collection|\think\Collection {
        return AppVip::where(['app_id' => $appId])->select();
    }
}
