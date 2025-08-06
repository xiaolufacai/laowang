<?php
declare (strict_types=1);

namespace app\index\controller;


use app\Request;
use think\facade\View;
use think\response\Json;

class User {

    public function users(Request $request): Json {
        // 获取查询参数
        $appId    = $request->get('app_id', null);
        $id       = $request->get('order_id', null);
        $page     = $request->get('page', 1);
        $pageSize = $request->get('page_size', 10);
        // 调用 Service 获取订单数据
        $result = UserService::users($appId, $id, $page, $pageSize);

        // 返回响应
        return json(['code' => 0, 'data' => $result]);
    }
}
