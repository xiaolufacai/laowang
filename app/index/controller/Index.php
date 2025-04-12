<?php
namespace app\index\controller;


use app\common\model\User;
use app\IndexBaseController;
use think\facade\Session;
use think\facade\View;
use think\Request;

class Index
{

    public function index(Request $request)
    {
        var_dump($request->user);
    }

    public function user(Request $request)
    {
        $user = User::where(['id' => $request->user['uid']]);
        return \json(['code' => 1, 'message' => 'OK', 'data' => ['user' => $user]]);
    }
}