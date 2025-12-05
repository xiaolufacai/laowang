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
        $appId = 'wxe4f024f4e62bd3d4';
        $url   = 'https://api.nuobt.site/index/wechat/callback';
        $authUrl = WechatService::getOAuthUrl($appId, $url);
        var_dump($authUrl);
    }

    public function wxd() {
        $code = '031sGPkl2pkaMg4ORfol2izmAz3sGPkR';
        $appId = 'wxe4f024f4e62bd3d4';
        var_dump($appId);
        $data = WechatService::getoAuthAccessToken($appId, $code);
        var_dump($data);
    }

    public function wxu() {
        $openid = 'or_fX63NPDOPlh7vVScWVJ4JOb4o';
        $at     = '98_xPBZ7s4ckmgz2hn4uRg1x_UrAZpxLjPm0xVKG_1R8-RQvmLATdxGNlyibuCVhtSL3lclO8BHq827oF_4WscofI50rykZNz1peE4I9b2vue8';
        $user   = WechatService::getUserInfo($at, $openid);
        var_dump($user);
    }
}
