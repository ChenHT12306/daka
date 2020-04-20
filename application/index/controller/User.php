<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
class User extends Controller
{
    private $appid = "wx0957d380884b4141";
    private $appsecret="5ddbb731ddb99385d1eaa7753b20db39";


    //获取用户基本信息（包括UnionID机制）
    public function simpleGetUserInfo($openId){
        $WxApi = new WxApi();
        $access_token = $WxApi->get_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openId.'&lang=zh_CN';
        $data = http_getRequest($url);
        $data = json_decode($data,1);
        return $data;
    }

    //    获取用户openid
    public function getBaseInfo(){
        $redirect_uri=urlencode("http://wx2.cht666.cn/Index/User/getUserOpenId");
        $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect";
        $this->redirect($url);
    }

    public function getUserOpenId(){
        $code=$_GET['code'];
        $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->appid."&secret=".$this->appsecret."&code=".$code."&grant_type=authorization_code";
        $res=http_request($url);
        $res = json_decode($res,1);

        //拉取用户信息(需scope为 snsapi_userinfo)
        if(!empty($res['access_token']) && !empty($res['openid'])){
            $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$res['access_token'].'&openid='.$res['openid'].'&lang=zh_CN';
            $data = http_getRequest($url);
            return $data;
        }

    }

    public function getBaseInfoPro(){
        $redirect_uri=urlencode("http://wx2.cht666.cn/Index/User/getUserOpenIdPro");
        $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect";
        $this->redirect($url);
    }

    public function getUserOpenIdPro(){
        $code=$_GET['code'];
        $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->appid."&secret=".$this->appsecret."&code=".$code."&grant_type=authorization_code";
        $res=http_request($url);
        $res = json_decode($res,1);

        //拉取用户信息(需scope为 snsapi_userinfo)
        if(!empty($res['access_token']) && !empty($res['openid'])){
            $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$res['access_token'].'&openid='.$res['openid'].'&lang=zh_CN';
            $data = http_getRequest($url);
//            //显示获得的数据
            $data = json_decode($data,1);

            $WxApi = new WxApi();
            $access_token = $WxApi->get_access_token();

            $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token;
            //scene_id放的是uid


            $qrcode = '{"expire_seconds": 1800, "action_name": "QR_STR_SCENE", "action_info": {"scene": {"scene_str": "'.$data['openid'] .'"}}}';

            $result = http_request($url, $qrcode);

            $result = json_decode($result, true);


            //通过ticket换取二维码
            $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($result['ticket']);

            $this->redirect($url);
        }
    }

    //创建临时二维码
    function getTemporaryQrcode($openid){
        header("Content-Type:text/html;charset=utf8");
        $WxApi = new WxApi();
        $access_token = $WxApi->get_access_token();

        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token;
//        $qrcode = '{"expire_seconds": 1800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.time().rand(0000,9999).'}}}';
        $qrcode = '{"expire_seconds": 1800, "action_name": "QR_STR_SCENE", "action_info": {"scene": {"scene_str": "'.$openid .'"}}}';
        $result = http_request($url, $qrcode);
        $result = json_decode($result, true);

        //通过ticket换取二维码
        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($result['ticket']);

        $img = file_get_contents($url);
        //获取图片的后最
        $imageSrc = './static/temp/'.$openid.'.png';
        $res =  file_put_contents($imageSrc,$img);
        return $imageSrc;
//        $this->redirect($url);

    }



    //记录日志
    public function log($log){
        //时间
        $date=date('Y_m_d',time());
        $time=date('Y-m-d H:i:s',time());
        $url='./wx_media/'.$date.'media.txt';

        $fp = fopen($url,"a");//打开文件资源通道 不存在则自动创建

        fwrite($fp,var_export($log,true)."\r\n/***************************************".$time."****************************************/\r\n");//写入文件

        fclose($fp);//关闭资源通道
    }



}
