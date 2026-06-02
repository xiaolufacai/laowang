<?php

namespace app\jobs;

use app\common\services\OppoService;
use app\common\services\VivoService;
use think\facade\Log;
use think\queue\Job;

class OppoReportJob {
    public function fire(Job $job, $data) {
        try {
            $oaid    = trim($data['oaid'] ?? '');
            $pkgName = trim($data['pkgName'] ?? '');
            $appId   = trim($data['appId'] ?? '');
            $type    = trim($data['reportType'] ?? 0);

            $result = OppoService::reportOppo($oaid, $pkgName, $type);
            var_dump($result);
            if (!$result) {
                Log::error("Oppo上报队列执行失败 | oaid={$oaid} pkgName={$pkgName}");
            }
            // 设置用户激活时间
            if ($result && ($type == 1)) {
                VivoService::activeUser($appId, $oaid);
            }
        } catch (\Exception $e) {
            Log::error(sprintf(
                'Oppo上报队列异常：%s 文件:%s 行号:%s',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));
        }

        $job->delete();
    }

    public function failed($data) {
        Log::error('Oppo上报队列任务达到最大重试次数 | data=' . json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}