<?php
declare (strict_types=1);

namespace app\middleware;

use think\Request;
use think\Response;

class CorsMiddleware {
    /**
     * 处理请求
     *
     * @param Request $request
     * @param \Closure $next
     * @return Response
     */
    public function handle($request, \Closure $next) {
        $response = $next($request);
        $response->header([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET,POST,PUT,DELETE,OPTIONS',
            'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Authorization',
            'Access-Control-Allow-Credentials' => 'true',
        ]);

        // 处理 OPTIONS 预检请求直接返回 204
        if ($request->isOptions()) {
            $response->code(204);
            $response->send();
            exit;
        }

        return $response;
    }
}
