<?php
declare (strict_types=1);

namespace app\admin\service;

use app\common\model\App;
use app\common\model\AppChannel;
use app\common\model\AppVip;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Config;
use  \think\facade\Validate;

class AppVipService
{

    /**
     * 添加APP 会员
     *
     * @param $data
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function add($data)
    {
        // 数据验证
        $validate = Validate::rule([
            'app_id'      => 'require',
            'old_price'   => 'require',
            'new_price'   => 'require',
            'corner_text' => 'require',
            'vip'         => 'require',
        ])->message([
            'app_id.require'      => 'APP ID有误',
            'old_price.require'   => '原价不能为空',
            'new_price.require'   => '现价不能为空',
            'corner_text.require' => '角标文案不能为空',
            'vip.require'         => '会员类型不能为空',
        ]);

        // 验证数据
        if (!$validate->check($data)) {
            return ['error' => 1, 'message' => $validate->getError()];
        }

        // 检查是否存在相同的 app_id 和 vip_id
        $existingRecord = AppVip::where('app_id', $data['app_id'])
            ->where('vip', $data['vip'])
            ->find();

        if ($existingRecord) {
            return ['error' => 1, 'message' => '该APP ID和VIP类型已存在，不能新增'];
        }

        // 如果存在 id，执行更新；否则执行新增
        if (isset($data['id']) && $data['id']) {
            // 更新操作
            $app = AppVip::find($data['id']);
            if ($app) {
                $app->app_id      = $data['app_id'];
                $app->old_price   = $data['old_price'];
                $app->new_price   = $data['new_price'];
                $app->corner_text = $data['corner_text'];
                $app->update_time = date('Y-m-d H:i:s');
                $app->vip         = $data['vip'];
                if ($app->save()) {
                    return ['error' => 0, 'message' => '更新成功'];
                } else {
                    return ['error' => 1, 'message' => '更新失败'];
                }
            } else {
                return ['error' => 1, 'message' => '未找到该记录进行更新'];
            }
        } else {
            // 新增操作
            $app = new AppVip();
            $data['create_time'] = date('Y-m-d H:i:s');
            if ($app->save($data)) {
                return ['error' => 0, 'message' => '添加成功'];
            } else {
                return ['error' => 1, 'message' => '添加失败'];
            }
        }
    }

    /**
     * 获取APP会员
     *
     * @param $appId
     * @return array
     */
    public static function appVips($appId): array
    {
        $rows = AppVip::where(['app_id' => $appId])->select()->toArray();
        $vips = Config::get('app.vips');
        foreach ($rows as &$vip) {
            $vip['vip_txt'] = $vips[$vip['vip']];
        }
        return $rows;
    }

    /**
     * 删除
     *
     * @param $id
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function delete($id): array
    {
        $vip = AppVip::find($id);
        if ($vip) {
            if ($vip->delete()) {
                return ['error' => 0,'message' => '删除成功'];
            } else {
                return ['error' => 1,'message' => '删除失败'];
            }
        } else {
            return ['error' => 1,'message' => '未找到该记录'];
        }
    }
}
