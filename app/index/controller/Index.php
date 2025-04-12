<?php
namespace app\index\controller;


use app\IndexBaseController;
use think\facade\Session;
use think\facade\View;
use think\Request;

class Index
{

    public function index(Request $request)
    {
        var_dump($request->user);
    }
}