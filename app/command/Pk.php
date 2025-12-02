<?php
declare(strict_types=1);

namespace app\command;

use app\common\service\WechatService;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;

class Pk extends Command {
    protected function configure() {
        $this->setName('pk')
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
    protected function detail($id, $type = 'default') {
        return "detail => id={$id}, type={$type}";
    }

    protected function apps() {
        var_dump(WechatService::apps());
    }
}
