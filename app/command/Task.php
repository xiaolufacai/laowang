<?php
declare(strict_types=1);

namespace app\command;

use app\common\service\OppoService;
use app\common\service\VivoService;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\facade\Db;
use think\facade\Log;

class Task extends Command {
    protected function configure() {
        $this->setName('task')
            ->addArgument('action', Argument::OPTIONAL, 'action name')
            ->addArgument('params', Argument::IS_ARRAY, 'params');
    }

    protected function execute(Input $input, Output $output) {
        // 获取子命令
        $action = $input->getArgument('action');
        $params = $input->getArgument('params');

        if (!$action) {
            return $output->writeln("Missing action");
        }

        // 兼容 test:detail 写法
        if (strpos($action, ':') !== false) {
            [, $action] = explode(':', $action);
        }

        if (!method_exists($this, $action)) {
            return $output->writeln("Action [$action] not found");
        }

        try {
            $result = call_user_func_array([$this, $action], $params);

            if ($result !== null) {
                $output->writeln($result);
            }

        } catch (\Throwable $e) {
            $output->writeln("Error: " . $e->getMessage());
        }
    }

    // ====================
    // 子命令方法放这
    // ====================

    /**
     *  兜底上报
     *
     */
    protected function vivo($channel = 'vivo') {
//        [$reportField, $activeField] = VivoService::reportField($channel);

        $startTime = strtotime(date('Y-m-d'));
        $endTime   = time();
        // 先查询未回传的前10%数量
        $topN = Db::name('report_vivo_data')
            ->alias('c')
            ->leftJoin('user u', 't.oaid = c.oaid')
            ->where('u.is_reportvivo', 0)
            ->where('u.channel', $channel)
            ->where('u.active_time', '>=', $startTime)
            ->where('u.active_time', '<', $endTime)
            ->value('CEIL(COUNT(DISTINCT c.oaid) * 0.1)');
        if ($topN <= 0) {
            var_dump('topN is empty');
            return;
        }

        $config = VivoService::getAdReportConfig();
        $amount = $config['ecpm_report_amount'];
        // 查询大于 配置的金额
        $list = Db::name('report_vivo_data')
            ->field('v.app_id, r.pkg_name, v.oaid')
            ->alias('r')
            ->join('user v', 'v.oaid = r.oaid AND v.is_report = 0')
            ->field([
                'r.oaid',
                'COUNT(*)'    => 'cnt',
                'SUM(r.ecpm)' => 'ecpm_sum',
            ])
            ->where('channel', $channel)
            ->where('r.active_time', '>=', $startTime)
            ->where('r.action', '=', VivoService::AD_SHOW_ACTION)
            ->group('r.oaid')
            ->having('SUM(r.ecpm) > ' . $amount)
            ->order('cnt', 'desc')
            ->limit($topN)
            ->select()
            ->toArray();

        foreach ($list as $app) {
            $appId   = trim($app['app_id'] ?? '');
            $pkgName = trim($app['pkg_name'] ?? '');
            $oaid    = $app['oaid'] ?? '';
            if ($appId === '' || $pkgName === '') {
                continue;
            }

            try {
                VivoService::fallbackReport($oaid, $pkgName, $appId, $channel);
            } catch (\Throwable $e) {
                Log::error(sprintf(
                    $channel .' fallback report error: %s file:%s line:%s',
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ));
            }
        }
    }
}
