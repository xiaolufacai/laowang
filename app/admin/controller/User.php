<?php
declare (strict_types=1);

namespace app\admin\controller;


use app\admin\service\UserService;
use app\AdminBaseController;
use app\Request;
use think\facade\View;
use think\response\Json;

class User extends AdminBaseController {
    /**
     * 用户 列表
     *
     * @return string
     */
    public function index(): string {
        View::assign([
            'nav' => '用户中心',
        ]);
        return View::fetch();
    }

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
