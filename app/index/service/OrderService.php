<?php
declare(strict_types=1);

namespace app\index\service;

use app\common\model\Order;
use app\common\model\AppVip;
use think\facade\Db;

/**
 * 订单服务 - 前端APP接口
 */
class OrderService {

    /**
     * 用户下单
     *
     * @param int $userId 用户ID
     * @param int $vipId  会员类型ID（app_vip表的ID）
     * @param int $appId  包ID
     * @return array
     */
    public static function setOrder(int $userId, int $vipId, int $appId): array {
        // 查询会员价格信息
        $vipInfo = AppVip::where('id', $vipId)
            ->where('app_id', $appId)
            ->find();

        if (!$vipInfo) {
            return ['error' => 1, 'message' => '会员类型不存在'];
        }

        // 获取会员实际金额和原始金额
        $amountData = VipService::amount($vipId);
        if ($amountData['error'] !== 0) {
            return ['error' => 1, 'message' => $amountData['message']];
        }

        // 生成订单号
        $orderNo = self::generateOrderNo();

        // 创建订单
        $order                  = new Order();
        $order->user_id         = $userId;
        $order->app_id          = $appId;
        $order->vip_id          = $vipId;
        $order->order_no        = $orderNo;
        $order->amount          = $amountData['data']['amount'];
        $order->original_amount = $amountData['data']['original_amount'];
        $order->pay_status      = Order::PAY_STATUS_PENDING;
        $order->status          = Order::STATUS_NORMAL;
        $order->subject         = '会员开通';

        if ($order->save()) {
            return [
                'error'   => 0,
                'message' => '订单创建成功',
                'data'    => [
                    'order_id'        => $order->id,
                    'order_no'        => $orderNo,
                    'amount'          => $amountData['data']['amount'],
                    'original_amount' => $amountData['data']['original_amount'],
                    'vip_type'        => $vipInfo->vip,
                ]
            ];
        }

        return ['error' => 1, 'message' => '订单创建失败'];
    }

    /**
     * 用户订单列表
     *
     * @param int      $userId   用户ID
     * @param int      $appId    包ID
     * @param int      $page     当前页
     * @param int      $pageSize 每页条数
     * @param int|null $status   支付状态
     * @return array
     */
    public static function orders(int $userId, int $appId, int $page = 1, int $pageSize = 10, ?int $status = null): array {
        $query = Order::where('user_id', $userId)
            ->where('app_id', $appId)
            ->where('status', Order::STATUS_NORMAL);

        if ($status !== null) {
            $query->where('pay_status', $status);
        }

        $orders = $query->order('create_time', 'desc')
            ->paginate($pageSize, false, ['page' => $page]);

        $list = [];
        foreach ($orders->items() as $order) {
            $list[] = [
                'id'              => $order->id,
                'order_no'        => $order->order_no,
                'amount'          => $order->amount,
                'original_amount' => $order->original_amount,
                'pay_status'      => $order->pay_status,
                'pay_status_text' => Order::getPayStatusText($order->pay_status),
                'pay_type'        => $order->pay_type,
                'pay_type_text'   => Order::getPayTypeText($order->pay_type),
                'create_time'     => $order->create_time,
                'pay_time'        => $order->pay_time,
            ];
        }

        return [
            'data'         => $list,
            'total'        => $orders->total(),
            'current_page' => $orders->currentPage(),
            'last_page'    => $orders->lastPage(),
        ];
    }

    /**
     * 用户订单详情
     *
     * @param int $userId  用户ID
     * @param int $orderId 订单ID
     * @return array
     */
    public static function detail(int $userId, int $orderId): array {
        $order = Order::where('id', $orderId)
            ->where('user_id', $userId)
            ->where('status', Order::STATUS_NORMAL)
            ->find();

        if (!$order) {
            return ['error' => 1, 'message' => '订单不存在'];
        }

        // 查询会员信息
        $vipInfo = AppVip::where('id', $order->vip_id)->find();

        return [
            'error'   => 0,
            'message' => '',
            'data'    => [
                'id'              => $order->id,
                'order_no'        => $order->order_no,
                'amount'          => $order->amount,
                'original_amount' => $order->original_amount,
                'pay_status'      => $order->pay_status,
                'pay_status_text' => Order::getPayStatusText($order->pay_status),
                'pay_type'        => $order->pay_type,
                'pay_type_text'   => Order::getPayTypeText($order->pay_type),
                'create_time'     => $order->create_time,
                'pay_time'        => $order->pay_time,
                'vip_info'        => $vipInfo ? [
                    'vip'       => $vipInfo->vip,
                    'old_price' => $vipInfo->old_price,
                    'new_price' => $vipInfo->new_price,
                ] : null,
            ]
        ];
    }

    /**
     * 用户取消订单
     *
     * @param int $userId  用户ID
     * @param int $orderId 订单ID
     * @return array
     */
    public static function cancel(int $userId, int $orderId): array {
        $order = Order::where('id', $orderId)
            ->where('user_id', $userId)
            ->where('status', Order::STATUS_NORMAL)
            ->find();

        if (!$order) {
            return ['error' => 1, 'message' => '订单不存在'];
        }

        // 只能取消待支付的订单
        if ($order->pay_status !== Order::PAY_STATUS_PENDING) {
            return ['error' => 1, 'message' => '只能取消待支付的订单'];
        }

        $order->pay_status  = Order::PAY_STATUS_CANCELLED;
        $order->update_time = date('Y-m-d H:i:s');

        if ($order->save()) {
            return ['error' => 0, 'message' => '订单取消成功'];
        }

        return ['error' => 1, 'message' => '订单取消失败'];
    }

    /**
     * 生成订单号
     *
     * @return string
     */
    private static function generateOrderNo(): string {
        return date('YmdHis') . str_pad((string)mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }
}