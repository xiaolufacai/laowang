<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

// APP相关接口
Route::get('apps','index/App/apps');

// 订单相关接口
Route::group('order', function() {
    Route::post('setOrder', 'index/Order/setOrder');     // 用户下单
    Route::get('orders', 'index/Order/orders');          // 用户订单列表
    Route::get('detail', 'index/Order/detail');          // 用户订单详情
    Route::post('cancel', 'index/Order/cancel');         // 用户取消订单
});

// 支付相关接口
Route::group('pay', function() {
    Route::post('create', 'index/Pay/create');           // 创建支付
    Route::get('query', 'index/Pay/query');              // 查询支付状态
    Route::post('notify/wechat', 'index/Pay/notifyWechat'); // 微信支付回调
    Route::post('notify/alipay', 'index/Pay/notifyAlipay'); // 支付宝支付回调
});