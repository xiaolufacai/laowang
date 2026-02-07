<?php
declare (strict_types=1);

namespace app\admin\service;


use think\db\exception\DbException;
use think\facade\Db;

class OrderService {


    /**
     * 查询订单列表
     *
     * @param int|null $appId
     * @param int|null $id
     * @param int      $page     当前页数
     * @param int      $pageSize 每页条数
     *
     * @return array 查询结果，包含分页数据和总数
     * @throws DbException
     */
    public static function orders($appId, $id, $page = 1, $pageSize = 10): array {
        // 基本查询构建
        $query = Db::name('order');

        // 根据传入的 app_id 和 id 添加查询条件
        if ($appId) {
            $query->where('app_id', $appId);
        }

        if ($id) {
            $query->where('id', $id);
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
}
