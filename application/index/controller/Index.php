<?php
namespace app\index\controller;

use think\Controller;

class Index extends Controller
{
    public function index()
    {
        //用于绑定接收事件服务器
//        $Auth = new Auth();
//        var_dump($Auth->valid());

        //相应事件
        $WxApi = new WxApi();
        $WxApi->responseMsg();
//        $WxApi->receiveImage($postObj);
//        $WxApi->uploadTmp();
    }


}
