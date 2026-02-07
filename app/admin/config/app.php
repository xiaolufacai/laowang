<?php
// 配置渠道
$channels = [
    'oppo'    => 'OPPO',
    'vivo'    => 'VIVO',
    'huawei'  => '华为',
    'xiaomi'  => '小米',
    'test'    => 'Test渠道',
    'qq'      => '应用宝',
    'baidu'   => '百度',
    'toutiao' => '头条',
    'honor'   => '荣耀',
    'cool'    => '酷安',
    'oneplus' => '一加',
    'other'   => '其他',
];
// 会员
$vips = [
    'all'   => '终身会员',
    'year'  => '年度会员',
    'month' => '月度会员',
    'day'   => '天会员',
];

// 支付方式
$paymentMethods = [
    'wxpay'  => '微信支付',
    'alipay' => '支付宝',
];

$agreementUrl = 'http://www.laowang.com/';

return [
    'channels'       => $channels,
    'vips'           => $vips,
    'paymentMethods' => $paymentMethods,
    'agreementUrl'   => $agreementUrl,
];