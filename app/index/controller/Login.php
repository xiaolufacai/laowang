<?php

namespace app\index\controller;


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

        // 生成 JWT token
        $issuedAt = time();
        $expirationTime = $issuedAt + 7 * 24 * 60 * 60;  // 7天后过期
        $payload = [
            'iss'       => 'laowang-publisher-issuer',      // 发行者
            'aud'       => 'laowang-user-audience',    // 用户
            'iat'       => $issuedAt,          // 发布时间
            'exp'       => $expirationTime,    // 过期时间
            'client_id' => $clientId,    // 用户的设备ID
        ];

        // 使用 HS256 算法生成 JWT token
        $jwt = JWT::encode($payload, JWTAuthMiddleware::KEY, 'HS256');

        // 返回生成的 token
        return \json(['code' => 0, 'message' => 'OK', 'data' => ['token' => $jwt]]);
    }
}
