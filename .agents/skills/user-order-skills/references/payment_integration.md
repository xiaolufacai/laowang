# ThinkPHP8 聚合支付（微信 / 支付宝）Skill（基于 yansongda/pay）

## 目标

为 **ThinkPHP8** 项目封装一套可落地的聚合支付能力，支持：

- 微信支付
- 支付宝支付
- 统一下单入口
- 异步回调处理
- 订单查询
- 退款能力
- 幂等处理
- 统一响应格式
- 统一的日志记录包括回调日志记录

适用场景：

- 会员充值
- 订单支付
- SaaS 服务购买
- 应用付费开通

---

## 一、推荐方案

第三方支付库建议使用：

- **yansongda/pay**：统一接微信与支付宝，最适合聚合支付场景

项目层面不建议控制器里直接写微信、支付宝逻辑，而是统一走：

- `PayService`：统一支付入口
- `WechatPayService`：微信支付适配层
- `AlipayService`：支付宝支付适配层
- `NotifyService`：异步回调处理

这样业务层只关心：

- 订单是否能支付
- 支付渠道是什么
- 支付结果是否成功

---

## 二、安装依赖

```bash
composer require yansongda/pay
```

---

## 三、推荐目录结构

```text
app/index/
        ├─ controller/
        │  └─ PayController.php
        ├─ service/
        │  └─ pay/
        │     ├─ PayService.php
        │     ├─ WechatPayService.php
        │     ├─ AlipayService.php
        │     └─ NotifyService.php
        ├─ validate/
        │  └─ PayValidate.php
        config/
        └─ pay.php
        route/
        └─ app.php
```

---

## 四、订单表设计示例

建议支付一定围绕订单表，不要前端直接传金额创建支付。

```sql
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_no` varchar(64) NOT NULL COMMENT '订单号',
  `user_id` bigint NOT NULL DEFAULT 0 COMMENT '用户ID',
  `app_id` int(11) NOT NULL DEFAULT '0' COMMENT '包ID',
  `vip_id` int(11) NOT NULL DEFAULT '0' COMMENT '会员类型ID',
  `subject` varchar(255) NOT NULL DEFAULT '' COMMENT '订单标题',
  `body` varchar(500) DEFAULT '' COMMENT '订单描述',
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '支付金额',
  `original_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '原始订单金额',
  `channel` varchar(20) NOT NULL DEFAULT '' COMMENT '支付渠道 wechat/alipay',
  `pay_type` varchar(20) NOT NULL DEFAULT '' COMMENT '支付类型 jsapi/app/h5/native/page',
  `business_type` varchar(50) NOT NULL DEFAULT '' COMMENT '业务类型 1：会员开通',
  `business_id` bigint NOT NULL DEFAULT 0 COMMENT '业务ID【当business_type：1时对应app_vip表的ID】',
  `pay_status` tinyint NOT NULL DEFAULT 0 COMMENT '0待支付 1已支付 2已关闭 3已退款',
  `transaction_id` varchar(128) DEFAULT '' COMMENT '三方交易号',
  `paid_at` datetime DEFAULT NULL COMMENT '支付时间',
  `notify_data` longtext COMMENT '回调原始数据',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '0正常 1删除',
  `create_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_no` (`order_no`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_business_type_business_id` (`business_type`, `business_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 五、配置文件

新建：`config/pay.php`

```php
<?php

return [
    'notify' => [
        'wechat' => env('pay.wechat.notify_url', ''),
        'alipay' => env('pay.alipay.notify_url', ''),
    ],

    'wechat' => [
        'mch_id'                 => env('pay.wechat.mch_id', ''),
        'mini_app_id'            => env('pay.wechat.mini_app_id', ''),
        'mp_app_id'              => env('pay.wechat.mp_app_id', ''),
        'app_id'                 => env('pay.wechat.app_id', ''),
        'mch_secret_key'         => env('pay.wechat.mch_secret_key', ''),
        'mch_secret_cert'        => env('pay.wechat.mch_secret_cert', ''),
        'mch_public_cert_path'   => env('pay.wechat.mch_public_cert_path', ''),
        'notify_url'             => env('pay.wechat.notify_url', ''),
    ],

    'alipay' => [
        'app_id'                  => env('pay.alipay.app_id', ''),
        'app_secret_cert'         => env('pay.alipay.app_secret_cert', ''),
        'alipay_public_cert_path' => env('pay.alipay.alipay_public_cert_path', ''),
        'alipay_root_cert_path'   => env('pay.alipay.alipay_root_cert_path', ''),
        'notify_url'              => env('pay.alipay.notify_url', ''),
        'return_url'              => env('pay.alipay.return_url', ''),
        'sandbox'                 => false,
    ],
];
```

---

## 六、`.env` 示例

```env
[PAY]
pay.wechat.mch_id=你的微信商户号
pay.wechat.mini_app_id=
pay.wechat.mp_app_id=
pay.wechat.app_id=你的微信APPID
pay.wechat.mch_secret_key=你的APIv3密钥
pay.wechat.mch_secret_cert=/www/wwwroot/project/cert/wechat/apiclient_key.pem
pay.wechat.mch_public_cert_path=/www/wwwroot/project/cert/wechat/apiclient_cert.pem
pay.wechat.notify_url=https://你的域名/pay/notify/wechat

pay.alipay.app_id=你的支付宝APPID
pay.alipay.app_secret_cert=/www/wwwroot/project/cert/alipay/appCertPublicKey.crt
pay.alipay.alipay_public_cert_path=/www/wwwroot/project/cert/alipay/alipayCertPublicKey_RSA2.crt
pay.alipay.alipay_root_cert_path=/www/wwwroot/project/cert/alipay/alipayRootCert.crt
pay.alipay.notify_url=https://你的域名/pay/notify/alipay
pay.alipay.return_url=https://你的域名/pay/return/alipay
```

> 密钥、证书路径建议走 `.env`，不要直接写死在代码里。

---

## 七、模型示例

`app/model/Order.php`

```php
<?php

namespace app\model;

use think\Model;

class Order extends Model
{
    protected $name = 'order';

    protected $autoWriteTimestamp = false;
}
```

---

## 八、参数校验

`app/validate/PayValidate.php`

```php
<?php

namespace app\validate;

use think\Validate;

class PayValidate extends Validate
{
    protected $rule = [
        'order_no' => 'require|max:64',
        'channel'  => 'require|in:wechat,alipay',
        'pay_type' => 'require|in:jsapi,app,h5,native,page',
    ];

    protected $message = [
        'order_no.require' => '订单号不能为空',
        'channel.require'  => '支付渠道不能为空',
        'channel.in'       => '支付渠道错误',
        'pay_type.require' => '支付方式不能为空',
        'pay_type.in'      => '支付方式错误',
    ];
}
```

---

## 九、统一响应格式建议

建议接口统一返回：

成功：

```json
{
  "result": true,
  "data": {},
  "errmsg": ""
}
```

失败：

```json
{
  "result": false,
  "errmsg": "订单不存在"
}
```

如果你项目已有固定异常格式，也可以统一成你现有的：

```json
{
  "code": 501,
  "msg": "sign的值错误"
}
```

---

## 十、统一支付服务

`app/service/pay/PayService.php`

```php
<?php

namespace app\index\service;

use app\common\model\Order;
use think\exception\ValidateException;

class PayService {
    public function create(array $data): array {
        validate(\app\validate\PayValidate::class)->check($data);

        $order = Order::where('order_no', $data['order_no'])
            ->where('status', 0)
            ->find();

        if (!$order) {
            throw new ValidateException('订单不存在');
        }

        if ((int)$order->pay_status === 1) {
            throw new ValidateException('订单已支付');
        }

        if (bccomp((string)$order->amount, '0', 2) <= 0) {
            throw new ValidateException('订单金额异常');
        }

        if ($data['channel'] === 'wechat') {
            return app(WechatPayService::class)->create($order, $data);
        }

        if ($data['channel'] === 'alipay') {
            return app(AlipayService::class)->create($order, $data);
        }

        throw new ValidateException('不支持的支付渠道');
    }
}
```

---

## 十一、微信支付服务

`app/service/pay/WechatPayService.php`

```php
<?php

namespace app\service\pay;

use app\model\Order;
use think\facade\Config;
use Yansongda\Pay\Pay;

class WechatPayService {
    public function create(Order $order, array $data): array {
        $config = Config::get('pay.wechat');
        $payType = $data['pay_type'];

        $payload = [
            'out_trade_no' => $order->order_no,
            'description'  => $order->subject,
            'amount'       => (int)bcmul((string)$order->amount, '100'),
            'notify_url'   => $config['notify_url'],
        ];

        $pay = Pay::wechat($config);

        switch ($payType) {
            case 'native':
                $result = $pay->scan($payload);
                return [
                    'channel'    => 'wechat',
                    'pay_type'   => 'native',
                    'pay_params' => $result,
                ];

            case 'h5':
                $result = $pay->h5($payload);
                return [
                    'channel'    => 'wechat',
                    'pay_type'   => 'h5',
                    'pay_params' => $result,
                ];

            case 'app':
                $result = $pay->app($payload);
                return [
                    'channel'    => 'wechat',
                    'pay_type'   => 'app',
                    'pay_params' => $result,
                ];

            case 'jsapi':
                $payload['openid'] = $data['openid'] ?? '';
                $result = $pay->mini($payload);
                return [
                    'channel'    => 'wechat',
                    'pay_type'   => 'jsapi',
                    'pay_params' => $result,
                ];

            default:
                throw new \RuntimeException('暂不支持的微信支付类型');
        }
    }
}
```

> 如果你是公众号支付，通常是 `mp`；如果你是小程序支付，通常是 `mini`。你可以根据自己项目再拆得更细。

---

## 十二、支付宝支付服务

`app/service/pay/AlipayService.php`

```php
<?php

namespace app\index\service;

use app\common\model\Order;
use think\facade\Config;
use Yansongda\Pay\Pay;

class AlipayService {
    public function create(Order $order, array $data): array {
        $config = Config::get('pay.alipay');
        $payType = $data['pay_type'];

        $payload = [
            'out_trade_no' => $order->order_no,
            'total_amount' => (string)$order->amount,
            'subject'      => $order->subject,
            'body'         => $order->body ?: $order->subject,
            'notify_url'   => $config['notify_url'],
            'return_url'   => $config['return_url'],
        ];

        $pay = Pay::alipay($config);

        switch ($payType) {
            case 'page':
                $result = $pay->page($payload);
                return [
                    'channel'    => 'alipay',
                    'pay_type'   => 'page',
                    'pay_params' => $result,
                ];

            case 'h5':
                $result = $pay->wap($payload);
                return [
                    'channel'    => 'alipay',
                    'pay_type'   => 'h5',
                    'pay_params' => $result,
                ];

            case 'app':
                $result = $pay->app($payload);
                return [
                    'channel'    => 'alipay',
                    'pay_type'   => 'app',
                    'pay_params' => $result,
                ];

            default:
                throw new \RuntimeException('暂不支持的支付宝支付类型');
        }
    }
}
```

---

## 十三、控制器示例

`app/controller/PayController.php`

```php
<?php

namespace app\controller;

use app\service\pay\NotifyService;
use app\service\pay\PayService;
use think\facade\Request;

class PayController {
    public function create() {
        try {
            $data = Request::post();
            $result = app(PayService::class)->create($data);

            return json([
                'result' => true,
                'data'   => $result,
                'errmsg' => '',
            ]);
        } catch (\Throwable $e) {
            return json([
                'result' => false,
                'errmsg' => $e->getMessage(),
            ]);
        }
    }

    public function notifyWechat() {
        return app(NotifyService::class)->wechat();
    }

    public function notifyAlipay() {
        return app(NotifyService::class)->alipay();
    }
}
```

---

## 十四、异步回调处理

`app/service/pay/NotifyService.php`

```php
<?php

namespace app\index\service;

use app\model\Order;
use think\facade\Config;
use think\facade\Db;
use Yansongda\Pay\Pay;

class NotifyService {
    public function wechat(){
        $config = Config::get('pay.wechat');
        $pay = Pay::wechat($config);

        $response = $pay->callback();
        $data = $response->toArray();

        $orderNo = $data['out_trade_no'] ?? '';
        $transactionId = $data['transaction_id'] ?? '';

        Db::transaction(function () use ($orderNo, $transactionId, $data) {
            $order = Order::where('order_no', $orderNo)->lock(true)->find();
            if (!$order) {
                throw new \RuntimeException('订单不存在');
            }

            if ((int)$order->pay_status === 1) {
                return;
            }

            $order->pay_status = 1;
            $order->channel = 'wechat';
            $order->transaction_id = $transactionId;
            $order->paid_at = date('Y-m-d H:i:s');
            $order->notify_data = json_encode($data, JSON_UNESCAPED_UNICODE);
            $order->save();

            // 这个地方要根据订单的类型，判断是开通那种会员，给用户开通响应的会员时间
            调用方法
            VipService::open($order->business_id);
        });

        return $pay->success();
    }

    public function alipay(){
        $config = Config::get('pay.alipay');
        $pay = Pay::alipay($config);

        $response = $pay->callback();
        $data = $response->toArray();

        $orderNo = $data['out_trade_no'] ?? '';
        $transactionId = $data['trade_no'] ?? '';
        $tradeStatus = $data['trade_status'] ?? '';

        if (!in_array($tradeStatus, ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
            return 'success';
        }

        Db::transaction(function () use ($orderNo, $transactionId, $data) {
            $order = Order::where('order_no', $orderNo)->lock(true)->find();
            if (!$order) {
                throw new \RuntimeException('订单不存在');
            }

            if ((int)$order->pay_status === 1) {
                return;
            }

            $order->pay_status = 1;
            $order->channel = 'alipay';
            $order->transaction_id = $transactionId;
            $order->paid_at = date('Y-m-d H:i:s');
            $order->notify_data = json_encode($data, JSON_UNESCAPED_UNICODE);
            $order->save();

            // TODO: 这里写你的业务处理，例如开会员、发货、加余额
        });

        return 'success';
    }
}
```

---

## 十五、幂等处理关键点

异步回调必须做幂等，否则会出现：

- 重复开通会员
- 重复发放余额
- 重复创建权益

推荐做法：

1. 回调处理时对订单加锁
2. 先判断 `pay_status`
3. 已支付直接返回成功
4. 业务发放与订单更新放在同一事务中

示例核心逻辑：

```php
if ((int)$order->pay_status === 1) {
    return;
}
```

---

## 十六、路由示例

`route/app.php`

```php
use think\facade\Route;

Route::post('pay/create', 'PayController/create');
Route::post('pay/notify/wechat', 'PayController/notifyWechat');
Route::post('pay/notify/alipay', 'PayController/notifyAlipay');
```

---

## 十七、前端调用示例

```javascript
axios.post('/pay/create', {
  order_no: '202603300001',
  channel: 'wechat',
  pay_type: 'native'
}).then(res => {
  if (res.data.result) {
    console.log(res.data.data.pay_params)
  } else {
    alert(res.data.errmsg)
  }
})
```

### 常见支付类型

#### 微信支付

- `native`：PC 扫码支付
- `h5`：手机浏览器支付
- `app`：APP 支付
- `jsapi`：公众号 / 小程序支付

#### 支付宝支付

- `page`：PC 网页支付
- `h5`：手机网页支付
- `app`：APP 支付

---

## 十八、退款示例

### 微信退款

```php
$pay = \Yansongda\Pay\Pay::wechat(config('pay.wechat'));
$result = $pay->refund([
    'out_trade_no'  => $order->order_no,
    'out_refund_no' => 'refund_' . $order->order_no,
    'amount' => [
        'refund'   => 100,
        'total'    => 100,
        'currency' => 'CNY',
    ],
]);
```

### 支付宝退款

```php
$pay = \Yansongda\Pay\Pay::alipay(config('pay.alipay'));
$result = $pay->refund([
    'out_trade_no'   => $order->order_no,
    'refund_amount'  => '1.00',
    'out_request_no' => 'refund_' . $order->order_no,
]);
```

建议单独建立退款表，记录：

- 退款单号
- 退款金额
- 退款状态
- 退款响应
- 退款时间

---

## 十九、订单查询示例

### 微信订单查询

```php
$pay = \Yansongda\Pay\Pay::wechat(config('pay.wechat'));
$result = $pay->query(['out_trade_no' => $orderNo]);
```

### 支付宝订单查询

```php
$pay = \Yansongda\Pay\Pay::alipay(config('pay.alipay'));
$result = $pay->query(['out_trade_no' => $orderNo]);
```

适用于：

- 主动补单
- 定时任务补偿
- 回调丢失后的支付状态确认

---

## 二十、推荐补单机制

线上支付建议做“回调 + 主动查询”双保险：

1. 用户支付后依赖异步回调更新状态
2. 如果回调异常或网络抖动，则通过定时任务查询支付状态
3. 查询到成功后执行补单逻辑

例如 ThinkPHP8 命令行任务中：

- 查询待支付超过 1 分钟未更新的订单
- 向微信 / 支付宝主动查询
- 成功后补写订单状态

---

## 二十一、安全建议

### 1. 金额不能信前端

支付金额必须从订单表读取，不允许前端直接传最终金额作为支付依据。

### 2. 回调必须验签

不要自己随便解析回调，必须使用支付库的验签回调能力。

### 3. 订单号必须唯一

建议使用：

- 日期 + 用户ID + 随机数
- 雪花ID
- 业务前缀 + 时间戳 + 随机串

### 4. 业务逻辑必须事务处理

订单状态更新和业务发放要放在一个事务中。

### 5. 区分沙箱与正式环境

正式上线前先跑沙箱或测试环境，不要直接拿正式证书联调。

---

## 二十二、落地建议

如果你当前是后台管理系统 + 订单支付，建议第一阶段先支持：

- 微信 `native`
- 支付宝 `page`
- 微信回调
- 支付宝回调
- 订单查询

后面再逐步加：

- 微信 `h5`
- 支付宝 `h5`
- 退款
- 定时补单
- 微信 `app/jsapi`
- 支付宝 `app`

---

## 二十三、结论

在 ThinkPHP8 里做微信 + 支付宝聚合支付，最推荐的方案是：

- **第三方库：`yansongda/pay`**
- **项目层：自建统一支付服务层**
- **流程上：订单驱动 + 异步回调 + 幂等处理 + 主动补单**

这样代码清晰、扩展方便，也最适合后续继续加退款、查询、支付日志和更多业务类型。

