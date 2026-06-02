<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义
    'commands' => [
        'pk'    => \app\command\Pk::class,
        'order' => \app\command\OrderTest::class,
        'task'  => \app\command\Task::class,
    ],
];
