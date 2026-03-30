<?php
declare (strict_types=1);

namespace app\index\service\pay;

use app\common\model\Order;
use think\facade\Config;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Exception\Exception as PayException;

/**
 * 微信支付服务
 */
class WechatPayService {

    /**
     * 创建微信支付
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
            'description'  => $order->subject ?: '会员开通',
            'amount'       => (int)bcmul((string)$order->amount, '100'),
            'notify_url'   => $config['notify_url'],
        ];

        $pay = Pay::wechat($config);

        switch ($payType) {
            case 'native':
                // PC扫码支付
                $result = $pay->scan($payload);
                return [
                    'channel'    => 'wechat',
                    'pay_type'   => 'native',
                    'pay_params' => [
                        'code_url' => $result->code_url ?? '',
                    ],
                ];

            case 'h5':
                // 手机浏览器支付
                $payload['scene_info'] = [
                    'payer_client_ip' => request()->ip(),
                    'h5_info'         => [
                        'type' => 'Wap',
                    ],
                ];
                $result = $pay->h5($payload);
                return [
                    'channel'    => 'wechat',
                    'pay_type'   => 'h5',
                    'pay_params' => [
                        'h5_url' => $result->h5_url ?? '',
                    ],
                ];

            case 'app':
                // APP支付
                $result = $pay->app($payload);
                return [
                    'channel'    => 'wechat',
                    'pay_type'   => 'app',
                    'pay_params' => $result->toArray(),
                ];

            case 'jsapi':
                // 小程序/公众号支付
                if (empty($data['openid'])) {
                    throw new \RuntimeException('缺少openid参数');
                }
                $payload['payer'] = [
                    'openid' => $data['openid'],
                ];
                $result = $pay->mini($payload);
                return [
                    'channel'    => 'wechat',
                    'pay_type'   => 'jsapi',
                    'pay_params' => $result->toArray(),
                ];

            default:
                throw new \RuntimeException('暂不支持的微信支付类型');
        }
    }

    /**
     * 查询微信支付订单
     *
     * @param string $orderNo 订单号
     * @return array
     */
    public static function query(string $orderNo): array {
        $config = self::getConfig();
        $pay    = Pay::wechat($config);

        $result = $pay->query(['out_trade_no' => $orderNo]);

        $tradeState = $result->trade_state ?? '';

        if ($tradeState === 'SUCCESS') {
            return [
                'error'           => 0,
                'message'         => '支付成功',
                'data'            => [
                    'pay_status'     => 1,
                    'transaction_id' => $result->transaction_id ?? '',
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
     * 微信退款
     *
     * @param Order $order      订单对象
     * @param float $refundAmount 退款金额
     * @return array
     */
    public static function refund(Order $order, float $refundAmount): array {
        $config = self::getConfig();
        $pay    = Pay::wechat($config);

        $refundNo = 'refund_' . $order->order_no;

        $result = $pay->refund([
            'out_trade_no'  => $order->order_no,
            'out_refund_no' => $refundNo,
            'amount'        => [
                'refund'   => (int)bcmul((string)$refundAmount, '100'),
                'total'    => (int)bcmul((string)$order->amount, '100'),
                'currency' => 'CNY',
            ],
        ]);

        return [
            'error'       => 0,
            'message'     => '退款成功',
            'refund_no'   => $refundNo,
            'refund_data' => $result->toArray(),
        ];
    }

    /**
     * 获取微信支付配置
     *
     * @return array
     */
    private static function getConfig(): array {
        return Config::get('pay.wechat', []);
    }
}