<?php
declare (strict_types=1);

namespace app\index\service\pay;

use app\common\model\Order;
use app\index\service\VipService;
use think\facade\Config;
use think\facade\Db;
use think\facade\Log;
use Yansongda\Pay\Pay;

/**
 * 支付回调服务
 */
class NotifyService {

    /**
     * 微信支付回调
     *
     * @return string
     */
    public static function wechat(): string {
        $config = self::getWechatConfig();
        $pay    = Pay::wechat($config);

        try {
            $response = $pay->callback();
            $data     = $response->toArray();

            $orderNo      = $data['out_trade_no'] ?? '';
            $transactionId = $data['transaction_id'] ?? '';
            $tradeState   = $data['trade_state'] ?? '';

            // 记录回调日志
            self::logNotify('wechat', $orderNo, $data);

            // 只有支付成功才处理
            if ($tradeState !== 'SUCCESS') {
                return $pay->success();
            }

            // 处理订单
            self::handleOrder($orderNo, 'wechat', $transactionId, $data);

            return $pay->success();
        } catch (\Exception $e) {
            Log::error('微信支付回调异常: ' . $e->getMessage());
            return 'fail';
        }
    }

    /**
     * 支付宝支付回调
     *
     * @return string
     */
    public static function alipay(): string {
        $config = self::getAlipayConfig();
        $pay    = Pay::alipay($config);

        try {
            $response = $pay->callback();
            $data     = $response->toArray();

            $orderNo      = $data['out_trade_no'] ?? '';
            $transactionId = $data['trade_no'] ?? '';
            $tradeStatus  = $data['trade_status'] ?? '';

            // 记录回调日志
            self::logNotify('alipay', $orderNo, $data);

            // 只有支付成功才处理
            if (!in_array($tradeStatus, ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
                return 'success';
            }

            // 处理订单
            self::handleOrder($orderNo, 'alipay', $transactionId, $data);

            return 'success';
        } catch (\Exception $e) {
            Log::error('支付宝支付回调异常: ' . $e->getMessage());
            return 'fail';
        }
    }

    /**
     * 处理订单支付成功
     *
     * @param string $orderNo      订单号
     * @param string $channel      支付渠道
     * @param string $transactionId 第三方交易号
     * @param array  $notifyData   回调数据
     */
    private static function handleOrder(string $orderNo, string $channel, string $transactionId, array $notifyData): void {
        Db::transaction(function () use ($orderNo, $channel, $transactionId, $notifyData) {
            // 加锁查询订单
            $order = Order::where('order_no', $orderNo)->lock(true)->find();

            if (!$order) {
                throw new \RuntimeException('订单不存在: ' . $orderNo);
            }

            // 幂等处理：已支付直接返回
            if ($order->pay_status === Order::PAY_STATUS_PAID) {
                return;
            }

            // 更新订单状态
            $order->pay_status    = Order::PAY_STATUS_PAID;
            $order->pay_type      = $channel === 'wechat' ? Order::PAY_TYPE_WECHAT : Order::PAY_TYPE_ALIPAY;
            $order->transaction_id = $transactionId;
            $order->pay_time      = date('Y-m-d H:i:s');
            $order->notify_data   = json_encode($notifyData, JSON_UNESCAPED_UNICODE);
            $order->update_time   = date('Y-m-d H:i:s');
            $order->save();

            // 记录订单操作日志
            self::logOrderOperation($order->id, 'pay', '订单支付成功，支付渠道：' . $channel);

            // 调用会员开通服务
            if ($order->vip_id) {
                $vipResult = VipService::open($order->vip_id);
                if ($vipResult['error'] === 0) {
                    self::logOrderOperation($order->id, 'vip_open', '会员开通成功');
                } else {
                    self::logOrderOperation($order->id, 'vip_open_failed', '会员开通失败：' . $vipResult['message']);
                }
            }
        });
    }

    /**
     * 记录回调日志
     *
     * @param string $channel 支付渠道
     * @param string $orderNo 订单号
     * @param array  $data    回调数据
     */
    private static function logNotify(string $channel, string $orderNo, array $data): void {
        Db::name('pay_notify_log')->insert([
            'channel'     => $channel,
            'order_no'    => $orderNo,
            'notify_data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'create_time' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 记录订单操作日志
     *
     * @param int    $orderId   订单ID
     * @param string $operation 操作类型
     * @param string $message   操作说明
     */
    private static function logOrderOperation(int $orderId, string $operation, string $message): void {
        Db::name('order_log')->insert([
            'order_id'    => $orderId,
            'operation'   => $operation,
            'message'     => $message,
            'create_time' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 获取微信支付配置
     *
     * @return array
     */
    private static function getWechatConfig(): array {
        return Config::get('pay.wechat', []);
    }

    /**
     * 获取支付宝支付配置
     *
     * @return array
     */
    private static function getAlipayConfig(): array {
        return Config::get('pay.alipay', []);
    }
}