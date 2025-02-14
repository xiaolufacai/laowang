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

class Login
{
    /**
     * 登录页
     *
     * @return string
     */
    public function index()
    {
        if (Session::get('userId')) {
            return redirect('/admin/index/index')->send();
        }
        return View::fetch();
    }

    /**
     * 登录
     *
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function login(): Json
    {
        // 获取请求数据
        $data = Request::post();

        // 1. 表单验证
        $validate = Validate::rule([
            'username' => 'require',
            'password' => 'require',
            'captcha'  => 'require',
        ])->message([
            'username.require' => '用户名不能为空',
            'password.require' => '密码不能为空',
            'captcha.require'  => '验证码不能为空'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }

        // 2. **手动验证验证码**
        if (!Captcha::check($data['captcha'])) {
            return json(['code' => 1, 'msg' => '验证码错误']);
        }

        // 3. **验证用户名和密码**
        $user = Admin::where('username', $data['username'])->find();
        if (!$user) {
            return json(['code' => 1, 'msg' => '用户不存在']);
        }
        if ($user['password'] == md5($data['password'])) {
            Session::set('username', $data['username']);
            Session::set('userId', $user['id']);
            return json(['code' => 0, 'msg' => '登录成功']);
        } else {
            return json(['code' => 1, 'msg' => '用户名或密码错误']);
        }
    }
}
