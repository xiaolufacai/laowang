<?php

namespace app\index\controller;


use app\index\service\UserService;
use app\IndexBaseController;
use app\middleware\JWTAuthMiddleware;
use Firebase\JWT\JWT;
use think\facade\Request;
use think\response\Json;

class Login
{

    /**
     * 登录
     *
     * @return Json
     */
    public function login(): Json
    {
        $res = UserService::create();
        if ($res['error']) {
            // 返回生成的 token
            return \json(['code' => 1, 'message' => $res['message'], 'data' => $res]);
        }
        $jwt = UserService::token($res['data']['uid']);
        return \json(['code' => 1, 'message' => $res['message'], 'data' => ['token' => $jwt]]);
    }
}
