<?php
// app/middleware/JWTAuthMiddleware.php

namespace app\middleware;

use Closure;
use think\facade\Request;
use Firebase\JWT\JWT;
use think\exception\ValidateException;

class JWTAuthMiddleware
{
    // 秘钥，用于生成和验证 JWT
    const KEY = 'project-lao-wang'; // 你可以自定义秘钥

    public function handle($request, Closure $next)
    {
        var_dump(234230);die();
        // 获取请求头中的 token
        $token = Request::header('Authorization');
        if (!$token) {
            throw new ValidateException('Token is required');
        }

        // 去掉 token 前面的 Bearer 字符串
        $token = str_replace('Bearer ', '', $token);

        try {
            // 解码 JWT
            $headers = ['HS256'];
            $decoded = JWT::decode($token, self::KEY, $headers);
            // 将解码后的信息放入请求中，方便后续使用
            $request->user = (array) $decoded;
        } catch (\Exception $e) {
            throw new ValidateException('Invalid token');
        }

        return $next($request);
    }
}
