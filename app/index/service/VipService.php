<?php

namespace app\index\service;

use app\common\model\AppVip;
use think\model\Collection;

class VipService {

    /**
     * @return \think\Collection|Collection
     */
    public static function vips(): Collection|\think\Collection {
        return AppVip::select();
    }
}
