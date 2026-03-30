<?php
declare (strict_types=1);

namespace app\admin\controller;

use app\admin\service\OrderService;
use app\AdminBaseController;
use app\Request;
use think\facade\Config;
use think\facade\View;
use think\response\Json;

/**
 * 订单控制器 - 后台管理
 */
class Order extends AdminBaseController {

    /**
     * 订单列表页面
     *
     * @return string
     */
    public function index() {
        return View::fetch();
    }

    /**
     * 订单详情页面
     *
     * @return string
     */
    public function detail() {
        return View::fetch();
    }

    /**
     * 获取订单列表
     *
     * @param Request $request
     * @return Json
     */
    public function orders(Request $request): Json {
        // 获取查询参数
        $appId    = $request->get('app_id', null);
        $orderId  = $request->get('order_id', null);
        $payType  = $request->get('pay_type', null);
        $payStatus = $request->get('pay_status', null);
        $startTime = $request->get('start_time', null);
        $endTime   = $request->get('end_time', null);
        $page     = $request->get('page', 1);
        $pageSize = $request->get('page_size', 10);

        // 调用 Service 获取订单数据
        $result = OrderService::orders($appId, $orderId, $payType, $payStatus, $startTime, $endTime, $page, $pageSize);

        if (!empty($result['data'])) {
            // 处理支付平台字段和支付状态文本
            $paymentMethods = Config::get('app.paymentMethods');
            foreach ($result['data'] as &$item) {
                $item['pay_platform'] = $paymentMethods[$item['platform']] ?? '未知';
                $item['pay_status_text'] = \app\common\model\Order::getPayStatusText($item['pay_status']);
                $item['pay_type_text'] = \app\common\model\Order::getPayTypeText($item['pay_type']);
            }
        }

        // 返回响应
        return json(['code' => 0, 'data' => $result]);
    }

    /**
     * 获取订单详情
     *
     * @param Request $request
     * @return Json
     */
    public function getOrderDetail(Request $request): Json {
        $orderId = $request->get('order_id');

        if (empty($orderId)) {
            return json(['code' => -1, 'message' => '订单ID不能为空']);
        }

        $result = OrderService::detail($orderId);

        if ($result['error'] === 0) {
            return json(['code' => 0, 'data' => $result['data']]);
        }

        return json(['code' => -1, 'message' => $result['message']]);
    }

    /**
     * 订单操作日志
     *
     * @param Request $request
     * @return Json
     */
    public function logs(Request $request): Json {
        $orderId = $request->get('order_id');

        if (empty($orderId)) {
            return json(['code' => -1, 'message' => '订单ID不能为空']);
        }

        $logs = OrderService::logs($orderId);

        return json(['code' => 0, 'data' => $logs]);
    }
}
