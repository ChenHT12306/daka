<?php
namespace app\index\controller;

use think\Controller;

class Signature extends Controller
{
    public $appid="wx0957d380884b4141";
    public $appsecret="5ddbb731ddb99385d1eaa7753b20db39";

//    生成签名
    public function Signature(){
//        排序  做成数组
//        noncestr=Wm3WZYTPz0wzccnW
//        jsapi_ticket=sM4AOVdWfPE4DxkXGEs8VMCPGGVi4C3VM0P37wVUCFvkVAy_90u5h9nbSlYy3-Sl-HhTdfl2fzFy1AOcHKP7qg
//        timestamp=1414587457
//        url=http://mp.weixin.qq.com?params=value
        $noncestr=$this->noncestr();
        $jsapi_ticket=$this->jsapi_ticket();
        $timestamp=time();
        $url=$this->get_url();
        $appid=$this->appid;
        $arr=array(
            'noncestr='.$noncestr,
            'jsapi_ticket='.$jsapi_ticket,
            'timestamp='.$timestamp,
            'url='.$url,
        );
        sort($arr,SORT_STRING);
        $str=implode('&',$arr);
//        最终加密串
        $sign=sha1($str);
        return array(
            'timestamp'=>$timestamp,
            'nonceStr'=>$noncestr,
            'signature'=>$sign,
            'appId'=>$appid,
        );
    }



//    获取当前url
    public function get_url(){
        $url=$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
        return $url;

    }
//    生成随机字符串
    public function noncestr(){
        $str="wewqeasfr23432gq399t923d2j39rt2tu93t";
        $nonc='';
        for($i=0;$i<15;$i++){
            $nonc.=$str[rand(0,strlen($str))];
        }
        return $nonc;
    }
//    获取临时票据
    public function jsapi_ticket(){
        $path='./cache/ticket.txt';
        $time=time();
        if(is_file($path) && filectime($path)+7100<$time){
//        获取access_token
            $access=get_access_token();
            $url="https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$access&type=jsapi";
            $ticket=http_request($url);
            $ticketarr=json_decode($ticket,1);
            $tistring=$ticketarr['ticket'];
//        往文件里面写入字符串
            file_put_contents($path,$tistring);
            return $tistring;
        }else{
//        读文件
            return file_get_contents($path);
        }
    }







}
