<?php

namespace app\index\service;


use app\common\model\User;
use app\middleware\JWTAuthMiddleware;
use Firebase\JWT\JWT;
use think\db\exception\DbException;
use think\facade\Db;
use think\facade\Request;

class UserService {

    public static function create() {
        $data = Request::post();
        // 必须要有 client_id 才能进行判断
        if (!empty($data['client_id'])) {
            // 查找是否已经存在该 client_id 的记录
            $user = User::where('client_id', $data['client_id'])->find();

            if ($user) {
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
            } else {
                // 如果没有记录，进行新增操作
                $data['create_time'] = date('Y-m-d H:i:s');
                $data['update_time'] = date('Y-m-d H:i:s');
                // 进行新增
                $user = User::create($data);
            }
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
        $expirationTime = $issuedAt + 7 * 24 * 60 * 60;  // 7天后过期
        $payload        = [
            'iss' => 'laowang-publisher-issuer',      // 发行者
            'aud' => 'laowang-user-audience',    // 用户
            'iat' => $issuedAt,          // 发布时间
            'exp' => $expirationTime,    // 过期时间
            'uid' => $uid,    // 用户ID
        ];

        // 使用 HS256 算法生成 JWT token
        return JWT::encode($payload, JWTAuthMiddleware::KEY, 'HS256');
    }
}
