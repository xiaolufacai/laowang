<?php

namespace app\index\service;

use app\common\model\Agreement;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;

class AgreementService {


    /**
     *  根据APPID查询协议
     *
     * @param $appId
     * @return Agreement|array|Model|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getAgreement($appId) {
        return Agreement::where('app_id', $appId)->find();
    }
}