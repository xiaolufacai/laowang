<?php
declare (strict_types=1);

namespace app\admin\controller;


use app\admin\service\ConfigService;
use app\AdminBaseController;
use app\Request;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Config;
use think\facade\View;
use think\response\Json;

class Configs extends AdminBaseController {
    /**
     * App 列表
     *
     * @return string
     */
    public function index() {
        return View::fetch();
    }

    /**
     * APP列表
     *
     * @return Json
     */
    public function configs(Request $request): Json {
        // 获取查询参数
        $channel  = $request->get('channel', null);
        $page     = $request->get('page', 1);
        $pageSize = $request->get('page_size', 10);
        // 调用 Service 获取订单数据
        $result = ConfigService::configs($channel, $page, $pageSize);
        // 返回响应
        return json(['code' => 0, 'data' => $result]);
    }

    /**
     * 新增APP
     *
     * @param Request $request
     * @return Json
     */
    public function add(Request $request): Json {
        $post = $request->post();
        return ConfigService::add($post);
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
    public function delete(Request $request): Json {
        $id     = $request->post('id');
        $result = ConfigService::delete($id);
        return json(['code' => $result['error'], 'message' => $result['message']]);
    }

    /**
     *  会员类型
     *
     * @return Json
     */
    public function vips() {
        $vips = config('app.vips');
        return json(['code' => 0, 'data' => $vips, 'message' => 'OK']);
    }
}
