<?php
declare (strict_types=1);

namespace app\admin\service;

use app\common\model\Agreement;
use  \think\facade\Validate;

class AgreementService
{
    public static function add($data)
    {
        $validate = Validate::rule([
            'package'           => 'require',
            'user_agreement'    => 'require',
            'privacy_agreement' => 'require',
        ])->message([
            'package.require'           => '包名不能为空',
            'user_agreement.require'    => '用户协议不能为空',
            'privacy_agreement.require' => '隐私协议不能为空',
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }
        $model = Agreement::where(['package' => $data['package']])->find();
        if (empty($model)) {
            $app = new Agreement();
        }
        if ($app->save($data)) {
            return json(['code' => 0, 'msg' => '添加应用成功']);
        } else {
            return json(['code' => 1, 'msg' => '添加应用失败']);
        }
    }
}
