<?php
declare (strict_types=1);

namespace app\admin\controller;


use app\admin\service\AppService;
use app\AdminBaseController;
use app\common\model\App as Apps;
use app\Request;
use think\facade\Config;
use think\facade\View;
use think\response\Json;

class Agreement extends AdminBaseController
{
    /**
     * App 列表
     *
     * @return string
     */
    public function index()
    {
        return View::fetch();
    }

    /**
     * APP列表
     *
     * @return Json
     */
    public function apps(): Json
    {
        $apps = Apps::where(['status' => Apps::STATUS_NORMAL])->select();
        foreach ($apps as $app) {
            $agreement = \app\common\model\Agreement::where(['package' => $app['name']])->find();
            $app['user_agreement'] = $agreement['user_agreement'];
            $app['privacy_agreement'] = $agreement['privacy_agreement'];
        }
        return json(['code' => 0, 'message' => 'OK', 'data' => $apps]);
    }

    /**
     * 新增APP
     *
     * @param Request $request
     * @return Json
     */
    public function add(Request $request): Json
    {
        $post = $request->post();
        return AppService::add($post);
    }
}
