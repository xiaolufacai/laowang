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

class Wechat {

    public function callback() {
        var_dump(runtime_path() . 'wechat.log');
    }
}