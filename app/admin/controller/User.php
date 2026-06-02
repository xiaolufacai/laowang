<?php
declare (strict_types=1);

namespace app\admin\controller;


use app\admin\service\UserService;
use app\AdminBaseController;
use app\Request;
use think\db\exception\DbException;
use think\facade\View;
use think\response\Json;

class User extends AdminBaseController {
    /**
     * 用户 列表页
     *
     * @return string
     */
    public function index(): string {
        View::assign([
            'nav' => '用户中心',
        ]);
        return View::fetch();
    }

    /**
     *  用户列表
     *
     * @param Request $request
     * @return Json
     * @throws DbException
     */
    public function users(Request $request): Json {
        // 获取查询参数
        $appId    = $request->get('app_id', null);
        $id       = $request->get('order_id', null);
        $page     = $request->get('page', 1);
        $pageSize = $request->get('page_size', 10);

        $vipTypes = config('app.vips');

        $channels = config('app.channels');
        // 调用 Service 获取订单数据
        $result = UserService::users($appId, $id, $page, $pageSize);
        if (!empty($result['data'])) {
            foreach ($result['data'] as &$item) {
                $item['vip_type_txt'] = $vipTypes[$item['vip_type']] ?? '';
                $item['channel_txt']  = $channels[$item['channel']] ?? '';
            }
        }
        // 返回响应
        return json(['code' => 0, 'data' => $result]);
    }

    /**
     *  设置用户会员
     *
     * @param Request $request
     * @return Json
     */
    public function setUserVip(Request $request): Json {
        try {
            $userId  = $request->param('user_id', null);
            $vipType = $request->param('vip_type', null);
            $vipTime = $request->param('vip_time', null);

            if (empty($userId) || empty($vipType) || empty($vipTime)) {
                return json(['code' => 1, 'message' => '用户|会员类型|会员到期时间必填']);
            }
            $result = UserService::setUserVip($userId, $vipType, $vipTime);
            return json(['code' => 0, 'data' => $result]);
        } catch (\Exception $exception) {
            return json(['code' => 2, 'message' => $exception->getMessage(), 'data' => []]);
        }
    }
}
