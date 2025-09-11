<?php

namespace app\index\service;

use app\common\model\Configure;
use think\model\Collection;

class ConfigService {

    /**
     *  获取配置
     *
     * @return \think\Collection|Collection
     */
    public static function configs() {
        return Configure::where(['status' => Configure::STATUS_NORMAL])->select();
    }
}