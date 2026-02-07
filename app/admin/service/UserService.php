<?php
declare (strict_types=1);

namespace app\admin\service;


use app\common\model\User;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use think\model\contract\Modelable;

class UserService {

    /**
     * 查询用户列表
     *
     * @param int|null $appId
     * @param int|null $id
     * @param int      $page     当前页数
     * @param int      $pageSize 每页条数
     *
     * @return array 查询结果，包含分页数据和总数
     * @throws DbException
     */
    public static function users($appId, $id, $page = 1, $pageSize = 10): array {
        // 基本查询构建
        $query = Db::name('user');

        // 根据传入的 app_id 和 id 添加查询条件
        if ($appId) {
            $query->where('app_id', $appId);
        }

        if ($id) {
            $query->where('id', $id);
        }

        // 查询分页数据
        $users = $query->paginate((int)$pageSize, false, ['page' => $page]);

        // 获取总数
        $total = $users->total();

        // 返回分页数据和总数
        return [
            'data'         => $users->items(),
            'total'        => $total,
            'current_page' => $users->currentPage(),
            'last_page'    => $users->lastPage(),
        ];
    }

    /**
     *  设置用户会员
     *
     * @param $userId
     * @param $vipType
     * @param $vipTime
     * @return array
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public static function setUserVip($userId, $vipType, $vipTime): array {
        $user = User::find($userId);
        if (empty($user)) {
            return ['error' => 1, 'message' => '用户不存在', 'data' => []];
        }
        $user['vip_type']        = $vipType;
        $user['vip_time']        = $vipTime;
        $user['vup_create_time'] = date('Y-m-d H:i:s');
        if ($user->save()) {
            return ['error' => 0, 'message' => '会员设置成功', 'data' => []];
        }
        return ['error' => 1, 'message' => '会员设置失败', 'data' => []];
    }
}
