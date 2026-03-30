<?php
declare (strict_types=1);

namespace app\admin\service;

use app\common\model\Order;
use think\db\exception\DbException;
use think\facade\Db;

/**
 * 订单服务 - 后台管理
 */
class OrderService {

    /**
     * 查询订单列表
     *
     * @param int|null $appId      包ID
     * @param int|null $orderId    订单ID
     * @param int|null $payType    支付类型 1:微信 2:支付宝
     * @param int|null $payStatus  支付状态 0:待支付 1:已支付 2:已取消 3:已完成
     * @param string|null $startTime 开始时间
     * @param string|null $endTime   结束时间
     * @param int      $page        当前页数
     * @param int      $pageSize    每页条数
     *
     * @return array 查询结果，包含分页数据和总数
     * @throws DbException
     */
    public static function orders($appId, $orderId, $payType = null, $payStatus = null, $startTime = null, $endTime = null, $page = 1, $pageSize = 10): array {
        // 基本查询构建
        $query = Db::name('orders');

        // 根据传入的 app_id 添加查询条件
        if ($appId) {
            $query->where('app_id', $appId);
        }

        // 根据订单ID查询
        if ($orderId) {
            $query->where('id', $orderId);
        }

        // 根据支付类型查询
        if ($payType !== null) {
            $query->where('pay_type', $payType);
        }

        // 根据支付状态查询
        if ($payStatus !== null) {
            $query->where('pay_status', $payStatus);
        }

        // 根据时间范围查询
        if ($startTime) {
            $query->where('create_time', '>=', $startTime);
        }
        if ($endTime) {
            $query->where('create_time', '<=', $endTime . ' 23:59:59');
        }

        // 查询分页数据
        $orders = $query->order('create_time', 'desc')
            ->paginate((int)$pageSize, false, ['page' => $page]);

        // 获取总数
        $total = $orders->total();

        // 返回分页数据和总数
        return [
            'data'         => $orders->items(),
            'total'        => $total,
            'current_page' => $orders->currentPage(),
            'last_page'    => $orders->lastPage(),
        ];
    }

    /**
     * 获取订单详情
     *
     * @param int $orderId 订单ID
     * @return array
     */
    public static function detail(int $orderId): array {
        $order = Db::name('orders')
            ->where('id', $orderId)
            ->find();

        if (!$order) {
            return ['error' => 1, 'message' => '订单不存在'];
        }

        // 查询会员信息
        $vipInfo = null;
        if ($order['vip_id']) {
            $vipInfo = Db::name('app_vip')
                ->where('id', $order['vip_id'])
                ->find();
        }

        // 查询用户信息
        $userInfo = null;
        if ($order['user_id']) {
            $userInfo = Db::name('user')
                ->where('id', $order['user_id'])
                ->find();
        }

        return [
            'error'   => 0,
            'message' => '',
            'data'    => [
                'order'    => $order,
                'vip_info' => $vipInfo,
                'user_info' => $userInfo ? [
                    'id'     => $userInfo['id'],
                    'phone'  => $userInfo['phone'] ?? '',
                ] : null,
            ]
        ];
    }

    /**
     * 获取订单操作日志
     *
     * @param int $orderId 订单ID
     * @return array
     */
    public static function logs(int $orderId): array {
        $logs = Db::name('order_log')
            ->where('order_id', $orderId)
            ->order('create_time', 'desc')
            ->select()
            ->toArray();

        return $logs;
    }
}
