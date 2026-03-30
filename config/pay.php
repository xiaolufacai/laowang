<?php
/**
 * 支付配置
 */

return [
    // 微信支付配置
    'wechat' => [
        'mch_id'                 => env('pay.wechat.mch_id', ''),
        'mini_app_id'            => env('pay.wechat.mini_app_id', ''),
        'mp_app_id'              => env('pay.wechat.mp_app_id', ''),
        'app_id'                 => env('pay.wechat.app_id', ''),
        'mch_secret_key'         => env('pay.wechat.mch_secret_key', ''),
        'mch_secret_cert'        => env('pay.wechat.mch_secret_cert', ''),
        'mch_public_cert_path'   => env('pay.wechat.mch_public_cert_path', ''),
        'notify_url'             => env('pay.wechat.notify_url', ''),
    ],

    // 支付宝支付配置
    'alipay' => [
        'app_id'                  => env('pay.alipay.app_id', ''),
        'app_secret_cert'         => env('pay.alipay.app_secret_cert', ''),
        'alipay_public_cert_path' => env('pay.alipay.alipay_public_cert_path', ''),
        'alipay_root_cert_path'   => env('pay.alipay.alipay_root_cert_path', ''),
        'notify_url'              => env('pay.alipay.notify_url', ''),
        'return_url'              => env('pay.alipay.return_url', ''),
        'sandbox'                 => false,
    ],
];