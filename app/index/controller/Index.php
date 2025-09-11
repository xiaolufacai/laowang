<?php

namespace app\index\controller;


use app\common\model\User;
use app\IndexBaseController;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Session;
use think\facade\View;
use think\Request;
use think\response\Json;

class Index {

    /**
     * @param Request $request
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function user(Request $request) {
        $user = User::where(['id' => $request->user['uid']])->find();
        // 判断会员是否到期
        $vipTime = $user['vip_time'];
        $user['vip_status'] = (strtotime($user['vip_time']) >= time()) ? 1 : 0;
        return \json(['code' => 1, 'message' => 'OK', 'data' => ['user' => $user]]);
    }
}