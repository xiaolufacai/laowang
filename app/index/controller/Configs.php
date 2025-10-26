<?php
declare (strict_types=1);

namespace app\index\controller;


use app\index\service\AgreementService;
use app\common\model\App;
use app\index\service\ChannelService;
use app\index\service\ConfigService;
use app\IndexBaseController;
use app\Request;
use think\response\Json;

class Configs extends IndexBaseController {

    /**
     *  配置接口
     *
     * @return Json
     */
    public function index() {
        try {
            // 根据包名查询协议数据
            $id = $this->appId;
            // 协议地址【根据包走】
            $agreementUrl                               = config('app.agreementUrl') . '/index/agreement/index?app_id=' . $id . '&type=';
            $data['optionsInfo']['privacyPolicy']       = $agreementUrl . 'privacy_agreement';
            $data['optionsInfo']['userAgreement']       = $agreementUrl . 'user_agreement';
            $data['optionsInfo']['sdkInfoList']         = $agreementUrl . 'sdk_list';
            $data['optionsInfo']['userCollectInfoList'] = $agreementUrl . 'user_collect';
            $data['optionsInfo']['vipAgreement']        = $agreementUrl . 'vip_agreement';

            // 获取渠道信息【渠道+包】
            $channelData                        = ChannelService::getChannelByApp($this->channel, $id);
            $data['optionsInfo']['versionName'] = $channelData['version_no'] ?? '';
            $data['optionsInfo']['versionCode'] = $channelData['version_name'] ?? '';
            $data['optionsInfo']['auditStatus'] = $channelData['status'] ?? 0;

            // 获取扩展信息【根据渠道】
            $extraInfo         = ConfigService::configs($this->channel);
            $data['extraInfo'] = [];
            foreach ($extraInfo as $k => $v) {
//                $data['extraInfo'][]['extraKey'] = $v['key'];
//                $data['extraInfo'][]['extraValue'] = $v['value'];
                $tmp['extraKey']     = $v['key'];
                $tmp['extraValue']   = $v['value'];
                $data['extraInfo'][] = $tmp;
            }
            return json(['code' => 200, 'data' => $data, 'message' => 'OK']);
        } catch (\Exception $e) {
            return json(['code' => -1, 'data' => [], 'message' => $e->getMessage()]);
        }
    }

    public function s() {

    }
}
