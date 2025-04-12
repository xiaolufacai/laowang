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

Route::get('think', function () {
    return 'hello,ThinkPHP8!';
});

// index模块下，除了 login 外，其他都加上 jwt 中间件
Route::group(function () {
    Route::get('index', 'index/index/index');
    Route::get('users', 'index/index/users');
    // 其他路由...
})->middleware(\app\middleware\JWTAuthMiddleware::class);

Route::post('/login', 'index/login/login');