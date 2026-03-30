<?php
declare (strict_types=1);

namespace app\index\service\pay;

use app\common\model\Order;
use think\facade\Config;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Exception\Exception as PayException;

/**
 * 支付宝支付服务
 */
class AlipayService {

    /**
     * 创建支付宝支付
     *
     * @param Order $order 订单对象
     * @param array $data  支付参数
     * @return array
     * @throws PayException
     */
    public static function create(Order $order, array $data): array {
        $config  = self::getConfig();
        $payType = $data['pay_type'];

        $payload = [
            'out_trade_no' => $order->order_no,
            'total_amount' => (string)$order->amount,
            'subject'      => $order->subject ?: '会员开通',
            'body'         => $order->subject ?: '会员开通',
            'notify_url'   => $config['notify_url'],
            'return_url'   => $config['return_url'] ?? '',
        ];

        $pay = Pay::alipay($config);

        switch ($payType) {
            case 'page':
                // PC网页支付
                $result = $pay->page($payload);
                return [
                    'channel'    => 'alipay',
                    'pay_type'   => 'page',
                    'pay_params' => [
                        'html' => $result->getBody(),
                    ],
                ];

            case 'h5':
                // 手机网页支付
                $payload['quit_url'] = $config['return_url'] ?? '';
                $result = $pay->wap($payload);
                return [
                    'channel'    => 'alipay',
                    'pay_type'   => 'h5',
                    'pay_params' => [
                        'html' => $result->getBody(),
                    ],
                ];

            case 'app':
                // APP支付
                $result = $pay->app($payload);
                return [
                    'channel'    => 'alipay',
                    'pay_type'   => 'app',
                    'pay_params' => $result->toArray(),
                ];

            default:
                throw new \RuntimeException('暂不支持的支付宝支付类型');
        }
    }

    /**
     * 查询支付宝支付订单
     *
     * @param string $orderNo 订单号
     * @return array
     */
    public static function query(string $orderNo): array {
        $config = self::getConfig();
        $pay    = Pay::alipay($config);

        $result = $pay->query(['out_trade_no' => $orderNo]);

        $tradeStatus = $result->trade_status ?? '';

        if (in_array($tradeStatus, ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
            return [
                'error'           => 0,
                'message'         => '支付成功',
                'data'            => [
                    'pay_status'     => 1,
                    'transaction_id' => $result->trade_no ?? '',
                ]
            ];
        }

        return [
            'error'   => 0,
            'message' => '未支付',
            'data'    => ['pay_status' => 0]
        ];
    }

    /**
     * 支付宝退款
     *
     * @param Order $order      订单对象
     * @param float $refundAmount 退款金额
     * @return array
     */
    public static function refund(Order $order, float $refundAmount): array {
        $config = self::getConfig();
        $pay    = Pay::alipay($config);

        $refundNo = 'refund_' . $order->order_no;

        $result = $pay->refund([
            'out_trade_no'   => $order->order_no,
            'refund_amount'  => (string)$refundAmount,
            'out_request_no' => $refundNo,
        ]);

        return [
            'error'       => 0,
            'message'     => '退款成功',
            'refund_no'   => $refundNo,
            'refund_data' => $result->toArray(),
        ];
    }

    /**
     * 获取支付宝支付配置
     *
     * @return array
     */
    private static function getConfig(): array {
        return Config::get('pay.alipay', []);
    }
}