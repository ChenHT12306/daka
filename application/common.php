<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function dd($data){
    echo '<pre>';
    print_r($data);
    die;
}


function http_request($url,$data=array()){
    //初始化
    $ch=curl_init();
    //设置
    //请求的URL地址
    curl_setopt($ch,CURLOPT_URL,$url);
    //获取的信息以文件流的形式返回
    curl_setopt($ch,CURLOPT_RETURNTRANSFER ,true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    if(!empty($data)){
        //设置POST
        curl_setopt($ch,CURLOPT_POST,true);
        //设置POST数据
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
    }
    //执行
    $con=curl_exec($ch);
    //关闭
    curl_close($ch);
    return $con;
}

function http_getRequest($url){
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 0);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //执行命令
    $data = curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    //显示获得的数据
//            $data = json_decode($data,1);
    return $data;
}

