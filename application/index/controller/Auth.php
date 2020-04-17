<?php
namespace app\index\controller;

use think\Controller;

class Auth extends Controller
{
    private $token = 'power';
    //验证签名
    public function valid()

    {

        $echoStr = $_GET["echostr"];


        //valid signature , option

        if($this->checkSignature()){

            echo $echoStr;

            exit;

        }

    }
    

	private function checkSignature()

    {

        $signature = $_GET["signature"];

        $timestamp = $_GET["timestamp"];

        $nonce = $_GET["nonce"];


        

        $tmpArr = array($this->token, $timestamp, $nonce);

        sort($tmpArr);

        $tmpStr = implode( $tmpArr );

        $tmpStr = sha1( $tmpStr );


        if( $tmpStr == $signature ){

            return true;

        }else{

            return false;

        }

    }
    
    //记录日志
    public function log($log){

        //时间
        $date=date('Y_m_d',time());
        $time=date('Y-m-d H:i:s',time());
        $url='./wx_callback/'.$date.'callback.txt';

        $fp = fopen($url,"a");//打开文件资源通道 不存在则自动创建

        fwrite($fp,var_export($log,true)."\r\n/***************************************".$time."****************************************/\r\n");//写入文件

        fclose($fp);//关闭资源通道
    }



}
