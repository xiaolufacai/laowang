# 订单接口服务
这个是用于订单OrderService业务
## 订单服务名称
    OrderService
## 订单命名空间
    app\index\service
## 服务类型
### 用户下单服务(setOrder)
    这个需要用户ID，用户会员类型
### 用户订单列表服务(orders)
    这个需要支持分页，状态时间查询
### 用户订单详情服务(detail)
    根据订单ID返回订单的具体数据
### 用户取消订单服务(cancel)
    用户ID，订单ID进行订单取消