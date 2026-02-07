<?php

namespace app\common\tool;

class CURL {

    /**
     * @brief 发起一个HTTP(S)请求，并返回响应文本
     * @param $url  string 请求地址
     * @param array $param 请求参数
     * @param string $method 请求类型(GET|POST)
     * @param int $timeout 超时时间
     * @param null $exOptions 额外配置
     * @return mixed
     */
    public static function http($url, $param = [], $method = 'GET', $timeout = 15, $exOptions = NULL) {
        // 判断是否开启了curl扩展
        if (!function_exists('curl_init')) exit('please open this curl extension');

        // 将请求方法变大写
        $method = strtoupper($method);
        $ch     = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if (isset($_SERVER['HTTP_USER_AGENT'])) curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        if (isset($_SERVER['HTTP_REFERER'])) curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_REFERER']);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if (!empty($param)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, (is_array($param)) ? http_build_query($param) : $param);
                }
                break;
            case 'GET':
            case 'DELETE':
                if ($method == 'DELETE') {
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                }
                if (!empty($param)) {
                    $url = $url . (strpos($url, '?') ? '&' : '?') . (is_array($param) ? http_build_query($param) : $param);
                }
                break;
        }
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        // 设置额外配置
        if ($exOptions) {
            foreach ($exOptions as $k => $v) {
                curl_setopt($ch, $k, $v);
            }
        }
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}