<?php

namespace app\common\services;


use app\controller\api\user\User;
use app\jobs\Queue;
use Psr\SimpleCache\InvalidArgumentException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Log;
use think\facade\Request;
use think\response\Json;

class OppoService {
    const AD_SHOW_ACTION = 'adShow';

    /**
     *  上报数据 oppo 数据
     *
     * @param        $oaid
     * @param        $pkgName
     * @param int    $reportType 上报类型 1：激活 2；注册
     * @return mixed
     */
    public static function reportOppo($oaid, $pkgName, $reportType = 2) {
        $userController = app()->make(User::class);
        return $userController->reportoppo($oaid, $pkgName, $reportType);
    }

    /**
     * OPPO 广告监测回调接口（数据库存储版）
     * reportoppo
     * 路由示例: /oppo/callback/:company_id/:app_id
     */
    public function oppodata($company_id = 0, $app_id = 0) {
        // 1. 记录原始请求（用于调试）
        $requestData = Request::param();

        Log::info('【OPPO Ad Callback】Received data==' . $company_id . json_encode($requestData, JSON_UNESCAPED_UNICODE));

        // 2. 必须返回 HTTP 200
        http_response_code(200);

        // 3. 定义回调字段到数据库字段的映射
        // 格式：'回调中的字段名' => '数据库字段名'
        $fieldMapping = [
            'req'     => 'req',
            'imei'    => 'imei',
            'oaid'    => 'oaid',
            'ts'      => 'ts',
            'ownerId' => 'owner_id',   // 注意：回调是 ownerId，数据库用 owner_id
            'planId'  => 'planid',
            'groupId' => 'groupid',
            'adId'    => 'ad_id',
            'ip'      => 'ip',
            'ua'      => 'ua',
            // 注意：回调中没有 __AN__, __OV__, __OS__, __M__, __LAN__, __SPUID__, __PROGRESS__, __EXPINFO__
            // 如果后续有，再补充
        ];

        // 4. 标准化并清理数据
        $insertData = [];
        foreach ($fieldMapping as $inputKey => $dbField) {
            $value = isset($requestData[$inputKey]) ? trim($requestData[$inputKey]) : 'UNKNOWN';
            // 清理控制字符
            $value = preg_replace('/[\r\n\t\x00-\x1f]/', '', $value);
            // 截断到 255 字符（UA 可能很长，但你的表结构限制了）
            $value                = mb_substr($value, 0, 255, 'UTF-8');
            $insertData[$dbField] = $value;
        }

        // 5. 补充缺失的字段（设为 UNKNOWN）
        $allDbFields = ['an', 'ov', 'os', 'm', 'lan', 'spuid', 'progress', 'expinfo'];
        foreach ($allDbFields as $field) {
            if (!isset($insertData[$field])) {
                $insertData[$field] = '';
            }
        }

        // 6. 添加业务字段
        $insertData['company_id'] = (int)$company_id;
        $insertData['app_id']     = (int)$app_id;
        $insertData['event_type'] = 'click'; // 或根据 URL 动态判断
        $oaid                     = trim($insertData['oaid'] ?? '');
        // 如果 oaid 也为空，则无法匹配用户，跳过
        if ($oaid === '') {
            Log::info('OPPO Click Callback - POST】oaid: 为空 oaid==' . $oaid . '--user--' . $company_id);
            return '';
        }

        // 🔥 关键步骤：查询 users 表，看 oaid 是否已存在（代表用户已激活）
        $userExists = Db::name('users')->where('oaid', $oaid)->where('app_id', $app_id)->find();

        if (!$userExists) {
            // 用户不存在，说明尚未激活，不入库也不上报
            Log::info('OPPO Click Callback - POST】uid: 为空 oaid==' . $oaid . '--user--' . $company_id);
            return '';
        }

        $flag = Db::name('oppo_ad_events')
            ->where('oaid', $oaid)
            ->where('company_id', $company_id)
            ->where('app_id', $app_id)
            ->find();
        if ($flag) {
            //已上报过了
            Log::info('【OPPO Click Callback - POST】Skipped: 已上报过了==' . $oaid);
            return '';
        }

        try {
            // 7. 写入数据库
            $result = Db::name('oppo_ad_events')->insert($insertData);
            /*
            // 实例化 User 控制器
            $userController = app()->make(User::class);
            // 调用上报方法
            $reportResult = $userController->reportoppo($oaid);
            if ($reportResult) {
                Log::info('【OPPO Click Callback - POST】Skipped: 上报成功==' . $oaid);
            } else {
                Log::info('【OPPO Click Callback - POST】Skipped: 上报失败==' . $oaid);
            }

            if ($result) {
                Log::info("【OPPO Ad Callback】Success", [
                    'req'   => $insertData['req'] ?? 'N/A',
                    'ad_id' => $insertData['ad_id'] ?? 'N/A'
                ]);
            } else {
                Log::error("【OPPO Ad Callback】Insert failed (no exception)", $insertData);
            }
            */
        } catch (\Exception $e) {
            Log::error("【OPPO Ad Callback】Error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data'  => $insertData
            ]);
        }

        // 8. 返回空响应（OPPO 要求 200 + 空 body）
        return '';
    }
}
