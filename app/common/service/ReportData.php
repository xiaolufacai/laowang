<?php

namespace app\common\service;

use think\App;
use think\exception\ValidateException;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Request;
use think\facade\Log;

class ReportData {


    //回传上报数据V2
    public function vivoreturn_data($oaid, $clickId) {
        $clientId     = 20260401064;           // 替换为你的 client_id
        $clientSecret = '66B348A2A5F15DE9A1EDC39B622DE23F264CFBA41065B33FC41082F258247B07'; // 替换为你的 client_secret

        $oldRefreshToken = Db::name('user_vivodataconfig')->where('id', 1)->value('oldRefreshToken');
        $refreshToken    = $this->refreshToken($clientId, $clientSecret, $oldRefreshToken);
        if (isset($refreshToken['access_token']) && isset($refreshToken['refresh_token'])) {
            Db::name('user_vivodataconfig')->where('id', 1)->update(['oldRefreshToken' => $refreshToken['refresh_token']]);
        }

        // ==================== 调用 ====================
        // 1. 准备的认证信息
        $AccessToken  = $refreshToken['access_token']; // 真实的 Access Token
        $AdvertiserId = '267618e9b16e8a44f2ee'; // 真实的广告主ID

        // 2. 构建符合文档要求的请求数据
        if (!empty($clickId)) {
            $requestData = [
                'srcType'  => 'app', // 事件源类型
                'pkgName'  => 'com.dsjn.yicch', // 应用包名 (srcType=app/Quickapp 时必填)
                'srcId'    => 'ds-202604133309', // 事件源ID，在营销平台创建
                'dataFrom' => '1', // 事件产生来源，默认为1 (vivo事件)
                'dataFor'  => '0', // 归因类型，默认为0 (精准归因)
                'dataList' => [
                    [
                        'userIdType' => 'OAID', // 用户标识类型
                        'userId'     => $oaid, // 用户标识值
                        'cvType'     => 'ACTIVATION', // 事件类型，如激活、注册等 REGISTER
                        'cvTime'     => (int)(microtime(true) * 1000), // 事件发生时间戳（毫秒）
                        'clickId'    => $clickId, // vivo渠道+精准归因时必填
                        // 'extParam'   => [ // 扩展参数
                        //     'payAmount' => '100', // 付费金额，单位：分
                        // ]
                    ]

                ]
            ];
        } else {
            //手动上报，模糊归因
            $requestData = [
                'srcType'  => 'app', // 事件源类型
                'pkgName'  => 'com.dsjn.yicch', // 应用包名 (srcType=app/Quickapp 时必填)
                'srcId'    => 'ds-202604133309', // 事件源ID，在营销平台创建
                'dataFrom' => '1', // 事件产生来源，默认为1 (vivo事件)
                'dataFor'  => '1', // 归因类型，默认为0 (精准归因)
                'dataList' => [
                    [
                        'userIdType' => 'OAID', // 用户标识类型
                        'userId'     => $oaid, // 用户标识值
                        'cvType'     => 'REGISTER', // 事件类型，如激活、注册等 REGISTER
                        'cvTime'     => (int)(microtime(true) * 1000), // 事件发生时间戳（毫秒）
                        // 'extParam'   => [ // 扩展参数
                        //     'payAmount' => '100', // 付费金额，单位：分
                        // ]
                    ]

                ]
            ];
        }

        $requestData['srcId'] = preg_replace('/\s+/', '-', trim($requestData['srcId']));
        // 3. 调用函数上传数据
        $result = $this->uploadUserBehaviorData($AccessToken, $AdvertiserId, $requestData);
        if ($result !== false) {
            return true;
            // return $this->success($result,'上传成功！');
        } else {
            return false;
            // return $this->error('上传失败，请检查日志。!');
        }

    }


    /**
     * 获取 vivo 营销平台 Access Token
     *
     * @param int    $client_id     应用 ID
     * @param string $client_secret 应用密钥
     * @param string $code          授权码（authorization_code）
     * @return array|false 返回 token 信息数组 或 false（失败时）
     */
    public function getAccessTokenvivo($client_id, $client_secret, $code) {
        // 构造请求 URL（注意：GET 参数需正确拼接）
        $url = "https://marketing-api.vivo.com.cn/openapi/v1/oauth2/token?" . http_build_query([
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
                'grant_type'    => 'code', // 固定值
                'code'          => $code
            ]);

        // 初始化 cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'VivoMarketingAPI-PHP/1.0');

        // 执行请求
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);
        // 检查 cURL 错误
        if ($error) {
            error_log("cURL Error: " . $error);
            return false;
        }

        // 解析 JSON 响应
        $data = json_decode($response, true);

        // 判断 HTTP 状态码和响应结构
        if ($httpCode !== 200 || !is_array($data)) {
            error_log("HTTP $httpCode: " . $response);
            return false;
        }

        // 成功返回 token 数据
        return $data; // 通常包含 access_token, expires_in, refresh_token 等
    }


    /**
     * 使用 refresh_token 刷新 Access Token
     *
     * @param string $clientId     应用 ID
     * @param string $clientSecret 应用密钥
     * @param string $refreshToken 上次获取 token 时返回的 refresh_token 值
     * @return array|false          成功返回新 token 数据，失败返回 false
     */
    function refreshToken($clientId, $clientSecret, $refreshToken) {
        $url = "https://marketing-api.vivo.com.cn/openapi/v1/oauth2/refreshToken?" . http_build_query([
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken
            ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'VivoTokenRefresher/1.0');

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);
        if ($error) {
            error_log("cURL Error: " . $error);
            return false;
        }

        $data = json_decode($response, true);
        if (!$data || $httpCode !== 200) {
            error_log("Refresh failed (HTTP {$httpCode}): " . $response);
            return false;
        }

        // 假设 vivo 返回结构如：
        // {"code":200,"msg":"success","data":{"access_token":"new_...", "expires_in":86400, "refresh_token":"new_refresh_..."}}
        if (isset($data['data'])) {
            return $data['data'];
        }
        return $data; // 兼容直接返回顶层结构的情况
    }

    /**
     * 上传用户行为数据到 vivo 营销平台 (V2)
     *
     * @param string $accessToken  通过 OAuth2 获取的 Access Token
     * @param string $advertiserId 广告主 ID
     * @param array  $requestData  符合 API 文档要求的请求数据数组
     * @return array|false          返回 API 响应数据 或 false（失败时）
     */
    function uploadUserBehaviorData($accessToken, $advertiserId, $requestData) {
        // 构建请求URL，包含必需的查询参数
        $baseUrl     = 'https://marketing-api.vivo.com.cn/openapi/v2/advertiser/behavior/upload';
        $queryParams = [
            'access_token'  => $accessToken,
            'timestamp'     => round(microtime(true) * 1000), // 当前时间戳（毫秒）
            'nonce'         => uniqid(), // 随机字符串，保证唯一性
            'advertiser_id' => $advertiserId
        ];
        $url         = $baseUrl . '?' . http_build_query($queryParams);

        // 将请求数据编码为 JSON
        $jsonBody = json_encode($requestData, JSON_UNESCAPED_UNICODE);
        if ($jsonBody === false) {
            error_log("JSON Encode Error: " . json_last_error_msg());
            return false;
        }

        // 初始化 cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonBody)
        ]);

        curl_setopt($ch, CURLOPT_USERAGENT, 'VivoMarketingAPI-PHP/2.0');

        // 执行请求
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);
        // 检查 cURL 错误
        if ($error) {
            error_log("cURL Error ({$httpCode}): " . $error);
            return false;
        }

        // 解析 JSON 响应
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON Decode Error: " . json_last_error_msg() . " | Raw Response: " . $response);
            return false;
        }

        // 记录响应（可选，用于调试）
        // error_log("API Response ({$httpCode}): " . print_r($data, true));

        // 判断 HTTP 状态码
        if ($httpCode !== 200) {
            error_log("HTTP {$httpCode}: " . $response);
            return false;
        }

        // 成功返回 API 的响应数据
        return $data;
    }


    /**
     * OPPO 广告数据回传示例 (PHP)
     * 文档版本: v3.20
     */
    public function reportoppo($raw_oaid, $pkName = 'com.dsjn.yicch', $dataType = 2) {
        // ====== 配置信息 (由 OPPO 提供) ======
        $BASE64_KEY = 'XGAXicVG5GMBsx5bueOe4w=='; // AES 加密密钥 (Base64格式)
        $SALT       = 'e0u6fnlag06lc3pl';               // 签名盐值
        $API_URL    = 'https://api.ads.heytapmobi.com/api/uploadActiveData';

        // ====== 准备回传数据 ======
        $client_ip = request()->ip();
        $pkg       = $pkName; // 您的应用包名
        $ad_id     = 101097648; // 已归因的广告ID
        $timestamp = time() * 1000; // 当前时间戳 (毫秒)

        // 1. 对 IMEI 进行 AES 加密
//        $encrypted_oaid = strtolower(md5($raw_oaid));
        $encrypted_oaid = $this->aesEncrypt($raw_oaid, $BASE64_KEY);
        // 2. 构建请求 Body (JSON)
        $body_data = [
            'ouid'        => $encrypted_oaid,
            'clientIp'    => $client_ip,
            'pkg'         => $pkg,
            'adId'        => $ad_id,
            'timestamp'   => $timestamp,
            'dataType'    => $dataType, // 1.激活 2.注册
            'ascribeType' => 1, // 广告主归因
            'channel'     => 1, // OPPO
            'appType'     => 1, // 应用
            'type'        => 2, // imei md5加密 (此处文档示例为1，但实际传输的是AES加密后的值)
            'userIdType'  => 1  // oaid或imei归因
        ];

        $body_json = json_encode($body_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // 3. 生成签名 (signature)
        $signature = $this->generateSignature($body_json, $timestamp, $SALT);

        // 4. 设置请求头
        $headers = [
            'Content-Type: application/json',
            "signature: {$signature}",
            "timestamp: {$timestamp}"
        ];

        // 5. 发送 POST 请求
        $response = $this->sendPostRequest($API_URL, $body_json, $headers);
        $result   = json_decode($response, true);
        Log::info('【OPPO Click Callback - POST】Skipped: 上报oppo==' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['ret'] == 0) {
            return true;
        } else {
            return false;
        }
    }
    // ==================== 辅助函数 ====================

    /**
     * 使用 AES-128-ECB 对数据进行加密
     * @param string $data      原始数据
     * @param string $base64Key Base64格式的密钥
     * @return string Base64编码后的加密字符串
     */
    function aesEncrypt($data, $base64Key) {
        $key = base64_decode($base64Key);
        // PHP 的 openssl_encrypt 默认会处理 PKCS#7 填充 (等同于 PKCS#5)
        $encrypted = openssl_encrypt($data, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        return base64_encode($encrypted);
    }

    /**
     * 生成 MD5 签名
     * @param string $postData  JSON格式的请求体
     * @param int    $timestamp 时间戳 (毫秒)
     * @param string $salt      盐值
     * @return string 小写的MD5签名
     */
    function generateSignature($postData, $timestamp, $salt) {
        $content = $postData . $timestamp . $salt;
        return strtolower(md5($content));
    }

    /**
     * 发送 POST 请求
     * @param string $url     API 地址
     * @param string $data    JSON 数据
     * @param array  $headers 请求头
     * @return string 响应内容
     */
    function sendPostRequest($url, $data, $headers) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 生产环境请谨慎处理证书验证
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = 'Curl error: ' . curl_error($ch);
            curl_close($ch);
            return $error;
        }
        curl_close($ch);

        // 检查 HTTP 状态码
        if ($httpCode == 403) {
            return 'Error: Signature verification failed (HTTP 403).';
        }

        return $response;
    }


}