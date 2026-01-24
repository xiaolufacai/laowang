<?php
// 配置渠道
$channels = [
    'oppo'    => 'OPPO',
    'vivo'    => 'VIVO',
    'huawei'  => '华为',
    'xiaomi'  => '小米',
    'yyb'     => '应用宝',
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
];

// 支付方式
$paymentMethods = [
    'wxpay'  => '微信支付',
    'alipay' => '支付宝',
];

// 协议地址
$agreementUrl = 'http://110.40.229.180/';

// 协议类型
$agreements = [
    'privacy_agreement' => '隐私政策',
    'user_agreement'    => '用户协议',
    'sdk_list'          => '第三方SDK信息共享清单',
    'user_collect'      => '已收集个人信息清单',
    'vip_agreement'     => '会员服务协议'
];
return [
    'channels'       => $channels,
    'vips'           => $vips,
    'paymentMethods' => $paymentMethods,
    'agreementUrl'   => $agreementUrl,
    'agreements'     => $agreements,
];