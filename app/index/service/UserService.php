<?php

namespace app\index\service;


use app\common\model\App;
use app\common\model\User;
use app\common\service\WechatService;
use app\middleware\JWTAuthMiddleware;
use Firebase\JWT\JWT;
use think\db\exception\DbException;
use think\facade\Db;
use think\facade\Request;

class UserService {

    public static function create($data = []) {
        if (empty($data)) {
            $data = Request::post();
            // 包ID
            $data['app_id'] = Request::header('appId');
            // 设备ID
            $data['client_id'] = Request::header('deviceNum');
            // 手机品牌
            $data['mobile_brand'] = Request::header('mobile_brand');
            // 手机型号
            $data['mobile_model'] = Request::header('mobileModel');
            // 获取package_id
            $app = App::where(['app_id' => $data['app_id'], 'status' => App::STATUS_NORMAL])->find();
            if (empty($app)) {
                return ['error' => 1, 'message' => 'APP ID NOT FOUND', 'data' => []];
            }
            $data['package_id'] = $app['id'];
        }
        // 必须要有 client_id 才能进行判断
        if (!empty($data['client_id'])) {
            // 查找是否已经存在该 client_id 的记录
            $user = User::where(['client_id' => $data['client_id'], 'app_id' => $data['app_id']])->find();

            if ($user) {
                // 判断当前client_id是否使用微信登录，如果是则查询同一个APP ID下最先使用微信登录的openid 为主账号
                if (!empty($user->openid)) {
                    $user = User::where(['app_id' => $data['app_id'], 'openid' => $user->openid, 'status' => 0])->order('id asc')->find();
                } else {
                    // 如果记录存在，遍历数据并更新变化的字段
                    $updateData = [];
                    foreach ($data as $key => $value) {
                        // 如果字段值发生变化，进行更新
                        if ($user[$key] != $value) {
                            $updateData[$key] = $value;
                        }
                    }
                    // 如果有更新的字段
                    if ($updateData) {
                        $updateData['update_time'] = date('Y-m-d H:i:s'); // 更新 `update_time`
                        $user->save($updateData);
                    }
                }
            } else {
                // 如果没有记录，进行新增操作
                $data['create_time'] = date('Y-m-d H:i:s');
                $data['update_time'] = date('Y-m-d H:i:s');
                // 进行新增
                $user = User::create($data);
            }
            session('uid', $user->id);
            return ['error' => 0, 'message' => 'OK', 'data' => ['uid' => $user->id]];
        } else {
            // 没有 client_id 时返回错误
            return ['error' => 1, 'message' => 'CLIENT ID 有误', 'data' => ['uid' => 0]];
        }
    }

    /**
     * 生成token
     *
     * @param $uid
     * @return string
     */
    public static function token($uid): string {
        // 生成 JWT token
        $issuedAt       = time();
        $expirationTime = $issuedAt + 60 * 24 * 60 * 60;  // 7天后过期
        $payload        = [
            'iss' => 'lao-wang-publisher-issuer',      // 发行者
            'aud' => 'lao-wang-user-audience',    // 用户
            'iat' => $issuedAt,          // 发布时间
            'exp' => $expirationTime,    // 过期时间
            'uid' => $uid,    // 用户ID
        ];

        // 使用 HS256 算法生成 JWT token
        return JWT::encode($payload, JWTAuthMiddleware::KEY, 'HS256');
    }

    /**
     *  微信登录
     *
     * @param $data
     * @return array
     */
    public static function wechatLogin($data) {
        if (empty($data['code'])) {
            return ['error' => 1, 'message' => 'CODE ERROR', 'data' => []];
        }

        if (empty($data['wx_app_id'])) {
            return ['error' => 1, 'message' => 'APP ID ERROR', 'data' => []];
        }

        // 根据code获取用户信息
        $resp = WechatService::getoAuthAccessToken($data['wx_app_id'], $data['code']);
        if (empty($resp['errcode']) || empty($resp['openid'])) {
            return ['error' => 1, 'message' => $resp['errmsg'], 'data' => []];
        }
        // 根据openid获取用户信息
        $wxUser = WechatService::getUserInfo($resp['access_token'], $resp['openid']);
        if (!empty($wxUser['errcode']) || empty($wxUser['openid'])) {
            return ['error' => 1, 'message' => $wxUser['errmsg'], 'data' => []];
        }

        // 判断当前client是否已经注册过
        $data['openid']   = $wxUser['openid'];
        $data['avatar']   = $wxUser['headimgurl'];
        $data['nickname'] = $wxUser['nickname'];
        // 包ID
        $data['app_id'] = Request::header('appId');
        // 设备ID
        $data['client_id'] = Request::header('deviceNum');
        // 手机品牌
        $data['mobile_brand'] = Request::header('mobileBrand');
        // 手机型号
        $data['mobile_model'] = Request::header('mobileModel');
        // 获取package_id
        $app = App::where(['app_id' => $data['app_id'], 'status' => App::STATUS_NORMAL])->find();
        if (empty($app)) {
            return ['error' => 1, 'message' => 'APP ID NOT FOUND', 'data' => []];
        }
        $data['package_id'] = $app['id'];
        $data['unionid']    = $wxUser['unionid'] ?? '';

        return self::create($data);
    }
}
