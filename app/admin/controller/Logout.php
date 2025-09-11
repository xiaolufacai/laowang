<?php

namespace app\admin\controller;


use app\common\model\Admin;
use think\captcha\facade\Captcha;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\View;
use think\facade\Session;
use think\facade\Request;
use think\facade\Validate;
use think\response\Json;

class Logout {
    /**
     * 登录页
     *
     * @return string
     */
    public function logout() {
        Session::clear();
        return redirect('/admin/login/index')->send();
    }
}
