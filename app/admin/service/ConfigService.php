<?php
declare (strict_types=1);

namespace app\admin\service;

use app\common\model\AppVip;
use app\common\model\Configure;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use  \think\facade\Validate;

class ConfigService {
    public static function add($data) {
        $validate = Validate::rule([
            'key'     => 'require',
            'value'   => 'require',
            'channel' => 'require',
        ])->message([
            'key.require'     => '键名不能为空',
            'value.require'   => '键值不能为空',
            'channel.require' => '渠道不能为空',
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }
        $id = $data['id'] ?? 0;
        if ($id > 0) {
            $app = Configure::find($id);
            unset($data['id']);
        } else {
            $app = new Configure();
        }
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['user_id']     = (int)session('uid');
        if ($app->save($data)) {
            return json(['code' => 0, 'msg' => '添加应用成功']);
        } else {
            return json(['code' => 1, 'msg' => '添加应用失败']);
        }
    }

    /**
     * 查询订单列表
     *
     * @param string $channel  渠道
     * @param int    $page     当前页数
     * @param int    $pageSize 每页条数
     *
     * @return array 查询结果，包含分页数据和总数
     * @throws DbException
     */
    public static function configs($channel, $page = 1, $pageSize = 10): array {
        // 基本查询构建
        $query = Db::name(Configure::TABLE_NAME);

        // 根据传入的 app_id 和 id 添加查询条件
        if ($channel) {
            $query->where('channel', $channel);
        }

        // 查询分页数据
        $orders = $query->paginate((int)$pageSize, false, ['page' => $page]);

        // 获取总数
        $total = $orders->total();

        // 返回分页数据和总数
        return [
            'data'         => $orders->items(),
            'total'        => $total,
            'current_page' => $orders->currentPage(),
            'last_page'    => $orders->lastPage(),
        ];
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
    public static function delete($id): array {
        $model = Configure::find($id);
        if ($model) {
            if ($model->delete()) {
                return ['error' => 0, 'message' => '删除成功'];
            } else {
                return ['error' => 1, 'message' => '删除失败'];
            }
        } else {
            return ['error' => 1, 'message' => '未找到该记录'];
        }
    }
}
