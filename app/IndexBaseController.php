<?php
declare (strict_types=1);

namespace app;

use think\App;
use think\exception\ValidateException;
use think\facade\Session;
use think\Validate;
use \app\common\model\App as AppModel;

/**
 * 控制器基础类
 */
abstract class IndexBaseController {
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     *  渠道
     *
     * @var
     */
    public $channel;

    /**
     *  包名ID
     *
     * @var
     */
    public $appId;

    /**
     *  包ID所在表的ID
     *
     * @var
     */
    public $id;

    /**
     *  设备号
     *
     * @var
     */
    public $device;

    /**
     *  版本code
     *
     * @var
     */
    public $versionCode;

    /**
     *  版本名[1.0.0]
     *
     * @var
     */
    public $versionName;

    /**
     *  微信 APP ID
     * @var mixed
     */
    public mixed $wechatAppId;

    /**
     * 构造方法
     * @access public
     * @param App $app 应用对象
     */
    public function __construct(App $app, Request $request) {
        $this->app     = $app;
        $this->request = $this->app->request;

        $this->device      = $request->header('deviceNum');
        $this->channel     = strtolower($request->header('channel'));
        $this->appId       = $request->header('appId');
        $this->versionCode = $request->header('versionCode');
        $this->versionName = $request->header('versionName');
        // 查询包是否存在
        $app = AppModel::where('app_id', $this->appId)->where(['status' => AppModel::STATUS_NORMAL])->find();
        if (empty($app)) {
            return json(['code' => -1, 'data' => [], 'message' => '包名不存在']);
        }
        $this->id          = $app['id'];
        $this->wechatAppId = $app['wx_id'];
//        $this->device      = $this->request['deviceNum'] ?? '';
//        $this->channel     = $this->request['channel'] ?? '';
//        $this->versionCode = $this->request['versionCode'] ?? '';
//        $this->versionName = $this->request['versionName'] ?? '';
    }
}
