<?php
declare(strict_types = 1);

namespace app\index\controller;

use app\IndexBaseController;
use app\index\service\OrderService;
use app\Request;
use think\response\Json;

/**
 * 订单控制器 - 前端APP接口
 */
class Order extends IndexBaseController {

    /**
     * 用户下单
     *
     * @param Request $request
     * @return Json
     */
    public function setOrder(Request $request): Json {
        $userId = $request->userId;
        $vipId  = $request->post('vip_id');

        if (empty($userId)) {
            return json(['code' => -1, 'message' => '用户未登录']);
        }

        if (empty($vipId)) {
            return json(['code' => -1, 'message' => '请选择会员类型']);
        }

        $result = OrderService::setOrder($userId, $vipId, $this->id);

        if ($result['error'] === 0) {
            return json(['code' => 0, 'data' => $result['data'], 'message' => '订单创建成功']);
        }

        return json(['code' => -1, 'message' => $result['message']]);
    }

    /**
     * 用户订单列表
     *
     * @param Request $request
     * @return Json
     */
    public function orders(Request $request): Json {
        $userId   = $request->userId;
        $page     = $request->get('page', 1);
        $pageSize = $request->get('page_size', 10);
        $status   = $request->get('status', null);

        if (empty($userId)) {
            return json(['code' => -1, 'message' => '用户未登录']);
        }

        $result = OrderService::orders($userId, $this->id, $page, $pageSize, $status);

        return json(['code' => 0, 'data' => $result]);
    }

    /**
     * 用户订单详情
     *
     * @param Request $request
     * @return Json
     */
    public function detail(Request $request): Json {
        $userId  = $request->userId;
        $orderId = $request->get('order_id');

        if (empty($userId)) {
            return json(['code' => -1, 'message' => '用户未登录']);
        }

        if (empty($orderId)) {
            return json(['code' => -1, 'message' => '订单ID不能为空']);
        }

        $result = OrderService::detail($userId, $orderId);

        if ($result['error'] === 0) {
            return json(['code' => 0, 'data' => $result['data']]);
        }

        return json(['code' => -1, 'message' => $result['message']]);
    }

    /**
     * 用户取消订单
     *
     * @param Request $request
     * @return Json
     */
    public function cancel(Request $request): Json {
        $userId  = $request->userId;
        $orderId = $request->post('order_id');

        if (empty($userId)) {
            return json(['code' => -1, 'message' => '用户未登录']);
        }

        if (empty($orderId)) {
            return json(['code' => -1, 'message' => '订单ID不能为空']);
        }

        $result = OrderService::cancel($userId, $orderId);

        if ($result['error'] === 0) {
            return json(['code' => 0, 'message' => '订单取消成功']);
        }

        return json(['code' => -1, 'message' => $result['message']]);
    }
}