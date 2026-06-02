# 订单接口控制器
## 控制器名称
    Order
## 控制器命名空间
    namespace app\index\controller;
## 控制器接口
### 用户下单接口(setOrder)
    这个需要用户ID，用户会员类型
### 用户订单列表(orders)
    这个需要支持分页，状态时间查询
### 用户订单详情(detail)
    根据订单ID返回订单的具体数据
### 用户取消订单(cancel)
    用户ID，订单ID进行订单取消