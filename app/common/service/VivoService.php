<?php

namespace app\common\service;


use app\common\service\ReportData;
use app\jobs\Queue;
use Psr\SimpleCache\InvalidArgumentException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Log;
use think\response\Json;

class VivoService {

    const  CLIENT_ID      = 20260401064;
    const  CLIENT_SECRET  = '66B348A2A5F15DE9A1EDC39B622DE23F264CFBA41065B33FC41082F258247B07';
    const  ADVERTISER_ID  = '267618e9b16e8a44f2ee';
    const  AD_SHOW_ACTION = 'adShow';

    /**
     *  配置
     *
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getAdReportConfig(): array {
        return Db::name('ad_report_config')->where('status', 1)->find();
    }

    /**
     * 通知
     *
     * @param $appId
     * @return array
     */
    public static function notify($appId): array {
        header('Content-Type: application/json; charset=utf-8');
        $companyId = 25;
        $logPrefix = "[Vivo-Click-CB][company={$companyId}][app={$appId}]";
        $rawInput  = '';

        try {
            $rawInput = file_get_contents('php://input');
            Db::name('vivo_notify_request_logs')->insert([
                'company_id' => $companyId,
                'app_id'     => $appId,
                'raw_input'  => $rawInput,
                'request_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512, 'UTF-8'),
                'status'     => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            Log::info("{$logPrefix}[INPUT] Raw input received | data=" . $rawInput);
            $postData = json_decode($rawInput, true);

            Log::info("{$logPrefix}[RECV] Raw input received | data=" . json_encode($postData, JSON_UNESCAPED_UNICODE));

            if (!is_array($postData)) {
                throw new \Exception("Invalid JSON data");
            }

            $total   = count($postData);
            $skipped = 0;
            $stored  = 0;

            foreach ($postData as $index => $item) {
                $itemTag = "[item=" . ($index + 1) . "/{$total}]";
                $clickId = trim($item['clickId'] ?? '');

                if ($clickId === '') {
                    $skipped++;
                    Log::info("{$logPrefix}{$itemTag}[SKIP] Empty clickId | raw=" . json_encode($item, JSON_UNESCAPED_UNICODE));
                    continue;
                }

                $oaid = trim($item['oaid'] ?? '');

                if (empty($oaid)) {
                    $skipped++;
                    Log::info("{$logPrefix}{$itemTag}[SKIP] Empty oaid | clickId={$clickId}");
                    continue;
                }

                // 1. 构建入库数据
                $insertData = [
                    'company_id'       => $companyId,
                    'app_id'           => $appId,
                    'click_id'         => $clickId,
                    'request_id'       => trim($item['requestId'] ?? ''),
                    'imei'             => trim($item['imei'] ?? ''),
                    'click_time'       => $item['clickTime'] ?? 0,
                    'ip'               => trim($item['ip'] ?? ''),
                    'ua'               => mb_substr(trim($item['ua'] ?? ''), 0, 1024, 'UTF-8'),
                    'oaid'             => $oaid,
                    'creative_id'      => $item['creativeId'] ?? 0,
                    'media_type'       => $item['mediaType'] ?? 0,
                    'advertiser_id'    => trim($item['advertiserId'] ?? ''),
                    'advertiser_name'  => mb_substr(trim($item['advertiserName'] ?? ''), 0, 255, 'UTF-8'),
                    'advertisement_id' => $item['advertisementId'] ?? 0,
                    'ad_name'          => mb_substr(trim($item['adName'] ?? ''), 0, 255, 'UTF-8'),
                    'group_id'         => $item['groupId'] ?? 0,
                    'group_name'       => mb_substr(trim($item['groupName'] ?? ''), 0, 255, 'UTF-8'),
                    'campaign_id'      => $item['campaignId'] ?? 0,
                    'campaign_name'    => mb_substr(trim($item['campaignName'] ?? ''), 0, 255, 'UTF-8'),
                    'expinfo'          => trim($item['expinfo'] ?? ''),
                    'activation_time'  => 0,
                    'combine_id'       => $oaid . '_' . $appId,
                ];

                // 2. 安全过滤
                foreach ($insertData as $key => $value) {
                    if (is_string($value)) {
                        $insertData[$key] = preg_replace('/[\r\n\t\x00-\x1f]/', '', $value);
                    }
                }

                $where = [
                    'company_id' => $insertData['company_id'],
                    'app_id'     => $insertData['app_id'],
                    'click_id'   => $insertData['click_id'],
                ];
                // 3. 入库
                $vivoUser = Db::name('vivo_click_postbacks')->where($where)->find();

                if ($vivoUser) {
                    // 更新click id
                    if (empty($vivoUser['click_id']) && !empty($insertData['click_id'])) {
                        Db::name('vivo_click_postbacks')->where($where)->update(['click_id' => $insertData['click_id']]);
                        // 更新用户数据
                        self::updateUser($oaid, $appId, $insertData['click_id']);
                    }
                    $skipped++;
                    Log::info("{$logPrefix}{$itemTag}[SKIP] Duplicate click data | clickId={$clickId} | oaid={$oaid}");
                    continue;
                }
                // 更新click id
                if (!empty($insertData['click_id'])) {
                    // 更新用户数据
                    self::updateUser($oaid, $appId, ['vivo_clickid' => $insertData['click_id']]);
                }

                Db::name('vivo_click_postbacks')->insert($insertData);
                $stored++;
                Log::info("{$logPrefix}{$itemTag}[STORED] Click data saved | clickId={$clickId} | oaid={$oaid}");
            }

            Log::info("{$logPrefix}[DONE] Processing complete | total={$total} stored={$stored} skipped={$skipped}");
            return ['code' => 0, 'msg' => '操作成功'];
        } catch (\Exception $e) {
            Log::error("{$logPrefix}[ERROR] Exception caught | msg=" . $e->getMessage() . " | raw=" . ($rawInput ?? ''), [
                'trace' => $e->getTraceAsString(),
            ]);
            return ['code' => 0, 'msg' => 'received'];
        }
    }

    /**
     *  上报数据vivo数据
     *
     * @param        $oaid
     * @param        $clickId
     * @param        $pkgName
     * @param string $appId
     * @param int    $reportType 上报类型 0：广告上报 1；启动上报
     * @param int    $dataFor    1：精准上报 2：归因兜底上报
     * @return mixed
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function reportVivo($oaid, $clickId, $pkgName, $appId, $reportType = 0, $dataFor = 1) {
        $clientId     = self::CLIENT_ID;
        $clientSecret = self::CLIENT_SECRET;

        // 判断今天上报是否已经达标【激活不受限制】
//        if (self::isUploadUserPercentageReached($appId) && ($reportType == 0)) {
//            return ['error' => 1, 'message' => 'IS UPLOAD USER PERCENTAGE REACHED'];
//        }

        // 实例化User 控制器
        $userController  = app()->make(ReportData::class);
        $oldRefreshToken = Db::name('user_vivodataconfig')->where('id', 1)->value('oldRefreshToken');
        $refreshToken    = $userController->refreshToken($clientId, $clientSecret, $oldRefreshToken);
        if (isset($refreshToken['access_token']) && isset($refreshToken['refresh_token'])) {
            Db::name('user_vivodataconfig')->where('id', 1)->update(['oldRefreshToken' => $refreshToken['refresh_token']]);
        }

        // ==================== 调用 ====================
        // 1. 准备的认证信息
        $AccessToken  = $refreshToken['access_token']; // 真实Access Token
        $AdvertiserId = self::ADVERTISER_ID; // 真实的广告主ID

        // 根据appId从app表获取srcId
        $srcId = Db::name('app')->where('app_id', $appId)->value('src_id');
        if (empty($srcId)) {
            return ['error' => 1, 'message' => "[Vivo-Report] 未找到appId={$appId}对应的srcId"];
        }

        if ($reportType == 0) {
            // 判断是否激活
            $eligible = self::isEligibleReport($appId, $oaid);
            if ($eligible['error'] == 1) {
                return ['error' => 1, 'message' => "[Vivo-AdReport]----> {$eligible['message']} | pkgName={$pkgName} appId={$appId}"];
            }

            // 获取vivo上报数据
            $reportData = self::getVivoAdReportData($pkgName, $appId, $oaid);
            if (empty($reportData['dataList'])) {
                return ['error' => 1, 'message' => "[Vivo-AdReport] no reportable data | pkgName={$pkgName} appId={$appId}", 'data' => $reportData];
            }
            // 回传数据到vivo
            self::uploadVivoAdReportData($pkgName, $appId, $reportData, $dataFor);
            // 将APP 上报数据更新成已经回传
            Db::name('report_vivo_data')
                ->where('channel', 'vivo')
                ->where('app_id', $appId)
                ->where('oaid', $oaid)
                ->update([
                    'is_report'  => 1,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            // 将某个oai
            Db::name('vivo_click_postbacks')->where(['oaid' => $oaid, 'app_id' => $appId])->update(['is_report' => 1]);
            // 将用户表is_report改为1
            Db::name('user')->where(['oaid' => $oaid, 'app_id' => $appId])->update(['is_report' => 1]);
        } else {
            // 新用户需要回传激活
            var_dump('新用户需要回传激活');
            $id = Db::table('vivo_click_postbacks')->where(['oaid' => $oaid, 'app_id' => $appId])
                ->where('activation_time', '>', 0)->value('id');
            if (!empty($id)) {
                var_dump(1);
                return ['error' => 1, 'message' => '[Vivo-StartReport] not a new user'];
            }
            var_dump(2);
            $dataList[] = [
                'userIdType' => 'OAID',
                'userId'     => $oaid,
                'cvType'     => 'ACTIVATION', // 启动上报使用激活类型
                'cvTime'     => (int)(microtime(true) * 1000),
            ];
            var_dump(3);
            if (empty($dataList)) {
                var_dump(4);
                return ['error' => 1, 'message' => '[Vivo-StartReport] all records oaid empty'];
            }

            // 构建请求数据（启动上报使用模糊归因，无clickId
            $requestData = [
                'srcType'  => 'app',
                'pkgName'  => $pkgName,
                'srcId'    => $srcId, // 事件源ID，从app表获获取
                'dataFrom' => '1',
                'dataFor'  => '1', // 模糊归因
                'dataList' => $dataList,
            ];

            $requestData['srcId'] = preg_replace('/\s+/', '-', trim($requestData['srcId']));

            // 调用函数上传数据
            $result = $userController->uploadUserBehaviorData($AccessToken, $AdvertiserId, $requestData);
            var_dump($result);
            if ($result['code'] == 0) {
                var_dump(5);
                // 批量更新已上报状态
                Db::name('app_start_records')->where(['id' => $id])->update(['report_time' => time()]);
                Db::name('vivo_click_postbacks')->where(['oaid' => $oaid, 'app_id' => $appId])->update(['activation_time' => time()]);
                // 更新用户表
                self::activeUser($appId, $oaid);
                Log::info("[Vivo-StartReport] success id: " . $id);
                return ['error' => 0, 'message' => '[Vivo-StartReport] success'];
            } else {
                return ['error' => 1, 'message' => '[Vivo-StartReport] fail'];
            }
        }
    }

    /**
     * 获取满足广告上报给VIVO平台规则的数据
     * @param string $pkgName
     * @param string $appId
     * @param string $oaid
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    private static function getVivoAdReportData($pkgName, $appId, $oaid = '', $channel = 'vivo'): array {
        $config = self::getAdReportConfig();
        // 获取动态查询字段
//        [$reportField, $activeField] = self::reportField($channel);
        if ($oaid !== '') {
            // 判断是否已经回传过
            $db = self::db($appId, $oaid);
            $db->where('is_report', 1);
            $reported = $db->value('id');
            if (!empty($reported)) {
                return ['dataList' => [], 'message' => "[$channel-AdReport] oaid already reported | oaid={$oaid} appId={$appId}"];
            }
        }

        // 判断是否超出7天
        $postbackQuery = self::db($appId, $oaid);
        $postbackQuery->where('is_report', 0)
            ->where('active_time', '>=', time() - ((int)$config['valid_days'] * 86400))
            ->where('active_time', '>', 0)
            ->where('oaid', $oaid);

        $postbackRows = $postbackQuery->field('id, oaid')->select()->toArray();
        if (empty($postbackRows)) {
            return ['dataList' => [], 'message' => "[$channel-AdReport] no valid activation | oaid={$oaid} appId={$appId}"];
        }

        $recordQuery = Db::name('report_vivo_data')
            ->where('is_report', 0)
            ->where('action', self::AD_SHOW_ACTION)
            ->where('app_id', $appId)
            ->where('oaid', $oaid)
            ->where('channel', 'vivo')
            ->field('oaid, COUNT(*) AS report_count, SUM(ecpm) AS total_ecpm')
            ->group('oaid')
            ->having('report_count >= ' . (int)$config['show_report_count'] . ' OR total_ecpm >= ' . (float)$config['ecpm_report_amount'])
            ->order('report_count', 'desc')
            ->order('total_ecpm', 'desc');

        $records = $recordQuery->select()->toArray();

        if (empty($records)) {
            return ['dataList' => [], 'message' => 'report_vivo_data query is empty'];
        }

        $dataList = [];
        $now      = (int)(microtime(true) * 1000);

        $dataList[] = [
            'userIdType' => 'OAID',
            'userId'     => $oaid,
            'cvType'     => 'REGISTER',
            'cvTime'     => $now,
        ];

        return [
            'dataList' => $dataList,
        ];
    }

    /**
     *  回传数据
     *
     * @param       $pkgName
     * @param       $appId
     * @param array $reportData
     * @param       $dataFor
     * @return bool
     * @throws DbException
     */
    private static function uploadVivoAdReportData($pkgName, $appId, array $reportData, $dataFor = 1): bool {
        $clientId     = self::CLIENT_ID;
        $clientSecret = self::CLIENT_SECRET;

        $userController  = app()->make(ReportData::class);
        $oldRefreshToken = Db::name('user_vivodataconfig')->where('id', 1)->value('oldRefreshToken');
        $refreshToken    = $userController->refreshToken($clientId, $clientSecret, $oldRefreshToken);
        if (isset($refreshToken['access_token']) && isset($refreshToken['refresh_token'])) {
            Db::name('user_vivodataconfig')->where('id', 1)->update(['oldRefreshToken' => $refreshToken['refresh_token']]);
        }

        $srcId = Db::name('app')->where('app_id', $appId)->value('src_id');
        if (empty($srcId)) {
            Log::error("[Vivo-AdReport] missing srcId | appId={$appId}");
            return false;
        }

        $requestData = [
            'srcType'  => 'app',
            'pkgName'  => $pkgName,
            'srcId'    => preg_replace('/\s+/', '-', trim($srcId)),
            'dataFrom' => '1',
            'dataFor'  => $dataFor,
            'dataList' => $reportData['dataList'],
        ];

        $rule  = $reportData['rule'] ?? '';
        $ids   = $reportData['ids'] ?? [];
        $count = count($ids);

        $result = $userController->uploadUserBehaviorData($refreshToken['access_token'], self::ADVERTISER_ID, $requestData);
        if ($result === false) {
            Log::error("[Vivo-AdReport] upload fail | rule={$rule} count={$count}");
            return false;
        }

        Log::info("[Vivo-AdReport] upload success | rule={$rule} count={$count}");
        return true;
    }

    /**
     *  兜底回传数据
     *
     * @param        $oaid
     * @param        $pkgName
     * @param        $appId
     * @param string $channel
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws ModelNotFoundException
     */
    public static function fallbackReport($oaid, $pkgName, $appId, $channel = 'vivo'): bool {
        $oaid    = trim($oaid);
        $pkgName = trim($pkgName);
        $appId   = trim($appId);
        $channel = trim($channel);

        if ($oaid === '' || $pkgName === '' || $appId === '') {
            Log::info("[$channel-AdFallback] missing required params | pkgName={$pkgName} appId={$appId}");
            return false;
        }

        if (self::isUploadUserPercentageReached($appId, $channel)) {
            Log::info("[$channel-AdFallback] report percentage reached | pkgName={$pkgName} appId={$appId}");
            return false;
        }

        $startTime = strtotime(date('Y-m-d'));
        $endTime   = time();

//        [$reportField, $activeField] = self::reportField($channel);

        $postbackId = (self::db($appId, $oaid))
            ->where('is_report', 0)
            ->where('active_time', '>=', $startTime)
            ->where('active_time', '<', $endTime)
            ->value('id');

        if (empty($postbackId)) {
            Log::info("[$channel-AdFallback] no unreported active users | pkgName={$pkgName} appId={$appId}");
            return false;
        }

        switch ($channel) {
            case 'vivo':
                $reportData = [
                    'dataList' => [
                        [
                            'userIdType' => 'OAID',
                            'userId'     => $oaid,
                            'cvType'     => 'REGISTER',
                            'cvTime'     => (int)(microtime(true) * 1000),
                        ],
                    ],
                    'rule'     => 'fallback_top_value',
                ];

                if (!self::uploadVivoAdReportData($pkgName, $appId, $reportData, 2)) {
                    Log::info("[$channel-AdFallback] uploadVivoAdReportData false | pkgName={$pkgName} appId={$appId}");
                    return false;
                }
                break;
            case 'oppo':
                if (!OppoService::reportOppo($oaid, $pkgName)) {
                    Log::info("[$channel-AdFallback] reportOppo false | pkgName={$pkgName} appId={$appId}");
                    return false;
                }
                break;
            default:
                Log::info("[$channel-AdFallback] unsupported channel | pkgName={$pkgName} appId={$appId}");
                return false;
        }

        // 将oaid 标记为已经回传
        (self::db($appId, $oaid))->update(['is_report' => 1]);

        // 将到目前为止的数据标记为已经回传
        Db::name('report_vivo_data')
            ->where('app_id', $appId)
            ->where('oaid', $oaid)
            ->update([
                'is_report'  => 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        Log::info("[$channel-AdFallback] fallback report success | pkgName={$pkgName} appId={$appId}");
        return true;
    }

    /**
     * 转化上报
     *
     * @param array $data
     * @return mixed
     */
    public static function report(array $data) {
        $pkgName   = trim($data['pkgName'] ?? '');
        $oaid      = trim($data['oaid'] ?? '');
        $cvType    = (int)($data['cvType'] ?? '');
        $payAmount = (float)($data['payAmount'] ?? 0);
        $ecpm      = (float)($data['ecpm'] ?? 0);
        $channel   = $data['channel'] ?? '';

        // 参数校验
        if (!$pkgName) {
            return ['error' => 1, 'message' => '转化上报失败：pkgName不能为空'];
        }

        if (!$oaid) {
            return ['error' => 1, 'message' => '转化上报失败：oaid不能为空'];
        }

        if (!$cvType) {
            return ['error' => 1, 'message' => '转化上报失败：cvType不能为空'];
        }

        try {
            // 写入数据
            Db::name('report_data')->insertGetId([
                'channel'    => $data['channel'] ?? '',
                'pkg_name'   => $pkgName,
                'oaid'       => $oaid,
                'cv_type'    => $cvType,
                'pay_amount' => $payAmount,
                'action'     => $data['action'] ?? '',
                'ad_type'    => $data['ad_type'] ?? '',
                'code_id'    => $data['code_id'] ?? 0,
                'slot_id'    => $data['slot_id'] ?? 0,
                'sdk_name'   => $data['sdk_name'] ?? '',
                'app_id'     => $data['app_id'] ?? '',
                'ecpm'       => $ecpm,
                'is_report'  => 0,
                'combine_id' => $oaid . '-' . ($data['app_id'] ?? ''),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // 推送队列上报vivo
            $clickId  = trim($data['clickId'] ?? '');
            $eligible = self::isEligibleReport($data['app_id'], $data['oaid'], true, $channel);
            if ($eligible['error'] == 0) {
                if ($channel == 'vivo') {
                    Queue::push(\app\jobs\VivoReportJob::class, [
                        'oaid'    => $oaid,
                        'clickId' => $clickId,
                        'channel' => $data['channel'] ?? '',
                        'pkgName' => $pkgName,
                        'appId'   => $data['app_id'] ?? '',
                    ], 'vivo_report');
                } elseif ($channel == 'oppo') {
                    Queue::push(\app\jobs\OppoReportJob::class, [
                        'oaid'       => $oaid,
                        'channel'    => $data['channel'] ?? '',
                        'pkgName'    => $pkgName,
                        'appId'      => $data['app_id'] ?? '',
                        'reportType' => 2,
                    ], 'oppo_report');
                }
            } else {
                return ['error' => 0, 'message' => $eligible['message']];
            }

            return ['error' => 0, 'message' => 'OK'];
        } catch (\Throwable $e) {
            Log::error(sprintf(
                '转化上报异常：%s 文件:%s 行号:%s',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));
            return ['error' => 1, 'message' => 'SYSTEM ERROR'];
        }
    }

    /**
     *  归因
     *
     * @param        $oaid
     * @param string $channel
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function attribution($oaid, $channel = 'vivo') {
        $row = Db::name('vivo_click_postbacks')->where('channel', $channel)->where('oaid', $oaid)->select()->toArray();
        if (!empty($row)) {
            return true;
        }
        return false;
    }

    /**
     *  某个APP ID 今天的上报率
     *
     * @param $appId
     * @return int
     * @throws DbException
     */
    public static function calculateTodayReportedPercentage($appId): int {
        $startTime = strtotime(date('Y-m-d'));
        $endTime   = time();

//        [$reportField, $activeField] = self::reportField($channel);

        $db = (self::db($appId))
            ->where('active_time', '>=', $startTime)
            ->where('active_time', '<', $endTime);


        $total = $db->count();

        if ($total <= 0) {
            return 0;
        }

        $reported = (clone $db)->where('is_report', 1)->count();
        return intval(($reported / $total) * 100);
    }

    /**
     *  今天上报是否达标，true：是【今天回传用户百分比已经满足不需要再次上报】 false：未满足
     *
     * @param        $appId
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws InvalidArgumentException
     * @throws ModelNotFoundException
     */
    public static function isUploadUserPercentageReached($appId): bool {
        $cacheKey = 'upload_user_percentage_reached_v1_' . $appId . '_' . date('Ymd');
        if (Cache::store('redis')->get($cacheKey)) {
            return true;
        }

        $reportPercentage = self::getAdReportConfig()['report_percent'] ?? 0;
        // 今日上报百分比
        $reportedPercentage = self::calculateTodayReportedPercentage($appId);
        $isReached          = $reportedPercentage >= $reportPercentage;

        if ($isReached) {
            $expire = strtotime(date('Y-m-d') . ' 23:59:59') - time();
            Cache::store('redis')->set($cacheKey, 1, $expire);
        }
        return $isReached;
    }

    /**
     *  是否能上报
     *
     * @param        $appId
     * @param        $oaid
     * @param bool   $bool
     * @param string $channel
     * @return array|bool|int[]
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
    public static function isEligibleReport($appId, $oaid, $bool = false, $channel = 'vivo') {
        $config = self::getAdReportConfig();

//        [$reportField, $activeField] = self::reportField($channel);
        $userData = self::db($appId, $oaid)->where('is_report', 0)->field(['active_time', 'is_report'])->find();
        if (empty($userData)) {
            if ($bool) {
                return false;
            } else {
                return ['error' => 1, 'status' => 1, 'message' => 'EMPTY DATA'];
            }
        }
        if ($userData['active_time'] < 0) {
            if ($bool) {
                return false;
            }
            return ['error' => 1, 'status' => 2, 'message' => 'NOT ACTIVE'];
        }
        if ($userData['is_report'] == 1) {
            if ($bool) {
                return false;
            }
            return ['error' => 1, 'status' => 3, 'message' => 'IS REPORTED'];
        }
        if ($userData['active_time'] < time() - ((int)$config['valid_days'] * 86400)) {
            if ($bool) {
                return false;
            }
            return ['error' => 1, 'status' => 4, 'message' => 'EXPIRED 7 DAYS'];
        }
        if ($bool) {
            return true;
        }
        return ['error' => 0, 'status' => 0, 'message' => 'OK'];
    }

    /**
     *  设置用户
     *
     * @param $oaid
     * @param $data
     * @return int|string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function setUser($oaid, $appId, $data) {
        $user = Db::name('user')->where(['oaid' => $oaid, 'app_id' => $appId])->find();
        if ($user) {
            return Db::name('user')->where(['oaid' => $oaid, 'app_id' => $appId])->update($data);
        }

        $data['oaid'] = $oaid;
        return Db::name('user')->insert($data);
    }

    /**
     *  设置用户
     *
     * @param $oaid
     * @param $data
     * @return false|int
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function updateUser($oaid, $appId, $data) {
        $user = Db::name('user')->where(['oaid' => $oaid, 'app_id' => $appId])->find();
        if ($user) {
            return Db::name('user')->where(['oaid' => $oaid, 'app_id' => $appId])->update($data);
        } else {
            return false;
        }
    }

    /**
     * @param $appId
     * @param $oaid
     * @return Db
     */
    public static function db($appId = '', $oaid = '') {
        $db = Db::name('users');
        if ($appId) {
            $db->where(['app_id' => $appId]);
        }
        if ($oaid) {
            $db->where(['oaid' => $oaid]);
        }
        return $db;
    }

    public static function reportField($channel = 'vivo'): array {
        return ['is_report', 'active_time'];
//        $fields = [
//            'vivo' => ['is_reportvivo', 'vivo_active_time'],
//            'oppo' => ['is_reportoppo', 'oppo_active_time'],
//        ];
//        if (!isset($fields[$channel])) {
//            throw new Exception('CHANNEL NOT SPECIFIED');
//        }
//        return $fields[$channel];
    }

    /**
     *  设置用户注册时间
     *
     * @param $appId
     * @param $oaid
     * @return int
     * @throws DbException
     */
    public static function activeUser($appId, $oaid) {
        return self::db($appId, $oaid)->update(['active_time' => time()]);
    }
}
