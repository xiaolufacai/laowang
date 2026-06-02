<?php

namespace app\index\controller;

use app\common\services\VivoService;
use app\jobs\Queue;
use think\App;
use think\facade\Db;
use think\facade\Request;
use think\Response;

class Device {

    /**
     * 转化上报接口
     *
     * POST /report
     */
    public function report(Request $request): Response {
        $data = [
            'pkgName'   => Request::header('pkgName', ''),
            'oaid'      => Request::header('oaid', ''),
            'app_id'    => Request::header('appId', ''),
            'cvType'    => Request::post('cvType', 2),
            'payAmount' => Request::post('payAmount', 0),
            'action'    => Request::post('action', ''), // "adShow" 活 "adClick"
            'ad_type'   => Request::post('adType', ''), // splash���� banner reward���� inter���� native��Ϣ��
            'code_id'   => Request::post('codeId', 0), // 广告id
            'slot_id'   => Request::post('slotId', 0), // 广告位id
            'sdk_name'  => Request::post('sdkName', 0),
            'ecpm'      => Request::post('ecpm', 0),
            'clickId'   => Request::post('clickId', ''),
            'channel'   => Request::header('channel', ''),
        ];

        $result = VivoService::report($data);

        if ($result['error']) {
            return json([
                'code' => 500,
                'msg'  => $result['message'],
            ]);
        }

        return json([
            'code' => 200,
            'msg'  => $result['message'],
        ]);
    }

    /**
     * 归因接口
     *
     */
    public function attribution(Request $request): Response {
        $oaid = Request::header('oaid', '');
        // 调用service
        $result = VivoService::attribution($oaid);

        return json([
            'code' => 200,
            'msg'  => 'OK',
            'data' => (bool)$result,
        ]);
    }

    /**
     * APP启动上报接口
     */
    public function appStart(Request $request): Response {
        $oaid    = trim(Request::header('oaid', ''));
        $channel = trim(Request::header('channel', ''));
        $appId   = trim(Request::header('appId', ''));
        if ($oaid === '') {
            return json([
                'code' => 400,
                'msg'  => 'oaid不能为空',
            ]);
        }

        $now  = date('Y-m-d H:i:s');
        $data = [
            'oaid'        => $oaid,
            'app_id'      => $appId,
            'channel'     => $channel,
            'start_time'  => $now,
            'create_time' => $now,
            'is_report'   => 0,
        ];
        Db::name('app_start_records')->insert($data);

        // 注册用户，调用接口直接将设置激活时间
        $userData = [
            'oaid'        => $oaid,
            'channel'     => $channel,
            'app_id'      => $appId,
            'brand'       => trim(Request::header('mobileBrand', '')),
            'model'       => trim(Request::header('mobileModel', '')),
            'active_time' => 0
        ];
        VivoService::setUser($oaid, $appId, $userData);

        $pkgName = Request::header('pkgName', '');
        if ($channel == 'vivo') {
            Queue::push(\app\jobs\VivoReportJob::class, [
                'oaid'       => $oaid,
                'clickId'    => '',
                'channel'    => $data['channel'],
                'pkgName'    => $pkgName,
                'appId'      => $appId,
                'reportType' => 1,
            ], 'vivo_report');
        } else if ($channel == 'oppo') {
            Queue::push(\app\jobs\OppoReportJob::class, [
                'oaid'       => $oaid,
                'channel'    => $data['channel'],
                'pkgName'    => $pkgName,
                'reportType' => 1,
            ], 'oppo_report');
        }

        return json([
            'code' => 200,
            'msg'  => 'success',
        ]);
    }
}
