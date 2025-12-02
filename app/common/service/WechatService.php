<?php

namespace app\common\service;

class WechatService {

    /**
     *  获取所有微信APP ID
     *
     * @return mixed
     */
    public static function apps() {
        return config('wechat.APPS');
    }
}