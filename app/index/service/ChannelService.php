<?php

namespace app\index\service;

use app\common\model\AppChannel;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;

class ChannelService {

    /**
     *  根据渠道获取渠道信息
     *
     * @param $channel
     * @return AppChannel|array|Model|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getChannelByApp($channel, $appId): AppChannel|array|Model|null {
        return AppChannel::where(['channel' => $channel, 'app_id' => $appId])->find();
    }
}