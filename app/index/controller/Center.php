<?php
declare (strict_types=1);

namespace app\index\controller;


use app\index\service\VipService;
use app\Request;
use think\response\Json;

class Center {

    /**
     *  会员中心
     *
     * @return Json
     */
    public function index() {
        try {
            $vips = VipService::vips();
            $data = [];
            foreach ($vips as $vip) {
                $data['goodsInfo'][] = [
                    'id'            => $vip['id'],
                    'name'          => $vip['name'],
                    'angleText'     => $vip['corner_text'],
                    'originalPrice' => $vip['old_price'],
                    'realPrice'     => $vip['new_price'],
                    'reducedPrice'  => $vip['old_price'] - $vip['new_price'],
                ];
            }
            return \json(['code' => 1, 'message' => 'OK', 'data' => ['agreement' => $data]]);
        } catch (\Exception $exception) {
            return json(['code' => -1, 'data' => [], 'message' => $exception->getMessage()]);
        }
    }
}
