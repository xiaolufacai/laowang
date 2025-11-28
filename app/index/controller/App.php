<?php

namespace app\index\controller;


use think\response\Json;
use \app\common\model\App as AppModel;

class App {

    /**
     * 登录
     *
     * @return Json
     */
    public function apps(): Json {
        $data = AppModel::where(['status' => AppModel::STATUS_NORMAL])->field(['project', 'description'])->order('id desc')->select()->toArray();
        return \json(['code' => 1, 'message' => 'OK', 'data' => ['apps' => $data]]);
    }
}
