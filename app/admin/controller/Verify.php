<?php

namespace app\admin\controller;


use think\captcha\facade\Captcha;
use think\Response;

class Verify {
    /**
     * 生成验证码
     *
     * @return array|Response
     */
    public function code(): array|Response {
        return Captcha::create();
    }
}
