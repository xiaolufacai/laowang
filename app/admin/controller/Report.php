<?php
declare (strict_types=1);

namespace app\admin\controller;

use app\AdminBaseController;
use app\common\model\App as AppModel;
use app\common\service\ReportData;
use app\common\services\VivoService;
use app\Request;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use think\response\Json;

class Report extends AdminBaseController {

    /**
     *  VIVO 上报
     *
     * @return Json
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function vivo(Request $request): Json {
        $id = (int)$request->param('id', 0);
        if ($id <= 0) {
            return json(['code' => 1, 'message' => '用户ID不能为空']);
        }

        $user = Db::name('users')->where('id', $id)->where('is_report', 0)->find();
        if (empty($user)) {
            return json(['code' => 1, 'message' => '用户不存在或已上报']);
        }

        if (empty($user['oaid'])) {
            return json(['code' => 1, 'message' => 'oaid不存在无法上报']);
        }

        $app = AppModel::where('app_id', $user['app_id'])->find();
        if (empty($app)) {
            return json(['code' => 1, 'message' => 'APP不存在']);
        }

        $result = VivoService::reportVivo($user['oaid'], '', $app['name'], $user['app_id'], 0, 1);
        if ($result === true || $result === null || (is_array($result) && (int)($result['error'] ?? 1) === 0)) {
            Db::name('users')->where('id', $id)->update([
                'is_report'   => 1,
                'report_time' => date('Y-m-d H:i:s'),
            ]);
            return json(['code' => 0, 'message' => '上报成功']);
        }

        return json(['code' => 1, 'message' => is_array($result) ? ($result['message'] ?? '上报失败') : '上报失败']);
    }


    /**
     *  OPPO 上报
     *
     * @return Json
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function oppo(Request $request): Json {
        $id = (int)$request->param('id', 0);
        if ($id <= 0) {
            return json(['code' => 1, 'message' => '用户ID不能为空']);
        }

        $user = Db::name('users')->where('id', $id)->where('is_report', 0)->find();
        if (empty($user)) {
            return json(['code' => 1, 'message' => '用户不存在或已上报']);
        }

        if (empty($user['oaid'])) {
            return json(['code' => 1, 'message' => 'oaid不存在无法上报']);
        }

        $app = AppModel::where('app_id', $user['app_id'])->find();
        if (empty($app)) {
            return json(['code' => 1, 'message' => 'APP不存在']);
        }

        $userController = app()->make(ReportData::class);
        $result         = $userController->reportoppo($user['oaid'], $app['name'], 2);
        if ($result) {
            Db::name('users')->where('id', $id)->update([
                'is_report'   => 1,
                'report_time' => date('Y-m-d H:i:s'),
            ]);
            return json(['code' => 0, 'message' => '上报成功']);
        }

        return json(['code' => 1, 'message' => '上报失败']);
    }
}
