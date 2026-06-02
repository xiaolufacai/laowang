<?php
declare(strict_types = 1);

namespace app\common\model;

use think\Model;

/**
 * @mixin \think\Model
 */
class Order extends Model {
    // 指定表名（不含前缀）
    protected $name = 'orders';

    // 支付状态常量
    const PAY_STATUS_PENDING   = 0; // 待支付
    const PAY_STATUS_PAID      = 1; // 已支付
    const PAY_STATUS_CANCELLED = 2; // 已取消
    const PAY_STATUS_COMPLETED = 3; // 已完成

    // 支付类型常量
    const PAY_TYPE_WECHAT  = 1; // 微信支付
    const PAY_TYPE_ALIPAY  = 2; // 支付宝支付

    // 订单状态常量
    const STATUS_NORMAL   = 0; // 正常
    const STATUS_DELETED  = 1; // 已删除

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 类型转换
    protected $type = [
        'amount' => 'float',
    ];

    /**
     * 获取支付状态文本
     *
     * @param int $status
     * @return string
     */
    public static function getPayStatusText(int $status): string {
        $texts = [
            self::PAY_STATUS_PENDING   => '待支付',
            self::PAY_STATUS_PAID      => '已支付',
            self::PAY_STATUS_CANCELLED => '已取消',
            self::PAY_STATUS_COMPLETED => '已完成',
        ];
        return $texts[$status] ?? '未知状态';
    }

    /**
     * 获取支付类型文本
     *
     * @param int $type
     * @return string
     */
    public static function getPayTypeText(int $type): string {
        $texts = [
            self::PAY_TYPE_WECHAT => '微信支付',
            self::PAY_TYPE_ALIPAY => '支付宝支付',
        ];
        return $texts[$type] ?? '未知类型';
    }

    /**
     * 关联用户
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 关联AppVip
     */
    public function appVip() {
        return $this->belongsTo(AppVip::class, 'vip_id', 'id');
    }
}