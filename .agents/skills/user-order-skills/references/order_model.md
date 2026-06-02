# 订单表的结构
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `app_id` int(11) NOT NULL DEFAULT '0' COMMENT '包ID',
  `amount` decimal(10,2) DEFAULT NULL COMMENT '金额',
  `platform` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '支付平台',
  `pay_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '支付类型 1：微信 2：支付宝',
  `pay_status` tinyint(1) DEFAULT NULL COMMENT '支付状态 0：未支付 1：已支付 2：已取消',
  `status` tinyint(1) DEFAULT NULL COMMENT '状态 0：正常 1：删除',
  `pay_time` datetime DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id_idx` (`user_id`) USING BTREE,
  KEY `app_id_idx` (`app_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC

