<?php
declare (strict_types = 1);

namespace app\index\service;

use app\common\model\AppVip;
use app\common\model\User;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;
use think\model\Collection;
use think\facade\Db;

/**
 * 会员服务
 */
class VipService {

    /**
     * 查询包VIP信息
     *
     * @param $appId
     * @return \think\Collection|Collection
     */
    public static function appVips($appId): Collection|\think\Collection {
        return AppVip::where(['app_id' => $appId])->select();
    }

    /**
     * 获取会员实际金额和原始金额
     *
     * @param int $vipId app_vip表的ID
     * @return array
     */
    public static function amount(int $vipId): array {
        $vipInfo = AppVip::where('id', $vipId)->find();

        if (!$vipInfo) {
            return ['error' => 1, 'message' => '会员类型不存在'];
        }

        return [
            'error'   => 0,
            'message' => '',
            'data'    => [
                'amount'          => $vipInfo->new_price,     // 实际支付金额
                'original_amount' => $vipInfo->old_price,     // 原始金额
                'vip_type'        => $vipInfo->vip,           // 会员类型
            ]
        ];
    }

    /**
     * 根据会员类型，进行会员时间开通
     *
     * @param int $vipId app_vip表的ID
     * @return array
     */
    public static function open(int $vipId): array {
        $vipInfo = AppVip::where('id', $vipId)->find();

        if (!$vipInfo) {
            return ['error' => 1, 'message' => '会员类型不存在'];
        }

        // 获取会员时长（天数）
        $vipDays = self::getVipDays($vipInfo->vip);

        if ($vipDays <= 0) {
            return ['error' => 1, 'message' => '会员时长配置错误'];
        }

        // 计算会员到期时间
        $expireTime = date('Y-m-d H:i:s', time() + ($vipDays * 86400));

        // 更新用户会员状态
        $user = User::where('id', $vipInfo->user_id)->find();
        if (!$user) {
            return ['error' => 1, 'message' => '用户不存在'];
        }

        // 如果用户已有会员，则延长会员时间
        if ($user->vip_expire_time && strtotime($user->vip_expire_time) > time()) {
            $expireTime = date('Y-m-d H:i:s', strtotime($user->vip_expire_time) + ($vipDays * 86400));
        }

        $user->vip_status     = 1;  // 会员状态：已开通
        $user->vip_type       = $vipInfo->vip;  // 会员类型
        $user->vip_expire_time = $expireTime;
        $user->update_time    = date('Y-m-d H:i:s');

        if ($user->save()) {
            return ['error' => 0, 'message' => '会员开通成功', 'data' => ['expire_time' => $expireTime]];
        }

        return ['error' => 1, 'message' => '会员开通失败'];
    }

    /**
     * 根据会员类型，进行会员时间取消开通
     *
     * @param int $vipId app_vip表的ID
     * @return array
     */
    public static function close(int $vipId): array {
        $vipInfo = AppVip::where('id', $vipId)->find();

        if (!$vipInfo) {
            return ['error' => 1, 'message' => '会员类型不存在'];
        }

        // 取消用户会员状态
        $user = User::where('id', $vipInfo->user_id)->find();
        if (!$user) {
            return ['error' => 1, 'message' => '用户不存在'];
        }

        $user->vip_status      = 0;  // 会员状态：已取消
        $user->vip_expire_time = null;
        $user->update_time     = date('Y-m-d H:i:s');

        if ($user->save()) {
            return ['error' => 0, 'message' => '会员取消成功'];
        }

        return ['error' => 1, 'message' => '会员取消失败'];
    }

    /**
     * 根据会员类型获取会员时长（天数）
     *
     * @param int $vipType 会员类型
     * @return int
     */
    private static function getVipDays(int $vipType): int {
        $vipDaysMap = [
            1 => 30,   // 月会员
            2 => 90,   // 季度会员
            3 => 365,  // 年会员
            4 => 9999, // 永久会员
        ];

        return $vipDaysMap[$vipType] ?? 0;
    }

    /**
     * 检查用户会员状态
     *
     * @param int $userId 用户ID
     * @return array
     */
    public static function checkVipStatus(int $userId): array {
        $user = User::where('id', $userId)->find();

        if (!$user) {
            return ['error' => 1, 'message' => '用户不存在', 'data' => ['is_vip' => false]];
        }

        // 检查会员是否过期
        $isVip = false;
        if ($user->vip_status == 1 && $user->vip_expire_time) {
            if (strtotime($user->vip_expire_time) > time()) {
                $isVip = true;
            } else {
                // 会员已过期，更新状态
                $user->vip_status = 0;
                $user->save();
            }
        }

        return [
            'error'   => 0,
            'message' => '',
            'data'    => [
                'is_vip'        => $isVip,
                'vip_type'      => $user->vip_type ?? 0,
                'vip_expire_time' => $user->vip_expire_time ?? '',
            ]
        ];
    }
}
