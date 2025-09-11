-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2025-08-06 09:51:17
-- 服务器版本： 5.7.44-log
-- PHP 版本： 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `laowang`
--

-- --------------------------------------------------------

--
-- 表的结构 `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '用户名',
  `password` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '密码',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态 0：正常 1：删除 2：禁用',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `status`, `create_time`, `update_time`) VALUES
(1, 'admin', 'e10adc3949ba59abbe56e057f20f883e', 0, '2025-02-12 23:28:25', '2025-02-12 23:28:27');

-- --------------------------------------------------------

--
-- 表的结构 `agreement`
--

CREATE TABLE `agreement` (
  `id` int(11) NOT NULL,
  `app_id` int(11) NOT NULL COMMENT '包ID',
  `user_agreement` longtext NOT NULL COMMENT '用户协议',
  `privacy_agreement` longtext NOT NULL COMMENT '隐私协议'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='协议';

--
-- 转存表中的数据 `agreement`
--

INSERT INTO `agreement` (`id`, `app_id`, `user_agreement`, `privacy_agreement`) VALUES
(1, 5, '<p>日日日日日日日日日日日日日日日日666</p>', '<p>踩踩踩踩踩踩踩踩踩踩踩踩踩踩踩踩踩踩从</p>'),
(2, 3, '<p>网页搜索如何发起投诉？</p><p><strong>1. &nbsp;</strong><strong>&nbsp;快照删除与更新内容投诉：</strong><br/>第一步：登录百度账号 , &nbsp;找到想要投诉的搜索结果点击 “ 百度快照 ”-- 在打开的百度快照页面上方点击 “ 投诉快照 ” ，或 前往<a href=\"https://help.baidu.com/newadd?prod_id=1&amp;category=1\" target=\"_blank\"><strong>百度服务中心</strong></a>发起投诉 &nbsp; &nbsp;<br/>第二步：复制百度快照地址（如果通过 “ 百度快照 ” 点击 “ 投诉快照 ” 进入投诉页面，快照地址会自动代入，请忽略这步）<br/>第三步：填写有效邮箱<br/>第四步：提交<br/><strong>2. &nbsp;</strong><strong>&nbsp;下拉词、为您推荐词、相关搜索词投诉 &nbsp;</strong><br/>登录百度账号 , 在&nbsp;<a href=\"https://help.baidu.com/newadd?prod_id=1&amp;category=6\" target=\"_self\"><strong>百度服务中心</strong></a>发起投诉。 &nbsp;<br/>第一步：填写搜索关键词<br/>第二步：选择投诉提示词种类 &nbsp;<br/>第三步：填写需要处理的词条，多个词条用 \";\" 隔开 &nbsp;<br/>第四步：填写有效邮箱 &nbsp;<br/>第五步：对投诉内容进行描述 &nbsp;<br/>第六步：点击提交图片（需提供的必要截图或资质） &nbsp;<br/>第七步：提交 &nbsp; &nbsp;<br/><strong>3. &nbsp;</strong><strong>&nbsp;不良信息内容举报 &nbsp; &nbsp;</strong><br/>请先登录百度账号。 &nbsp; &nbsp;<br/>第一步：在百度搜索页面找到你要举报的搜索结果 &nbsp; &nbsp;<br/>第二步：点击搜索结果后 “ 倒三角标志 ”---- &nbsp;选择 “ 举报 ” &nbsp; &nbsp;<br/>第三步：填写并提交举报内容 &nbsp; &nbsp;<br/><strong>4. &nbsp;&nbsp;</strong><strong>百度搜索移动端内容 投诉 &nbsp; &nbsp;</strong><br/>第一步： 打开手机百度或在手机浏览器中打开百度 &nbsp; &nbsp;<br/>第二步： 在百度搜索页面找到你要举报的搜索结果 &nbsp; &nbsp; &nbsp;<br/>第三步：在页面底部点击 “ 用户反馈 ” &nbsp; &nbsp; &nbsp;<br/>第四步：点击您要投诉的内容旁的蓝色圆形标志 &nbsp; &nbsp; &nbsp; &nbsp;<br/>第五步： 对投诉内容进行描述 &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<br/>第六步：点击提交图片（需提供的必要截图或资质） &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<br/>第七步：填写有效邮箱 &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<br/>第八步：提交</p>', '<p><b>1.不能通过“www.baidu.com”访问百度</b><br/>请先确认其他站点是否能够正常访问。确定网络无故障后，请使用IP地址    <a href=\"http://202.108.22.5/\" target=\"_blank\">http://202.108.22.5/</a>    访问百度，如果可以访问则请在您的电脑中   查找hosts文件   ，用文本编辑器打开，查看是否有 www.baidu.com 的记录项，如有请删除该记录，并重新启动浏览器。<br/>如若还不能访问请继续以下步骤，请把您的DNS设置为我们提供的两个DNS：202.106.0.20和202.106.196.115；（此为网通的DNS，不会影响您访问其他网站，请您放心使用！）具体位置：网络连接－本地连接－常规－属性－双击internet协议（tcp/ip）－修改DNS。    <br/><b>2.访问百度时，出现异常广告、弹窗、跳转等不正常情况</b><br/>具体现象包括：<br/>1）访问www.baidu.com时，跳转到其他网站。<br/>2） 在百度搜索关键词，出现的是其他搜索引擎的搜索结果。<br/>3）访问百度首页或者搜索结果页时，页面上出现异常浮动广告或者弹出广告页面。<br/>4）当您正常使用浏览器浏览网页时，页面会无故跳转到百度网页。<br/>首先百度绝不会在用户不知情的情况下擅自弹出或跳转到影响用户体验的不友好页面，也不会放置影响用户体验的广告。<br/>对于您的这种现象，我们初步判断是您在上网时不小心安装了一些病毒软件，这些软件一般都是在您不知情的情况下与其他免费软件捆绑在一起下载的，它们恶意篡改了您的系统信息，导致了您访问百度时出现异常情况。<br/>我们建议您使用 最新版百度卫士 进行电脑体检，体检结束后进行“一键优化”。如果问题仍未解决，请使用百度卫士的“系统修复”功能：打开百度卫士----安全维护----系统修复。<br/><b>3.以上方法不能解决问题?</b><br/>若以上方法不能帮您解决问题，请提供以下信息，并将执行结果发送至visit@baidu.com，以便我们能更好的为您分析问题。<br/>1.请您详细描述访问百度产品时出现的异常并提供异常页面的截图，以及描述一下您的网络环境（如您所在的地区、上网方式、公网IP地址、代理IP地址、以及您周围人群访问相同页面时的状况等）<br/>2.请您点击“开始”-“运行”-输入cmd-确定，分别执行ipconfig/all命令和ping www.baidu.com命令以及nslookup www.baidu.com命令和tracert www.baidu.com命令，并在地址栏中输入<a href=\"http://202.108.22.5/s?wd=a&amp;cl=3\" target=\"_blank\">http://202.108.22.5/s?wd=a&amp;cl=3</a> ，将以上5个操作的执行结果保存（截图、文本均可）发送给我们。<br/></p>');

-- --------------------------------------------------------

--
-- 表的结构 `app`
--

CREATE TABLE `app` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `name` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '包名称',
  `repository` text CHARACTER SET utf8 NOT NULL COMMENT '仓库地址',
  `package_url` text CHARACTER SET utf8 NOT NULL COMMENT '打包平台地址',
  `ad_id` text CHARACTER SET utf8 NOT NULL COMMENT '广告ID',
  `ym_id` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '友盟ID',
  `wx_id` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '微信ID',
  `app_id` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'APP ID',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0：正常 1：删除',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `app`
--

INSERT INTO `app` (`id`, `user_id`, `name`, `repository`, `package_url`, `ad_id`, `ym_id`, `wx_id`, `app_id`, `status`, `create_time`, `update_time`) VALUES
(1, 0, '1', '2', '3', '34', '4', '4', '4', 0, '2025-02-09 01:36:43', '2025-02-09 01:36:44'),
(2, 0, '2344', '234234', '234234', '234g234', '234324', '234', '234234', 0, '2025-02-09 01:37:57', '2025-02-09 01:37:58'),
(3, 0, '包名称', '仓库地址', '打包平台地址', '广告ID:  ADDD\n广告ID:  CCCCC\n', '友盟ID', '微信ID', '微信ID', 0, '2025-02-09 02:01:59', '2025-02-09 02:02:00'),
(4, 0, 'AAA', 'BBBB', 'CCCC', 'CCCC', 'CCC', 'CCC', 'CC', 0, '2025-02-09 02:02:50', '2025-02-09 02:02:51'),
(5, 0, 'dwfwerw', 'rewr', 'werw', 'erwer', 'werwer', 'wer', 'werwer', 0, '2025-02-09 02:04:49', '2025-02-09 02:04:50'),
(6, 0, 'com.nbt.tuner', 'com.xxx.git', 'com.xxx.jenkins', 'xxx1\nxxx2\nxxx3', 'xxx2', 'xxx3', 'xxx4', 0, '2025-08-01 11:31:35', '2025-08-01 11:31:35');

-- --------------------------------------------------------

--
-- 表的结构 `app_channel`
--

CREATE TABLE `app_channel` (
  `id` int(11) NOT NULL,
  `app_id` int(11) NOT NULL DEFAULT '0' COMMENT '包ID',
  `channel` varchar(255) NOT NULL COMMENT '渠道',
  `version_no` varchar(255) NOT NULL COMMENT '版本号',
  `version_name` varchar(255) NOT NULL COMMENT '版本名称',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '审核状态 0：审核中【默认】 1：审核通过',
  `list_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '上架状态 0：下架 1：上架',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `app_channel`
--

INSERT INTO `app_channel` (`id`, `app_id`, `channel`, `version_no`, `version_name`, `status`, `list_status`, `create_time`, `update_time`) VALUES
(1, 1, 'xiaomi', '1.0.0.1', '版本1', 0, 0, '2025-02-11 14:21:09', '2025-02-11 15:37:37'),
(2, 1, 'oppo', '1.0.01', 'V1', 1, 0, '2025-02-11 15:29:25', '2025-02-11 15:37:30'),
(3, 5, 'oppo', '1.1.1', 'V111', 0, 0, '2025-02-11 21:17:40', '2025-02-11 21:17:40'),
(4, 0, 'vivo', '222', 'V23', 0, 0, '2025-02-11 22:23:45', '2025-02-11 22:23:45'),
(5, 5, 'vivo', '1.1.2', 'v112', 1, 1, '2025-02-11 22:27:20', '2025-02-11 23:20:26'),
(6, 5, 'huawei', 'W', 'QQQ', 0, 0, '2025-02-11 22:30:19', '2025-02-11 22:30:19'),
(7, 6, 'test', '1', '1.0.0', 0, 1, '2025-08-01 11:34:09', '2025-08-01 15:28:55');

-- --------------------------------------------------------

--
-- 表的结构 `app_vip`
--

CREATE TABLE `app_vip` (
  `id` int(11) NOT NULL,
  `app_id` int(11) DEFAULT '0' COMMENT '包ID',
  `vip` varchar(255) DEFAULT NULL COMMENT 'VIP类型',
  `old_price` decimal(10,2) DEFAULT NULL COMMENT '原价',
  `new_price` decimal(10,2) DEFAULT NULL COMMENT '现价',
  `corner_text` varchar(255) DEFAULT NULL COMMENT '角标文案',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `app_vip`
--

INSERT INTO `app_vip` (`id`, `app_id`, `vip`, `old_price`, `new_price`, `corner_text`, `create_time`, `update_time`) VALUES
(5, 5, 'year', '234.00', '23434.00', '234', '2025-02-12 00:23:12', '2025-02-12 00:23:12'),
(11, 3, 'year', '1111.00', '11111.00', '1111111111', '2025-02-12 00:47:56', '2025-02-12 00:47:56'),
(12, 4, 'all', '9999.00', '999.00', '打折了', '2025-02-12 13:03:30', '2025-02-12 13:03:31'),
(13, 6, 'all', '128.00', '99.00', '限时优惠', '2025-08-01 11:35:07', '2025-08-01 11:35:07');

-- --------------------------------------------------------

--
-- 表的结构 `info_config`
--

CREATE TABLE `info_config` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT '用户ID',
  `key` varchar(255) DEFAULT NULL COMMENT '键',
  `value` text COMMENT '值',
  `channel` varchar(255) DEFAULT NULL COMMENT '渠道ID',
  `start_version` varchar(255) DEFAULT NULL COMMENT '最小支持版本',
  `end_version` varchar(255) DEFAULT NULL COMMENT '最大支持版本',
  `description` text COMMENT '描述',
  `is_lock` tinyint(1) DEFAULT NULL COMMENT '锁定状态 0：未锁定 1：锁定',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态 0：正常 1：删除',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `order`
--

CREATE TABLE `order` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `app_id` int(11) NOT NULL DEFAULT '0' COMMENT '包ID',
  `amount` decimal(10,2) DEFAULT NULL COMMENT '金额',
  `platform` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '支付平台',
  `pay_status` tinyint(1) DEFAULT NULL COMMENT '支付状态 0：未支付 1：已支付 2：已取消',
  `status` tinyint(1) DEFAULT NULL COMMENT '状态 0：正常 1：删除',
  `pay_time` datetime DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `order`
--

INSERT INTO `order` (`id`, `user_id`, `app_id`, `amount`, `platform`, `pay_status`, `status`, `pay_time`, `create_time`, `update_time`) VALUES
(1, 1, 1, '1.00', 'wxpay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(2, 1, 1, '1.00', 'wxpay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(3, 1, 1, '1.00', 'wxpay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(4, 1, 2, '1.00', 'wxpay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(5, 1, 2, '1.00', 'wxpay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(6, 1, 2, '1.00', 'wxpay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(7, 1, 2, '1.00', 'wxpay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(8, 1, 2, '1.00', 'wxpay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(9, 1, 2, '1.00', 'wxpay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(10, 1, 2, '1.00', 'wxpay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(11, 1, 3, '1.00', 'wxpay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(12, 1, 4, '1.00', 'wxpay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(13, 1, 5, '1.00', 'wxpay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(14, 1, 3, '1.00', 'wxpay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(15, 1, 2, '1.00', 'wxpay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(16, 1, 3, '1.00', 'wxpay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(17, 1, 1, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(18, 1, 2, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(19, 1, 3, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(20, 1, 4, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(21, 1, 4, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(22, 1, 4, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(23, 1, 4, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(24, 1, 5, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(25, 1, 5, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(26, 1, 5, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(27, 1, 5, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(28, 1, 5, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(29, 1, 2, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(30, 1, 2, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(31, 1, 2, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(32, 1, 1, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(33, 1, 1, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(34, 1, 1, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(35, 1, 1, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(36, 1, 1, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(37, 1, 2, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(38, 1, 3, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(39, 1, 4, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(40, 1, 4, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(41, 1, 5, '1.00', 'alipay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(42, 1, 5, '1.00', 'wxpay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(43, 1, 5, '1.00', 'wxpay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51'),
(44, 1, 3, '1.00', 'wxpay', 1, 1, '2025-02-12 17:03:46', '2025-02-12 17:03:48', '2025-02-12 17:03:51');

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `package_id` varchar(255) NOT NULL COMMENT '包ID，对应APP表的ID',
  `app_id` varchar(255) NOT NULL COMMENT 'APP ID',
  `client_id` varchar(255) NOT NULL COMMENT '用户设备ID',
  `mobile` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '手机号',
  `mobile_brand` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '手机品牌',
  `mobile_model` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '手机型号',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态 0：注册 1：删除',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `user`
--

INSERT INTO `user` (`id`, `package_id`, `app_id`, `client_id`, `mobile`, `mobile_brand`, `mobile_model`, `status`, `create_time`, `update_time`) VALUES
(1, 'AAA', '1', '888888', '18888888888', 'iPhone', 'iPhone16 Pro', 0, '2025-04-12 22:24:43', '2025-04-12 22:24:43');

--
-- 转储表的索引
--

--
-- 表的索引 `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `username_indx` (`username`) USING BTREE;

--
-- 表的索引 `agreement`
--
ALTER TABLE `agreement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package` (`app_id`);

--
-- 表的索引 `app`
--
ALTER TABLE `app`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `user_id_idx` (`user_id`) USING BTREE,
  ADD KEY `name_idx` (`name`) USING BTREE;

--
-- 表的索引 `app_channel`
--
ALTER TABLE `app_channel`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `app_id_idx` (`app_id`) USING BTREE;

--
-- 表的索引 `app_vip`
--
ALTER TABLE `app_vip`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `app_id_idx` (`app_id`) USING BTREE;

--
-- 表的索引 `info_config`
--
ALTER TABLE `info_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key_ix` (`key`),
  ADD KEY `user_id_idx` (`user_id`);

--
-- 表的索引 `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `user_id_idx` (`user_id`) USING BTREE,
  ADD KEY `app_id_idx` (`app_id`) USING BTREE;

--
-- 表的索引 `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `mobile_idx` (`mobile`) USING BTREE,
  ADD KEY `app_id_idx` (`app_id`) USING BTREE,
  ADD KEY `package` (`package_id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `agreement`
--
ALTER TABLE `agreement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `app`
--
ALTER TABLE `app`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `app_channel`
--
ALTER TABLE `app_channel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 使用表AUTO_INCREMENT `app_vip`
--
ALTER TABLE `app_vip`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- 使用表AUTO_INCREMENT `info_config`
--
ALTER TABLE `info_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `order`
--
ALTER TABLE `order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- 使用表AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
