<?php
declare (strict_types=1);

namespace app\index\service\pay;

use app\common\model\Order;
use app\index\service\pay\WechatPayService;
use app\index\service\pay\AlipayService;
use think\exception\ValidateException;

/**
 * 统一支付服务
 */
class PayService {

    /**
     * 创建支付
     *
     * @param array $data 支付参数
     * @return array
     * @throws ValidateException
     */
    public static function create(array $data): array {
        // 参数验证
        if (empty($data['order_no'])) {
            throw new ValidateException('订单号不能为空');
        }

        if (empty($data['channel'])) {
            throw new ValidateException('支付渠道不能为空');
        }

        if (!in_array($data['channel'], ['wechat', 'alipay'])) {
            throw new ValidateException('支付渠道错误');
        }

        if (empty($data['pay_type'])) {
            throw new ValidateException('支付方式不能为空');
        }

        // 查询订单
        $order = Order::where('order_no', $data['order_no'])
            ->where('status', Order::STATUS_NORMAL)
            ->find();

        if (!$order) {
            throw new ValidateException('订单不存在');
        }

        // 检查订单支付状态
        if ($order->pay_status === Order::PAY_STATUS_PAID) {
            throw new ValidateException('订单已支付');
        }

        if ($order->pay_status === Order::PAY_STATUS_CANCELLED) {
            throw new ValidateException('订单已取消');
        }

        // 检查金额
        if (bccomp((string)$order->amount, '0', 2) <= 0) {
            throw new ValidateException('订单金额异常');
        }

        // 根据渠道调用对应的支付服务
        if ($data['channel'] === 'wechat') {
            return WechatPayService::create($order, $data);
        }

        if ($data['channel'] === 'alipay') {
            return AlipayService::create($order, $data);
        }

        throw new ValidateException('不支持的支付渠道');
    }

    /**
     * 查询订单支付状态
     *
     * @param string $orderNo 订单号
     * @param string $channel 支付渠道
     * @return array
     */
    public static function query(string $orderNo, string $channel): array {
        $order = Order::where('order_no', $orderNo)->find();

        if (!$order) {
            return ['error' => 1, 'message' => '订单不存在'];
        }

        // 如果订单已支付，直接返回
        if ($order->pay_status === Order::PAY_STATUS_PAID) {
            return ['error' => 0, 'message' => '订单已支付', 'data' => ['pay_status' => 1]];
        }

        // 向第三方查询支付状态
        try {
            if ($channel === 'wechat') {
                $result = WechatPayService::query($orderNo);
            } else {
                $result = AlipayService::query($orderNo);
            }

            return $result;
        } catch (\Exception $e) {
            return ['error' => 1, 'message' => $e->getMessage()];
        }
    }
}