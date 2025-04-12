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
        // 获取设备ID
        $clientId = Request::param('client_id');
        if (!$clientId) {
            return \json(['code' => 1, 'message' => '客户端错误', 'data' => (object)[]]);
        }

        $jwt = UserService::token($clientId);
        // 返回生成的 token
        return \json(['code' => 0, 'message' => 'OK', 'data' => ['token' => $jwt]]);
    }
}
