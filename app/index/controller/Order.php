<?php
declare (strict_types=1);

namespace app\admin\controller;


use app\admin\service\OrderService;
use app\AdminBaseController;
use app\Request;
use think\facade\Config;
use think\facade\View;
use think\response\Json;

class Order extends AdminBaseController
{
    /**
     * App 列表
     *
     * @return string
     */
    public function index()
    {
        return View::fetch();
    }

    public function orders(Request $request): Json
    {
        // 获取查询参数
        $appId    = $request->get('app_id', null);
        $id       = $request->get('order_id', null);
        $page     = $request->get('page', 1);
        $pageSize = $request->get('page_size', 10);
        // 调用 Service 获取订单数据
        $result = OrderService::orders($appId, $id, $page, $pageSize);

        if (!empty($result['data'])) {
            // 处理 支付平台 字段
            $paymentMethods = Config::get('app.paymentMethods');
            foreach ($result['data'] as &$item) {
                $item['pay_platform'] = $paymentMethods[$item['platform']];
            }
        }
        // 返回响应
        return json(['code' => 0, 'data' => $result]);
    }
}
