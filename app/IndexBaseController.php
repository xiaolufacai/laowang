<?php
declare (strict_types=1);

namespace app;

use think\App;
use think\exception\ValidateException;
use think\facade\Session;
use think\Validate;

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
     *  包名
     *
     * @var
     */
    public $appId;

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
     * 构造方法
     * @access public
     * @param App $app 应用对象
     */
    public function __construct(App $app) {
        $this->app     = $app;
        $this->request = $this->app->request;
        $this->appId       = $this->request['appId'] ?? '';
        $this->device      = $this->request['deviceNum'] ?? '';
        $this->channel     = $this->request['channel'] ?? '';
        $this->versionCode = $this->request['versionCode'] ?? '';
        $this->versionName = $this->request['versionName'] ?? '';
    }
}
