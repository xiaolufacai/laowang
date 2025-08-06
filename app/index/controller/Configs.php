<?php
declare (strict_types=1);

namespace app\index\controller;


use app\index\service\AgreementService;
use app\BaseController;
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
            $app = App::find($this->appId);
            if (empty($app)) {
                return json(['code' => -1, 'data' => [], 'message' => '包名不存在']);
            }
            $data      = [];
            $id        = $app['id'];
            $agreement = AgreementService::getAgreement($id);
            // 协议地址
            $agreementUrl                               = config('app.agreementUrl');
            $data['optionsInfo']['privacyPolicy']       = $agreementUrl . $agreement['privacy_agreement'];
            $data['optionsInfo']['userAgreement']       = $agreementUrl . $agreement['user_agreement'];
            $data['optionsInfo']['sdkInfoList']         = $agreementUrl . $agreement['sdk_list'];
            $data['optionsInfo']['userCollectInfoList'] = $agreementUrl . $agreement['user_collect'];
            $data['optionsInfo']['vipAgreement']        = $agreementUrl . $agreement['vip_agreement'];

            // 获取渠道信息
            $channelData                        = ChannelService::getChannelByApp($this->channel, $id);
            $data['optionsInfo']['versionName'] = $channelData['version_no'] ?? '';
            $data['optionsInfo']['versionCode'] = $channelData['version_name'] ?? '';
            $data['optionsInfo']['auditStatus'] = $channelData['status'] ?? 0;

            // 获取扩展信息
            $extraInfo = ConfigService::configs();
            foreach ($extraInfo as $k => $v) {
                $data['extraInfo'][$v['key']] = $v['value'];
            }
            return json(['code' => 200, 'data' => $data, 'message' => 'OK']);
        } catch (\Exception $e) {
            return json(['code' => -1, 'data' => [], 'message' => $e->getMessage()]);
        }
    }
}
