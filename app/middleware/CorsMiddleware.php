<?php

namespace app\middleware;

class CorsMiddleware {
    public function handle($request, \Closure $next) {
        // 允许的来源
        $origin = $request->header('origin') ?? '*';

        $response = $next($request);

        $response->header([
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Methods'     => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With',
            'Access-Control-Allow-Credentials' => 'true',
        ]);

        // 处理 OPTIONS 预检请求
        if ($request->method(true) === 'OPTIONS') {
            return response('', 204);
        }

        return $response;
    }
}