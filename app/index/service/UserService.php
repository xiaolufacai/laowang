<?php

namespace app\index\service;


use app\middleware\JWTAuthMiddleware;
use Firebase\JWT\JWT;
use think\db\exception\DbException;
use think\facade\Db;

class UserService
{
    /**
     * 生成token
     *
     * @param $clientId
     * @return string
     */
    public static function token($clientId): string
    {
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
        return JWT::encode($payload, JWTAuthMiddleware::KEY, 'HS256');
    }
}
