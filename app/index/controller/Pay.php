<?php
declare (strict_types=1);

namespace app\index\controller;

use app\IndexBaseController;
use app\index\service\pay\PayService;
use app\index\service\pay\NotifyService;
use app\Request;
use think\response\Json;

/**
 * 支付控制器 - 前端APP接口
 */
class Pay extends IndexBaseController {

    /**
     * 创建支付
     *
     * @param Request $request
     * @return Json
     */
    public function create(Request $request): Json {
        $data = $request->post();

        // 添加用户ID到支付参数（用于验证订单归属）
        $data['user_id'] = $request->userId ?? null;

        try {
            $result = PayService::create($data);

            return json([
                'code'    => 0,
                'data'    => $result,
                'message' => '支付创建成功'
            ]);
        } catch (\Exception $e) {
            return json([
                'code'    => -1,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * 查询支付状态
     *
     * @param Request $request
     * @return Json
     */
    public function query(Request $request): Json {
        $orderNo = $request->get('order_no');
        $channel = $request->get('channel', 'wechat');

        if (empty($orderNo)) {
            return json(['code' => -1, 'message' => '订单号不能为空']);
        }

        $result = PayService::query($orderNo, $channel);

        if ($result['error'] === 0) {
            return json(['code' => 0, 'data' => $result['data']]);
        }

        return json(['code' => -1, 'message' => $result['message']]);
    }

    /**
     * 微信支付回调
     *
     * @return string
     */
    public function notifyWechat(): string {
        return NotifyService::wechat();
    }

    /**
     * 支付宝支付回调
     *
     * @return string
     */
    public function notifyAlipay(): string {
        return NotifyService::alipay();
    }
}