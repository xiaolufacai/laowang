<?php
declare (strict_types=1);

namespace app\admin\controller;


use app\admin\service\AgreementService;
use app\AdminBaseController;
use app\common\model\App as Apps;
use app\Request;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\View;
use think\response\Json;
use \app\common\model\Agreement as AgreementModel;

class Agreement extends AdminBaseController {
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
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function apps(): Json {
        $apps = Apps::where(['status' => Apps::STATUS_NORMAL])->select();
        foreach ($apps as $app) {
            $agreement                = AgreementModel::where(['app_id' => $app['id']])->find();
            $app['user_agreement']    = $agreement['user_agreement'] ?? '';
            $app['privacy_agreement'] = $agreement['privacy_agreement'] ?? '';
            $app['sdk_list']          = $agreement['sdk_list'] ?? '';
            $app['user_collect']      = $agreement['user_collect'] ?? '';
            $app['vip_agreement']     = $agreement['vip_agreement'] ?? '';
        }
        return json(['code' => 0, 'message' => 'OK', 'data' => $apps]);
    }

    /**
     * 新增APP
     *
     * @param Request $request
     * @return Json
     */
    public function update(Request $request): Json {
        $post = $request->post();
        return AgreementService::add($post);
    }
}
