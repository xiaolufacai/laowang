# 用户会员等级设计
这个是用于用户会员开通取消，金额计算等VipService业务
## 订单服务名称
    VipService
## 订单命名空间
    app\index\service
## 服务类型
### 获取会员实际金额和原始金额(方法名称：amount)
    public static function amount($vipId) {}
    这个需要app_id和vip类型
### 根据会员类型，进行会员时间开通
    public static function open($vipId) {}
    需要传递id
### 根据会员类型，进行会员时间取消开通
    public static function close($vipId) {}
    需要传递id