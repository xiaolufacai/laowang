<?php
declare (strict_types=1);

namespace app\admin\controller;

use app\admin\service\AdStatService;
use app\AdminBaseController;
use app\Request;
use think\db\exception\DbException;
use think\facade\Config;
use think\facade\View;
use think\response\Json;

class AdStat extends AdminBaseController {

    /**
     * 广告统计列表页
     *
     * @return string
     */
    public function index(): string {
        View::assign([
            'nav' => '广告统计',
        ]);
        return View::fetch();
    }

    /**
     * 广告统计列表
     *
     * @param Request $request
     * @return Json
     * @throws DbException
     */
    public function list(Request $request): Json {
        $appId     = $request->get('app_id', null);
        $channel   = $request->get('channel', null);
        $startTime = $request->get('start_time', null);
        $endTime   = $request->get('end_time', null);
        $page      = $request->get('page', 1);
        $pageSize  = $request->get('page_size', 10);

        $result = AdStatService::list($appId, $channel, $startTime, $endTime, $page, $pageSize);
        return json(['code' => 0, 'data' => $result]);
    }

    /**
     * 手动回传
     *
     * @param Request $request
     * @return Json
     */
    public function report(Request $request): Json {
        $id = (int)$request->post('id', 0);
        if ($id <= 0) {
            return json(['code' => 1, 'message' => '用户ID不能为空']);
        }

        $platform = (string)$request->post('platform', '');
        $result   = AdStatService::report($id, $platform);
        return json(['code' => $result['error'], 'message' => $result['message']]);
    }

    /**
     * 渠道列表
     *
     * @return Json
     */
    public function channels(): Json {
        return json(['code' => 0, 'data' => Config::get('app.channels')]);
    }
}
