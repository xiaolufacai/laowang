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
        $file = runtime_path() . 'wechat.log';
        file_put_contents($file, json_encode($_REQUEST), FILE_APPEND);
    }
}