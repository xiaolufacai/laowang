<?php

namespace app\index\controller;


use think\facade\View;
use think\Request;
use think\response\Json;
use \app\common\model\Agreement as AgreementModel;


class Agreement {
    /**
     * @param Request $request
     * @return Json
     */
    public function agreement(Request $request) {
        try {
            $appId     = $request->param('app_id');
            $agreement = \app\common\model\Agreement::where(['app_id' => $appId])->find();
            return \json(['code' => 1, 'message' => 'OK', 'data' => ['agreement' => $agreement]]);
        } catch (\Throwable $e) {
            return json(['code' => -1, 'data' => [], 'message' => $e->getMessage()]);
        }
    }

    /**
     * @param Request $request
     * @return string|void
     *
     */
    public function index(Request $request) {
        try {
            $appId = $request->param('app_id');
            $type  = $request->param('type');
            $data  = AgreementModel::where(['app_id' => $appId])->find();
            // 读取协议
            $agreements = config('app.agreements');
            $title = $agreements[$type] ?? '';
            $html  = $data[$type] ?? '';

            return View::fetch('index', ['title' => $title, 'html' => html_entity_decode($html)]);
        } catch (\Throwable $e) {
            abort(404, $e->getMessage());
        }
    }
}