# 订单接口文档

## 基础信息

- 基础路径：`/index.php/` 或根据项目配置
- 请求方式：POST/GET（根据接口说明）
- 返回格式：JSON

---

## 接口列表

### 1. 创建订单

**接口地址：** `POST /order/setOrder`

**请求参数：**

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| vip_id | int | 是 | 会员类型ID（app_vip表的ID） |

**请求示例：**
```
POST /order/setOrder
Content-Type: application/x-www-form-urlencoded

vip_id=1
```

**返回示例：**
```json
{
    "code": 0,
    "message": "订单创建成功",
    "data": {
        "order_id": 1001,
        "order_no": "2026033112345678901",
        "amount": 99.00,
        "original_amount": 199.00,
        "vip_type": 1
    }
}
```

**测试参数：**

| vip_id | 说明 |
|--------|------|
| 1 | 月度会员 |
| 2 | 季度会员 |
| 3 | 年度会员 |

---

### 2. 订单列表

**接口地址：** `GET /order/orders`

**请求参数：**

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | int | 否 | 页码，默认1 |
| page_size | int | 否 | 每页条数，默认10 |
| status | int | 否 | 支付状态，留空查全部 |

**支付状态说明：**

| status | 说明 |
|--------|------|
| 0 | 待支付 |
| 1 | 已支付 |
| 2 | 已取消 |
| 3 | 已完成 |

**请求示例：**
```
GET /order/orders?page=1&page_size=10&status=0
```

**返回示例：**
```json
{
    "code": 0,
    "data": {
        "data": [
            {
                "id": 1001,
                "order_no": "2026033112345678901",
                "amount": 99.00,
                "original_amount": 199.00,
                "pay_status": 0,
                "pay_status_text": "待支付",
                "pay_type": null,
                "pay_type_text": "未知类型",
                "create_time": "2026-03-31 12:34:56",
                "pay_time": null
            }
        ],
        "total": 1,
        "current_page": 1,
        "last_page": 1
    }
}
```

**测试参数：**

| page | page_size | status | 说明 |
|------|-----------|--------|------|
| 1 | 10 | (空) | 查询全部订单 |
| 1 | 10 | 0 | 查询待支付订单 |
| 1 | 10 | 1 | 查询已支付订单 |
| 1 | 10 | 2 | 查询已取消订单 |
| 1 | 10 | 3 | 查询已完成订单 |

---

### 3. 订单详情

**接口地址：** `GET /order/detail`

**请求参数：**

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| order_id | int | 是 | 订单ID |

**请求示例：**
```
GET /order/detail?order_id=1001
```

**返回示例：**
```json
{
    "code": 0,
    "data": {
        "id": 1001,
        "order_no": "2026033112345678901",
        "amount": 99.00,
        "original_amount": 199.00,
        "pay_status": 0,
        "pay_status_text": "待支付",
        "pay_type": null,
        "pay_type_text": "未知类型",
        "create_time": "2026-03-31 12:34:56",
        "pay_time": null,
        "vip_info": {
            "vip": 1,
            "old_price": 199.00,
            "new_price": 99.00
        }
    }
}
```

**测试参数：**

| order_id | 说明 |
|----------|------|
| 1 | 订单ID=1 |
| 2 | 订单ID=2 |
| 3 | 订单ID=3 |

---

### 4. 取消订单

**接口地址：** `POST /order/cancel`

**请求参数：**

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| order_id | int | 是 | 订单ID |

**请求示例：**
```
POST /order/cancel
Content-Type: application/x-www-form-urlencoded

order_id=1001
```

**返回示例：**
```json
{
    "code": 0,
    "message": "订单取消成功"
}
```

**测试参数：**

| order_id | 说明 |
|----------|------|
| 1 | 取消订单ID=1 |
| 2 | 取消订单ID=2 |

**注意：** 只能取消待支付状态的订单

---

## 错误码说明

| code | 说明 |
|------|------|
| 0 | 成功 |
| -1 | 失败（具体原因见message字段） |

---

## 支付类型说明

| pay_type | 说明 |
|----------|------|
| 1 | 微信支付 |
| 2 | 支付宝支付 |

---

## 命令行测试

可使用以下命令在终端进行接口测试（参数会交互式输入）：

```bash
# 创建订单
php8 think order create

# 订单列表
php8 think order list

# 订单详情
php8 think order detail

# 取消订单
php8 think order cancel
```