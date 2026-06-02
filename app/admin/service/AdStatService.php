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
                'u.report_time',
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
        $adStats  = self::getAdStats($data, $startTime, $endTime);

        foreach ($data as &$item) {
            $statKey                    = self::getAdStatKey($item['app_id'] ?? '', $item['oaid'] ?? '');
            $stat                       = $adStats[$statKey] ?? self::emptyAdStat();
            $channelKey                 = strtolower((string)($item['channel'] ?? ''));
            $item['channel_txt']        = $channels[$channelKey] ?? ($item['channel'] ?? '');
            $item['splash_income']      = self::formatAmount($stat['splash_income']);
            $item['interstitial_income'] = self::formatAmount($stat['interstitial_income']);
            $item['total_income']       = self::formatAmount($stat['splash_income'] + $stat['interstitial_income']);
            $item['splash_count']       = $stat['splash_count'];
            $item['interstitial_count'] = $stat['interstitial_count'];
            $item['total_count']        = $stat['splash_count'] + $stat['interstitial_count'];
            $item['splash_cpm']         = self::formatCpm($stat['splash_income'], $stat['splash_count']);
            $item['interstitial_cpm']   = self::formatCpm($stat['interstitial_income'], $stat['interstitial_count']);
            $item['report_platform']    = in_array($channelKey, self::getReportPlatforms()) ? $channelKey : '';
            $item['report_platform_txt'] = $item['report_platform'] ? strtoupper($item['report_platform']) : '未知';
            $item['report_status']      = self::getReportStatus($item);
            $item['report_status_txt']  = self::getReportStatusText($item);
            $item['active_time_txt']    = self::formatActiveTime($item);
            $item['report_time_txt']    = self::formatReportTime($item);
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
     * 获取当前页广告展示统计
     *
     * @param array       $users
     * @param string|null $startTime
     * @param string|null $endTime
     * @return array
     * @throws DbException
     */
    private static function getAdStats(array $users, $startTime = null, $endTime = null): array {
        $appIds = [];
        $oaids  = [];

        foreach ($users as $user) {
            if (!empty($user['app_id']) && !empty($user['oaid'])) {
                $appIds[] = $user['app_id'];
                $oaids[]  = $user['oaid'];
            }
        }

        $appIds = array_values(array_unique($appIds));
        $oaids  = array_values(array_unique($oaids));

        if (empty($appIds) || empty($oaids)) {
            return [];
        }

        $query = Db::name('report_vivo_data')
            ->field([
                'app_id',
                'oaid',
                'ad_type',
                'COUNT(*)'    => 'show_count',
                'SUM(ecpm)'   => 'income',
            ])
            ->whereIn('app_id', $appIds)
            ->whereIn('oaid', $oaids)
            ->where('action', 'adShow');

        if ($startTime && $endTime) {
            $query->where('created_at', '>', date('Y-m-d H:i:s', strtotime($startTime)));
            $query->where('created_at', '<', date('Y-m-d H:i:s', strtotime($endTime . ' 23:59:59')));
        }

        $rows = $query->group('app_id, oaid, ad_type')->select()->toArray();
        $data = [];

        foreach ($rows as $row) {
            $adType = self::normalizeAdType($row['ad_type'] ?? '');
            if (!in_array($adType, ['SPLASH', 'INTERSTITIAL'])) {
                continue;
            }

            $key = self::getAdStatKey($row['app_id'] ?? '', $row['oaid'] ?? '');
            if (!isset($data[$key])) {
                $data[$key] = self::emptyAdStat();
            }

            if ($adType === 'SPLASH') {
                $data[$key]['splash_income'] += self::convertEcpmIncome((float)($row['income'] ?? 0));
                $data[$key]['splash_count']  += (int)($row['show_count'] ?? 0);
                continue;
            }

            $data[$key]['interstitial_income'] += self::convertEcpmIncome((float)($row['income'] ?? 0));
            $data[$key]['interstitial_count']  += (int)($row['show_count'] ?? 0);
        }

        return $data;
    }

    /**
     * 空广告统计
     *
     * @return array
     */
    private static function emptyAdStat(): array {
        return [
            'splash_income'       => 0.00,
            'interstitial_income' => 0.00,
            'splash_count'        => 0,
            'interstitial_count'  => 0,
        ];
    }

    /**
     * 广告统计键
     *
     * @param mixed $appId
     * @param mixed $oaid
     * @return string
     */
    private static function getAdStatKey($appId, $oaid): string {
        return (string)$appId . '|' . (string)$oaid;
    }

    /**
     * 格式化广告类型
     *
     * @param string $adType
     * @return string
     */
    private static function normalizeAdType(string $adType): string {
        return strtoupper(trim($adType, " \t\n\r\0\x0B,"));
    }

    /**
     * 格式化金额
     *
     * @param float $amount
     * @return string
     */
    private static function formatAmount(float $amount): string {
        return number_format($amount, 2, '.', '');
    }

    /**
     * eCPM转实际收入
     *
     * @param float $income
     * @return float
     */
    private static function convertEcpmIncome(float $income): float {
        return $income / 1000;
    }

    /**
     * 格式化CPM
     *
     * @param float $income
     * @param int   $count
     * @return string
     */
    private static function formatCpm(float $income, int $count): string {
        if ($count <= 0) {
            return '0.00';
        }

        return number_format($income / $count, 2, '.', '');
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
        if (!in_array($platform, self::getReportPlatforms())) {
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
     * 支持手动回传的平台
     *
     * @return array
     */
    private static function getReportPlatforms(): array {
        return ['vivo', 'oppo', 'xiaomi', 'huawei', 'honor', 'rongyao'];
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

    /**
     * 格式化回传时间
     *
     * @param array $item
     * @return string
     */
    private static function formatReportTime(array $item): string {
        $time = trim((string)($item['report_time'] ?? ''));

        if ($time === '' || $time === '0000-00-00 00:00:00') {
            return '';
        }

        return $time;
    }
}
