<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
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


    //拉去用户信息登录
    public function userLogin(){
        //用户授权
        $User = new User();
        $User->getBaseInfo();
    }

    public function test(){
        $Image = new Image();
        $fileURL = $Image->Image('osuYV1uQNnerS9EJx6Pnpk9vIYAQ');

        $WxApi = new WxApi();
        $mediaId = $WxApi->uploadTmp($fileURL);
        echo $mediaId;
    }

    //群发接口
    public function sends(){
        $WxApi = new WxApi();
        $WxApi->messageToUsers();
    }



}
