<?php
declare (strict_types=1);

namespace app\admin\service;

use app\common\model\Users;
use think\db\exception\DbException;
use think\facade\Db;

class AdStatService {

    /**
     * 查询广告统计列表
     *
     * @param string|null $appId
     * @param string|null $channel
     * @param string|null $startTime
     * @param string|null $endTime
     * @param int         $page
     * @param int         $pageSize
     *
     * @return array
     * @throws DbException
     */
    public static function list($appId = null, $channel = null, $startTime = null, $endTime = null, $page = 1, $pageSize = 10): array {
        $query = Db::name('users')
            ->alias('u')
            ->leftJoin('app a', 'a.app_id = u.app_id')
            ->field([
                'u.id',
                'u.app_id',
                'u.channel',
                'u.oaid',
                'u.is_report',
                'u.active_time',
                'a.ad_id',
            ]);

        if ($appId) {
            $query->where('u.app_id', $appId);
        }

        if ($channel) {
            $query->where('u.channel', $channel);
        }

        if ($startTime) {
            $query->where('u.active_time', '>=', strtotime($startTime));
        }

        if ($endTime) {
            $query->where('u.active_time', '<=', strtotime($endTime . ' 23:59:59'));
        }

        $users = $query->order('u.id', 'desc')
            ->paginate((int)$pageSize, false, ['page' => $page]);

        $data     = $users->items();
        $channels = config('app.channels');

        foreach ($data as &$item) {
            $channelKey                 = strtolower((string)($item['channel'] ?? ''));
            $item['channel_txt']        = $channels[$channelKey] ?? ($item['channel'] ?? '');
            $item['splash_income']      = '0.00';
            $item['interstitial_income'] = '0.00';
            $item['total_income']       = '0.00';
            $item['splash_count']       = 0;
            $item['interstitial_count'] = 0;
            $item['total_count']        = 0;
            $item['splash_cpm']         = '0.00';
            $item['interstitial_cpm']   = '0.00';
            $item['report_platform']    = in_array($channelKey, ['vivo', 'oppo']) ? $channelKey : '';
            $item['report_platform_txt'] = $item['report_platform'] ? strtoupper($item['report_platform']) : '未知';
            $item['report_status']      = self::getReportStatus($item);
            $item['report_status_txt']  = self::getReportStatusText($item);
            $item['active_time_txt']    = self::formatActiveTime($item);
            $item['report_time_txt']    = '';
            $item['ad_id']              = $item['ad_id'] ?? '';
        }

        return [
            'data'         => $data,
            'total'        => $users->total(),
            'current_page' => $users->currentPage(),
            'last_page'    => $users->lastPage(),
        ];
    }

    /**
     * 手动设置回传状态
     *
     * @param int    $id
     * @param string $platform
     * @return array
     */
    public static function report(int $id, string $platform): array {
        $user = Users::find($id);
        if (empty($user)) {
            return ['error' => 1, 'message' => '用户不存在'];
        }

        $platform = strtolower($platform);
        if (!in_array($platform, ['vivo', 'oppo'])) {
            return ['error' => 1, 'message' => '回传平台错误'];
        }

        $channel = strtolower((string)$user['channel']);
        if ($channel !== $platform) {
            return ['error' => 1, 'message' => '回传平台必须与投流渠道一致'];
        }

        $user['is_report'] = 1;

        if ($user->save()) {
            return ['error' => 0, 'message' => '回传成功'];
        }

        return ['error' => 1, 'message' => '回传失败'];
    }

    /**
     * 获取回传状态
     *
     * @param array $item
     * @return int
     */
    private static function getReportStatus(array $item): int {
        return (int)($item['is_report'] ?? 0);
    }

    /**
     * 获取回传状态文案
     *
     * @param array $item
     * @return string
     */
    private static function getReportStatusText(array $item): string {
        $channel = strtolower((string)($item['channel'] ?? ''));
        if ($channel === 'vivo') {
            return (int)($item['is_report'] ?? 0) === 1 ? 'VIVO已回传' : 'VIVO未回传';
        }

        if ($channel === 'oppo') {
            return (int)($item['is_report'] ?? 0) === 1 ? 'OPPO已回传' : 'OPPO未回传';
        }

        return (int)($item['is_report'] ?? 0) === 1 ? '已回传' : '未回传';
    }

    /**
     * 格式化激活时间
     *
     * @param array $item
     * @return string
     */
    private static function formatActiveTime(array $item): string {
        $time = (int)($item['active_time'] ?? 0);

        if ($time <= 0) {
            return '';
        }

        return date('Y-m-d H:i:s', $time);
    }
}
