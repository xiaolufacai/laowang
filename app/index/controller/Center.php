<?php
declare (strict_types=1);

namespace app\index\controller;


use app\index\service\VipService;
use app\IndexBaseController;
use app\Request;
use think\response\Json;

class Center extends IndexBaseController {

    /**
     *  会员中心
     *
     * @return Json
     */
    public function index() {
        try {
            $vips = VipService::appVips($this->id);
            $data = [];
            // 读取会员类型
            $vipNames = config('app.vips');
            foreach ($vips as $vip) {
                $data['goodsInfo'][] = [
                    'id'            => $vip['id'],
                    'name'          => $vipNames[$vip['name']] ?? '',
                    'angleText'     => $vip['corner_text'],
                    'originalPrice' => $vip['old_price'],
                    'realPrice'     => $vip['new_price'],
                    'reducedPrice'  => $vip['old_price'] - $vip['new_price'],
                ];
            }
            return \json(['code' => 1, 'message' => 'OK', 'data' => $data]);
        } catch (\Exception $exception) {
            return json(['code' => -1, 'data' => [], 'message' => $exception->getMessage()]);
        }
    }
}
