<?php
declare(strict_types=1);

namespace app\command;

use app\index\service\OrderService;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;

class OrderTest extends Command {
    protected function configure() {
        $this->setName('order')
            ->setDescription('订单接口测试命令')
            ->addArgument('action', Argument::OPTIONAL, '操作方法');
    }

    protected function execute(Input $input, Output $output) {
        $action = $input->getArgument('action');

        if (!$action) {
            $output->writeln('');
            $output->writeln('========================================');
            $output->writeln('         订单接口测试命令');
            $output->writeln('========================================');
            $output->writeln('');
            $output->writeln('可用操作:');
            $output->writeln('  create   - 创建订单');
            $output->writeln('  list     - 订单列表');
            $output->writeln('  detail   - 订单详情');
            $output->writeln('  cancel   - 取消订单');
            $output->writeln('');
            $output->writeln('使用方法: php8 think order <action>');
            $output->writeln('示例: php8 think order create');
            $output->writeln('');
            return;
        }

        if (!method_exists($this, $action)) {
            $output->writeln("<error>操作 [{$action}] 不存在</error>");
            return;
        }

        try {
            $this->$action($input, $output);
        } catch (\Throwable $e) {
            $output->writeln("<error>错误: " . $e->getMessage() . "</error>");
            $output->writeln("<comment>文件: " . $e->getFile() . ":" . $e->getLine() . "</comment>");
        }
    }

    /**
     * 创建订单
     */
    protected function create(Input $input, Output $output) {
        $output->writeln('');
        $output->writeln('======== 创建订单 ========');
        $output->writeln('');

        // 交互式输入参数
        $userId = $this->askInput($input, $output, '请输入用户ID', true);
        $vipId  = $this->askInput($input, $output, '请输入会员类型ID (vip_id)', true);
        $appId  = $this->askInput($input, $output, '请输入App ID', true);

        $output->writeln('');
        $output->writeln('-------- 参数确认 --------');
        $output->writeln("用户ID: {$userId}");
        $output->writeln("会员类型ID: {$vipId}");
        $output->writeln("App ID: {$appId}");
        $output->writeln('');

        // 执行下单
        $result = OrderService::setOrder((int)$userId, (int)$vipId, (int)$appId);

        $output->writeln('-------- 返回结果 --------');
        $output->writeln(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $output->writeln('');
    }

    /**
     * 订单列表
     */
    protected function list(Input $input, Output $output) {
        $output->writeln('');
        $output->writeln('======== 订单列表 ========');
        $output->writeln('');

        // 交互式输入参数
        $userId   = $this->askInput($input, $output, '请输入用户ID', true);
        $appId    = $this->askInput($input, $output, '请输入App ID', true);
        $page     = $this->askInput($input, $output, '请输入页码', false, '1');
        $pageSize = $this->askInput($input, $output, '请输入每页条数', false, '10');
        $status   = $this->askInput($input, $output, '请输入支付状态(0待支付/1已支付/2已取消/3已完成, 留空查全部)', false, '');

        $output->writeln('');
        $output->writeln('-------- 查询参数 --------');
        $output->writeln("用户ID: {$userId}");
        $output->writeln("App ID: {$appId}");
        $output->writeln("页码: {$page}");
        $output->writeln("每页条数: {$pageSize}");
        $output->writeln("支付状态: " . ($status === '' ? '全部' : $status));
        $output->writeln('');

        // 执行查询
        $result = OrderService::orders(
            (int)$userId,
            (int)$appId,
            (int)$page,
            (int)$pageSize,
            $status === '' ? null : (int)$status
        );

        $output->writeln('-------- 返回结果 --------');
        $output->writeln(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $output->writeln('');
    }

    /**
     * 订单详情
     */
    protected function detail(Input $input, Output $output) {
        $output->writeln('');
        $output->writeln('======== 订单详情 ========');
        $output->writeln('');

        // 交互式输入参数
        $userId  = $this->askInput($input, $output, '请输入用户ID', true);
        $orderId = $this->askInput($input, $output, '请输入订单ID', true);

        $output->writeln('');
        $output->writeln('-------- 查询参数 --------');
        $output->writeln("用户ID: {$userId}");
        $output->writeln("订单ID: {$orderId}");
        $output->writeln('');

        // 执行查询
        $result = OrderService::detail((int)$userId, (int)$orderId);

        $output->writeln('-------- 返回结果 --------');
        $output->writeln(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $output->writeln('');
    }

    /**
     * 取消订单
     */
    protected function cancel(Input $input, Output $output) {
        $output->writeln('');
        $output->writeln('======== 取消订单 ========');
        $output->writeln('');

        // 交互式输入参数
        $userId  = $this->askInput($input, $output, '请输入用户ID', true);
        $orderId = $this->askInput($input, $output, '请输入订单ID', true);

        $output->writeln('');
        $output->writeln('-------- 参数确认 --------');
        $output->writeln("用户ID: {$userId}");
        $output->writeln("订单ID: {$orderId}");
        $output->writeln('');

        // 执行取消
        $result = OrderService::cancel((int)$userId, (int)$orderId);

        $output->writeln('-------- 返回结果 --------');
        $output->writeln(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $output->writeln('');
    }

    /**
     * 交互式输入
     *
     * @param Input  $input
     * @param Output $output
     * @param string $prompt 提示信息
     * @param bool   $required 是否必填
     * @param string $default 默认值
     * @return string
     */
    protected function askInput(Input $input, Output $output, string $prompt, bool $required = false, string $default = ''): string {
        $hint = $default ? " (默认: {$default})" : '';
        $output->write("<info>{$prompt}{$hint}:</info> ");

        $value = trim(fgets(STDIN));

        if ($value === '') {
            if ($default) {
                return $default;
            }
            if ($required) {
                $output->writeln('<error>该参数为必填项，请重新输入</error>');
                return $this->askInput($input, $output, $prompt, $required, $default);
            }
        }

        return $value;
    }
}