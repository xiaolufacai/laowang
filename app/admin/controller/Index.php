<?php
declare (strict_types = 1);

namespace app\admin\controller;


use app\AdminBaseController;
use app\common\model\Admin;
use think\facade\Session;
use think\facade\View;
use think\Request;

class Index extends AdminBaseController
{
    /**
     * 首页
     * 
     * @return string
     */
    public function index()
    {
        return View::fetch();
    }

    public function resetPassword(Request $request)
    {
        // 获取前端传过来的参数
        $oldPw  = $request->post('old_pw');
        $newPw  = $request->post('new_pw');
        $userId = Session::get('userId');
        // 获取当前管理员的密码（根据实际情况修改）
        $admin = Admin::where('id', $userId)->find();

        if (!$admin) {
            return json(['code' => 1, 'message' => '用户不存在']);
        }

        // 验证旧密码是否正确
        if (md5($oldPw) !== $admin['password']) {
            return json(['code' => 1, 'message' => '旧密码错误']);
        }

        // 更新密码
        $updateResult = Admin::where('id', $userId)->update([
            'password' => md5($newPw), // 新密码加密后存入数据库
        ]);

        if ($updateResult) {
            return json(['code' => 0, 'message' => '密码修改成功']);
        } else {
            return json(['code' => 1, 'message' => '密码修改失败，请重试']);
        }
    }
}
