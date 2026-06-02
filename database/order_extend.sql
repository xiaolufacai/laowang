-- 订单表扩展字段
-- 用于支持会员开通、支付类型等功能

ALTER TABLE `orders` ADD COLUMN `order_no` VARCHAR(64) DEFAULT '' COMMENT '订单号' AFTER `id`;
ALTER TABLE `orders` ADD COLUMN `vip_id` INT(11) NOT NULL DEFAULT 0 COMMENT '会员类型ID（app_vip表ID）' AFTER `app_id`;
ALTER TABLE `orders` ADD COLUMN `subject` VARCHAR(255) DEFAULT '' COMMENT '订单标题' AFTER `vip_id`;
ALTER TABLE `orders` ADD COLUMN `original_amount` DECIMAL(10,2) DEFAULT 0.00 COMMENT '原始订单金额' AFTER `amount`;
ALTER TABLE `orders` ADD COLUMN `transaction_id` VARCHAR(128) DEFAULT '' COMMENT '第三方交易号' AFTER `pay_status`;
ALTER TABLE `orders` ADD COLUMN `notify_data` LONGTEXT COMMENT '回调原始数据' AFTER `transaction_id`;

-- 修改支付类型字段注释
ALTER TABLE `orders` MODIFY COLUMN `pay_type` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '支付类型 1:微信 2:支付宝';

-- 修改支付状态字段注释
ALTER TABLE `orders` MODIFY COLUMN `pay_status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '支付状态 0:待支付 1:已支付 2:已取消 3:已完成';

-- 添加索引
ALTER TABLE `orders` ADD INDEX `idx_order_no` (`order_no`);
ALTER TABLE `orders` ADD INDEX `idx_pay_status` (`pay_status`);
ALTER TABLE `orders` ADD INDEX `idx_create_time` (`create_time`);

-- 订单操作日志表
CREATE TABLE IF NOT EXISTS `order_log` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `order_id` INT(11) NOT NULL DEFAULT 0 COMMENT '订单ID',
    `operation` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '操作类型',
    `message` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '操作说明',
    `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    INDEX `idx_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单操作日志表';

-- 支付回调日志表
CREATE TABLE IF NOT EXISTS `pay_notify_log` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `channel` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '支付渠道 wechat/alipay',
    `order_no` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '订单号',
    `notify_data` LONGTEXT COMMENT '回调原始数据',
    `create_time` DATETIME DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`),
    INDEX `idx_order_no` (`order_no`),
    INDEX `idx_channel` (`channel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付回调日志表';