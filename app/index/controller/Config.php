<?php

namespace app\index\controller;

use app\common\services\VivoService;
use think\App;
use think\facade\Request;
use think\Response;

class Config {

    /**
     * 协议
     *
     */
    public function agreement(Request $request): Response {
        $pkgName = Request::param('pkgName', '');
        // 所有协议
        $agreements = [
            'com.yichunchihang.sjbj' => [
                'privacy_policy' => 'https://api.yichunchihang.com/storage/home/hc-privacy_policy.html',
                'user_policy'    => 'https://api.yichunchihang.com/storage/home/hc-user_policy.html',
            ]
        ];

        return json([
            'code' => 0,
            'msg'  => 'OK',
            'data' => [
                'optionsInfo' => $agreements[$pkgName] ?? [],
                'extraInfo'   => [
                    'auditStatus' => 1,
                ]
            ],
        ]);
    }
}