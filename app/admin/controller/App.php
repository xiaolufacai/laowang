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

class App extends AdminBaseController {
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
    public function apps(): Json {
        $apps = Apps::where(['status' => Apps::STATUS_NORMAL])->select();
        return json(['code' => 0, 'message' => 'OK', 'data' => $apps]);
    }

    /**
     * 新增APP
     *
     * @param Request $request
     * @return Json
     */
    public function add(Request $request): Json {
        $post = $request->post();
        return AppService::add($post);
    }

    public function delete(Request $request): Json {
        $post = $request->post();
        return AppService::delete($post['id']);
    }

    public function app(Request $request) {
        $id = $request->get('id');
        return json(['code' => 0, 'message' => 'OK', 'data' => AppService::appData($id)]);
    }

    public function update(Request $request): Json {
        $post = $request->post();
        return AppService::edit($post);
    }

    /**
     * APP管理
     *
     * @return string
     */
    public function manage(): string {
        return View::fetch();
    }

    /**
     * APP 渠道列表
     *
     * @return Json
     */
    public function channels(): Json {
        $channels = Config::get('app.channels');
        return json(['code' => 0, 'data' => $channels]);
    }

    /**
     * APP 渠道信息
     *
     * @param Request $request
     * @return Json
     */
    public function appChannels(Request $request): Json {
        $appId = $request->get('app_id');
        $list  = AppService::appChannels($appId);
        return json(['code' => 0, 'data' => $list]);
    }

    /**
     * 切换APP上架状态
     *
     * @param Request $request
     * @return Json
     */
    public function switch(Request $request): Json {
        $id     = $request->post('id');
        $status = $request->post('status');

        // 状态
        $setStatus = intval(!$status);
        $result    = AppService::switchApp($id, $setStatus);
        return json(['code' => $result['error'], 'message' => $result['message']]);
    }

    /**
     * 切换APP上架状态
     *
     * @param Request $request
     * @return Json
     */
    public function setList(Request $request): Json {
        $id     = $request->post('id');
        $status = $request->post('list_status');

        // 状态
        $setStatus = intval(!$status);
        $result    = AppService::setListStatus($id, $setStatus);
        return json(['code' => $result['error'], 'message' => $result['message']]);
    }

    public function addChannel(Request $request) {
        $data   = $request->post();
        $appid  = $request->get('app_id');
        $result = AppService::setAppChannel($data, $appid);
        return json(['code' => $result['error'], 'message' => $result['message']]);
    }
}
