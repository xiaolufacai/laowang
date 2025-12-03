<?php

namespace app\common\service;

use app\common\model\App;
use app\common\tool\CURL;

class WechatService {

    /**
     *  获取所有微信APP ID
     *
     * @return mixed
     */
    public static function apps() {
        return config('wechat.APPS');
    }

    /**
     *  获取 APP SECRET
     *
     * @param $appId
     * @return mixed|string
     */
    public static function secret($appId): mixed {
        return App::find(['wx_id' => $appId])->secret;
    }

    /**
     * @brief  获取微信授权url
     * @param string $redirectUrl 授权后跳转的URL
     * @param bool   $openIdOnly  是否只获取openid，true时，不会弹出授权页面，但只能获取用户的openid，而false时，弹出授权页面，可以通过openid获取用户信息
     * @param string $state       重定向后会带上 state 参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节
     * @return string
     * @author zclee
     */
    public static function getOAuthUrl($appId, $redirectUrl = '', $openIdOnly = false, $state = '') {
        $redirectUrl = urlencode($redirectUrl);
        $scope       = $openIdOnly ? 'snsapi_base' : 'snsapi_userinfo';
        $wechatAppid = $appId;
        // 加上参数验证
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$wechatAppid}&redirect_uri={$redirectUrl}&response_type=code&scope={$scope}&state={$state}#wechat_redirect";
    }

    /**
     * @brief  根据code获取access_token
     * @param $code string 授权code
     * @return mixed
     * @author zclee
     */
    public static function getoAuthAccessToken($appId, $code) {
        $appSecret = static::secret($appId);
        // 请求接口获取access_token和openid
        $apiUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appId . '&secret=' . $appSecret . '&code=' . $code . '&grant_type=authorization_code';
        $result = CURL::http($apiUrl);
        return json_decode($result, true);
    }

    /**
     * @brief  获取用户信息
     * @param $accessToken string 通过授权code获取的access_token
     * @param $openId      string 用户openid
     * @return bool|array
     * @author zclee
     */
    public static function getUserInfo($accessToken, $openId) {
        // 请求接口获取用户信息
        $apiUrl      = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $accessToken . '&openid=' . $openId . '&lang=zh_CN';
        $curlResult  = CURL::http($apiUrl);
        $resultArray = json_decode($curlResult, true);
        if ($resultArray['errcode']) {
            return false;
        }
        return $resultArray;
    }

    /**
     * @brief  获取服务端access_token
     * @return mixed
     * @author zclee
     */
    public static function getAccessToken($appId) {
        $key   = 'wx_access_token:' . $appId;
        $token = cache($key);
        if ($token) {
            return $token;
        }
        $appSecret = static::secret($appId);
        $apiUrl    = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appId . '&secret=' . $appSecret;
        $result    = json_decode(CURL::http($apiUrl), true);
        if (isset($result['errcode']) && $result['errcode']) {
            return false;
        }
        cache($key, $result['access_token'], 3600);
        return $result['access_token'];
    }
}