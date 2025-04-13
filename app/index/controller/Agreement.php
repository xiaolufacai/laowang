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

class Agreement
{
    /**
     * @param Request $request
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function agreement(Request $request)
    {
        $appId = $request->param('app_id');
        $agreement = \app\common\model\Agreement::where(['app_id' => $appId])->find();
        return \json(['code' => 1, 'message' => 'OK', 'data' => ['agreement' => $agreement]]);
    }
}