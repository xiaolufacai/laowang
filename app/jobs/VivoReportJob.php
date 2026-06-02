<?php

namespace app\jobs;

use app\common\services\VivoService;
use think\facade\Log;
use think\queue\Job;

class VivoReportJob {
    public function fire(Job $job, $data) {
        try {
            $oaid    = trim($data['oaid'] ?? '');
            $clickId = trim($data['clickId'] ?? '');
            $pkgName = trim($data['pkgName'] ?? '');
            $appId   = trim($data['appId'] ?? '');
            $type    = trim($data['reportType'] ?? 0);

            $result = VivoService::reportVivo($oaid, $clickId, $pkgName, $appId, $type);
            var_dump($result);
            if (!$result) {
                Log::error("Vivo上报队列执行失败 | oaid={$oaid} clickId={$clickId} pkgName={$pkgName}");
            }
        } catch (\Exception $e) {
            Log::error(sprintf(
                'Vivo上报队列异常：%s 文件:%s 行号:%s',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));
        }

        $job->delete();
    }

    public function failed($data) {
        Log::error('Vivo上报队列任务达到最大重试次数 | data=' . json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}