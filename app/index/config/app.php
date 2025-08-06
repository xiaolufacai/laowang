<?php
// 配置渠道
$channels = [
    'oppo'   => 'OPPO',
    'vivo'   => 'VIVO',
    'huawei' => '华为',
    'xiaomi' => '小米',
    'yyb'    => '应用宝',
    'test'   => 'Test渠道',
];
// 会员
$vips = [
    'all'   => '终身会员',
    'year'  => '年度会员',
    'month' => '月度会员',
];

// 支付方式
$paymentMethods = [
    'wxpay'  => '微信支付',
    'alipay' => '支付宝',
];

// 协议地址
$agreementUrl = 'http://www.laowang.com';
return [
    'channels'       => $channels,
    'vips'           => $vips,
    'paymentMethods' => $paymentMethods,
    'agreementUrl'   => $agreementUrl,
];