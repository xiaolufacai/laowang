---
name: user-order-skills
description: 根据用户会员等级创建订单系统，自动生成订单模型、订单控制器接口、订单服务。接入支付系统，支持微信支付、支付宝支付。订单状态包括：待支付、已支付、已取消、已完成。自动生成订单列表页面和订单详情页面。
---

# User Order Skills - 订单系统生成器
该 Skill 用于 **根据用户会员等级创建订单系统**，自动生成订单模型、订单控制器接口、订单服务，并接入支付系统。
## 技术栈
- ThinkPHP8
- MySQL
- Redis
- 微信支付
- 支付宝支付
## 订单状态
- 待支付
- 已支付
- 已取消
- 已完成
## 生成原则
当用户提出订单系统需求时：
1. 自动生成订单数据库设计
2. 自动生成订单模型
3. 自动生成订单控制器接口
4. 自动生成订单服务
5. 自动接入微信支付和支付宝支付
6. 自动生成订单列表页面
7. 自动生成订单详情页面
禁止只生成部分代码。
## 项目结构
项目分为前端APP接口模块和后端管理两部分
### 前端APP接口模块
控制器：app/index/controllers/
服务：app/index/services/
模型：app/common/models/
### 后端管理模块
控制器：app/admin/controllers/
服务：app/admin/services/
模型：app/common/models/
页面：app/admin/views/orders/
## 订单模型设计
参考reference/order_model.md
## 接口订单控制器接口设计
参考reference/api_order_controller.md
## 后端订单控制器接口设计
参考reference/admin_order_controller.md
## 接口订单服务设计
参考reference/api_order_service.md
## 后端订单服务设计
参考reference/admin_order_service.md
## 支付系统接入设计
参考reference/payment_integration.md
## 用户会员等级设计
参考reference/user_membership.md
## 使用聚合设计模式，订单系统与用户系统解耦，订单系统通过接口调用用户系统获取会员等级信息。
## 订单系统支持多种支付方式，用户可以选择微信支付或支付宝支付完成订单支付。
## 订单系统自动生成操作日志，记录订单的创建、支付、取消、完成等操作，方便管理员查看订单操作历史。 