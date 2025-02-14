<?php
declare (strict_types=1);

namespace app\admin\controller;


use app\admin\service\AppVipService;
use app\AdminBaseController;
use app\Request;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Config;
use think\facade\View;
use think\response\Json;

class Price extends AdminBaseController
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
     * 会会员类型
     *
     * @return Json
     */
    public function vips(): Json
    {
        $channels = Config::get('app.vips');
        return json(['code' => 0, 'data' => $channels]);
    }

    public function addVip(Request $request): Json
    {
        $post   = $request->post();
        $result = AppVipService::add($post);
        return json(['code' => $result['error'],'message' => $result['message']]);
    }

    /**
     * App VIPS
     *
     * @param Request $request
     * @return Json
     */
    public function appVips(Request $request): Json
    {
        $appId = $request->get('app_id');
        $data  = AppVipService::appVips($appId);
        return json(['code' => 0, 'data' => $data]);
    }

    /**
     * 删除
     *
     * @param Request $request
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function delete(Request $request): Json
    {
        $id = $request->post('id');
        $result = AppVipService::delete($id);
        return json(['code' => $result['error'],'message' => $result['message']]);
    }
}
