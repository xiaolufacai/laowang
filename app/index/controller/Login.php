<?php

namespace app\index\controller;


use app\IndexBaseController;
use app\middleware\JWTAuthMiddleware;
use Firebase\JWT\JWT;
use think\captcha\facade\Captcha;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Session;
use think\facade\Request;
use think\facade\Validate;
use think\response\Json;

class Login extends IndexBaseController
{

    /**
     * 登录
     *
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
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
            'iss'       => 'your-issuer',      // 发行者
            'aud'       => 'your-audience',    // 用户
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
