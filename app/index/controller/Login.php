<?php

namespace app\index\controller;


use app\index\service\UserService;
use app\IndexBaseController;
use app\middleware\JWTAuthMiddleware;
use Firebase\JWT\JWT;
use think\facade\Request;
use think\response\Json;

class Login {

    /**
     * 登录
     *
     * @return Json
     */
    public function device(): Json {
        $res = UserService::create();
        if ($res['error']) {
            // 返回生成的 token
            return \json(['code' => 1, 'message' => $res['message'], 'data' => $res['data']]);
        }
        $jwt = UserService::token($res['data']['uid']);
        UserService::setLoginType($res['data']['uid'], UserService::CLIENT_ID_LOGIN);
        return \json(['code' => 200, 'message' => $res['message'], 'data' => ['token' => $jwt]]);
    }

    /**
     *  微信登录
     *
     * @return Json
     */
    public function wechat(): Json {
        $post = Request::post();
        $res  = UserService::wechatLogin($post);
        if ($res['error']) {
            // 返回生成的 token
            return \json(['code' => 1, 'message' => $res['message'], 'data' => $res['data']]);
        }
        $jwt = UserService::token($res['data']['uid']);
        UserService::setLoginType($res['data']['uid'], UserService::WECHAT_LOGIN);
        return \json(['code' => 200, 'message' => $res['message'], 'data' => ['token' => $jwt]]);
    }
}
