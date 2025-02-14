<?php
declare (strict_types=1);

namespace app\admin\service;

use app\common\model\App;
use app\common\model\AppChannel;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Config;
use  \think\facade\Validate;

class AppService
{
    public static function add($data)
    {
        $validate = Validate::rule([
            'name'        => 'require',
            'ad_id'       => 'require',
            'app_id'      => 'require',
            'package_url' => 'require',
            'repository'  => 'require',
            'wx_id'       => 'require',
            'ym_id'       => 'require',
        ])->message([
            'name.require'        => '包名不能为空',
            'ad_id.require'       => '广告ID不能为空',
            'app_id.require'      => 'APP ID不能为空',
            'package_url.require' => '打包平台地址不能为空',
            'repository.require'  => 'coding仓库地址不能为空',
            'ym_id.require'       => '友盟id不能为空',
            'wx_id.require'       => '微信id不能为空',
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 1, 'msg' => $validate->getError()]);
        }
        $app = new App();
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['user_id'] = (int)session('uid');
        if ($app->save($data)) {
            return json(['code' => 0, 'msg' => '添加应用成功']);
        } else {
            return json(['code' => 1, 'msg' => '添加应用失败']);
        }
    }

    /**
     * 获取APP 渠道信息
     *
     * @param $appId
     * @return array
     */
    public static function appChannels($appId, $channel = '')
    {
        $where = ['app_id' => $appId];
        if (!empty($channel)) {
            $where['channel'] = $channel;
        }
        $data = AppChannel::where($where)->select();
        if ($data) {
            $data = $data->toArray();
            $data = array_column($data, null, 'channel');
        }
        $channels = Config::get('app.channels');

        // 状态
        $statusArray = [
            0 => '审核中',
            1 => '线上模式',
        ];
        // 上架状态
        $listStatusArray = [
            0 => '未上架',
            1 => '已上架',
        ];

        $list = [];
        foreach ($channels as $key => $channel) {
            if (in_array($key, array_column($data, 'channel'))) {
                $row = $data[$key];
                $temp = [
                    'channel'       => $key,
                    'channel_txt'   => $channel,
                    'list'          => $listStatusArray[$row['list_status']],
                    'list_status'   => $row['list_status'],
                    'update_status' => 1,
                    'id'            => $row['id'],
                    'app_id'        => $row['app_id'],
                    'version_name'  => $row['version_name'],
                    'version_no'    => $row['version_no'],
                    'status_text'   => $statusArray[$row['status']],
                    'status'        => $row['status'],
                ];
            } else {
                $temp = [
                    'channel'      => $key,
                    'channel_txt'  => $channel,
                    'list'         => '未上架',
                    'list_status'   => 0,
                    'update_status' => 1,
                    'id'           => 0,
                    'app_id'       => $appId,
                    'version_name' => '',
                    'version_no'   => '',
                    'status_text'  => '',
                    'status'       => 0,
                ];
            }
            $list[] = $temp;
        }
        return $list;
    }

    /**
     * 切换应用渠道状态
     *
     * @param $id
     * @param $status
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function switchApp($id, $status): array
    {
        $model = AppChannel::find($id);
        // 上架状态
        $statusArray = [
            0 => '审核中',
            1 => '审核通过',
        ];
        if ($model) {
            $model->status = $status;
            if ($model->force()->save()) {
                return ['error' => 0, 'message' => '设置' . $statusArray[$status] . '成功'];
            } else {
                return ['error' => 1, 'message' => '设置' . $statusArray[$status] . '失败'];
            }
        } else {
            return ['error' => 1, 'message' => '渠道不存在'];
        }
    }

    /**
     * 设置上下架状态
     *
     * @param $id
     * @param $status
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function setListStatus($id, $status): array
    {
        $model = AppChannel::find($id);
        // 上架状态
        $statusArray = [
            0 => '下架',
            1 => '上架',
        ];
        if ($model) {
            $model->list_status = $status;
            if ($model->force()->save()) {
                return ['error' => 0, 'message' => '设置' . $statusArray[$status] . '成功'];
            } else {
                return ['error' => 1, 'message' => '设置' . $statusArray[$status] . '失败'];
            }
        } else {
            return ['error' => 1, 'message' => '渠道不存在'];
        }
    }

    /**
     * 设置APP渠道
     *
     * @param $data
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function setAppChannel($data)
    {
        $id = $data['id'];
        // 检查是否已有相同的 app_id 和 channel
        $exist = AppChannel::where('app_id', $data['app_id'])
            ->where('channel', $data['channel'])
            ->find();

        // 如果找到了相同的记录，返回错误提示
        if ($exist) {
            return ['error' => 1, 'message' => 'app_id 和 channel 已存在，无法新增或编辑'];
        }

        $model = AppChannel::find($id);
        if ($model) {
            $model->app_id = $data['app_id'];
            $model->version_name = $data['version_name'];
            $model->version_no = $data['version_no'];
            $model->update_time = date('Y-m-d H:i:s');
        } else {
            $model = new AppChannel();
            $model->app_id = $data['app_id'];
            $model->version_name = $data['version_name'];
            $model->version_no = $data['version_no'];
            $model->channel = $data['channel'];
            $model->create_time = date('Y-m-d H:i:s');
            $model->status = 0;
        }
        if ($model->save()) {
            return ['error' => 0, 'message' => '操作成功', 'data' => $model->id];
        } else {
            return ['error' => 1, 'message' => '操作失败'];
        }
    }
}
