<?php
namespace app\index\controller;

use think\Controller;

class WxApi extends Controller
{
    private $appid="wx0957d380884b4141";
    private $appsecret="5ddbb731ddb99385d1eaa7753b20db39";

    //    生成签名
    public function signature(){
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
        $path=__DIR__.'/cache/ticket.txt';
        $time=time();
        if(is_file($path) && filectime($path)+7100<$time){
//        获取access_token
            $access=$this->get_access_token();
            $url="https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$access&type=jsapi";
            $ticket=$this->http_request($url);
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
//    获取access_token
    public function get_access_token(){
//        因为有次数限制所以要做成缓存
        $path=$_SERVER['DOCUMENT_ROOT'].'/cache/access.txt';
        $time=time();

        if(is_file($path) && filectime($path)+7100<$time){
            $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appid&secret=$this->appsecret";

            $access=http_request($url);

            $accarr=json_decode($access,1);

            $accstr=$accarr['access_token'];

            file_put_contents($path,$accstr);
            return $accstr;
        }else{
            return file_get_contents($path);
        }
    }


    //响应消息      responseMsg()
    public function responseMsg(){
        //post原生数据  用户的信息  类型（XML）
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
//判断来源数据不为空
        if(empty($postStr)){
            exit;
        }
//把XML类型转换成PHP可以用的对象类型
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

        $this->fromUsername = $postObj->FromUserName;  //用户
        $this->toUsername = $postObj->ToUserName;      //我们
        $this->msgtype = $postObj->MsgType;            //信息类型
        $this->content = $postObj->Content;            //用户的信息
        $this->time = time();                          // 时间戳
        $this->log($this->msgtype);
        if($this->msgtype == 'text'){
//            处理文本类型
            $this->receiveText();

        }else if($this->msgtype == 'image'){
//            处理图片类型
            $this->replayImage($postObj);

        }else if($this->msgtype=='event'){
//            处理事件类型
            $this->receiveEvent($postObj);
        }

    }
//==========处理消息================================================
//    类的属性
    protected $fromUsername;
    protected $toUsername;
    protected $msgtype;
    protected $content;
    protected $time;
    //处理文本类型
    public function receiveText(){
        if($this->content == '点歌'){
            $txt = '暂时没有歌曲';
            $this->replayText($txt);
        }else if($this->content == '新闻'){
            $data=$this->mysql('wx_news');

//            $data = array(
//                array(
//                    'title'=>'新闻标题555555',
//                    'description'=>'新闻描述55555',
//                    'picurl'=>'http://wx.wangliang.wang/images/img5.jpg',
//                    'url'=>'http://www.baidu.com',
//                ),
//            );
//            回复新闻
            $this->replayNews($data);
        }else if($this->content=='天王盖地虎'){
            $txt='宝塔镇河妖';
            $this->replayText($txt);
        }else if($this->content=='图片'){
            $mediaid="jdID8dx6mhfP4LHo8SjJsRrPhoa4NO4eEykx8p9lo6zbFn5h9bToq-PruxeEiwwm";
            $this->replayImage($mediaid);
        }else if($this->content=='视频'){
//            回复视频
            $media="CdItZEjWi1P5aqsqZ5YxJi17j2C9nIjQbT1vspEI8Ll0dkm948ATY_FPajCinaPk";
            $title="响应式布局";
            $description="只要做一套等于多套";
            $this->replayVideo($media,$title,$description);
        }else if($this->content=='分享'){
            $txt="http://wx.cht666.cn/jssdk.php";
            $this->replayText($txt);
        }else{
            $txt = $this->content;
            $this->replayText($txt);
        }
    }
    //处理图片类型
    public function receiveImage($postObj){
//        来什么我回什么
        $mediaid = '5Ri0DoE38jLoKeJdQmK4zIPYDSZTBuGaJrxxdKx0NZO2NPfv6mmL6JjBfbucLKL_';
//        $mediaid = $postObj->mediaid;
//        回复图片消息
        $this->replayImage($postObj);
        $this->messageToUserName($mediaid);
    }




    //客服消息
    private function messageToUserName($media_id)//content 就是回复的消息，$fromUsername就是openid
    {
        //这里要获取token
        $ACC_TOKEN = $this->get_access_token();
                $data = '{
        "touser":"'.$this->fromUsername.'",
        "msgtype":"image",
        "image":
        {
        "media_id":"'.$media_id.'"
        }
        }';

        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$ACC_TOKEN;

        $result = http_request($url,$data);
        $final = json_decode($result);
        return $final;
    }

    //上传临时素材---图片
    public function uploadTmp(){
            $type = "image";
            $filepath = $_SERVER['DOCUMENT_ROOT']."/static/images/morning.jpg";
        if (class_exists('\CURLFile')) {
            $filedata = array('media' => new \CURLFile(realpath($filepath)));
        } else {
            $filedata = array('media' => '@' . realpath($filepath));
        }

//        var_dump($filedata);die;
            $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=".$this->get_access_token()."&type=".$type;
            $result = http_request($url, $filedata);
//            var_dump($result);die;
            $p = json_decode($result);
        $this->log($p->media_id);
        echo "media_id:".$p->media_id;

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
//    处理事件类型
    public function receiveEvent($postObj){
//            关注事件回复欢迎词123123123123
        if($postObj->Event=='subscribe'){
            $txt="嗨，我是你的新朋友——小涛。\n\n";
            $txt.="过去3年里，有超过750W像你一样的小伙伴，每天和我一起“早晚安”打卡，让日子活出仪式感，用健康规律的作息，从容面对生活中的忙忙碌碌；我每天为你准备的独家打卡图，让你秀出与众不同的坚持与积极生活的态度；小确幸是我为你准备的隐藏技能包，希望能给你带来一些微小的快乐和幸福；偶尔你也可以和我说说生活中大事小事，我会一直陪在你身边的。\n\n";
            $txt.="现在打卡向好友问声晚安吧！\n\n";
            $time = time();
            $Hour = date('H',$time);
            if($Hour>=20 && $Hour<=4){
                $txt.='<a href="http://www.baidu.com">晚上打卡</a>';
            }else if($Hour>=4 && $Hour<=12){
                $txt.='<a href="http://www.baidu.com">早上打卡</a>';
            }else{
                $txt.='<a href="http://www.baidu.com">测试打卡</a>';
            }
            $this->replayText($txt);
        }else if($postObj->Event=='unsubscribe'){
            //取消关注
        }else if($postObj->Event=='CLICK'){
            $key=$postObj->EventKey;
            if($key=='MORNING'){   //早晨打卡  4-12点
                $time = time();
                $Hour = date('H',$time);
                $Minute = date('i',$time);
                if($Hour>=4 && $Hour<=12){
                    $this->replayText("早晨打卡成功\n打卡时间为：".$Hour.':'.$Minute);
                    $this->messageToUserName('wrfYVIKv0rlCdckEGkN9TV5fvQUf74-GdTB60MsvRMJnvKeG7WdEMjbfpAdPYSn8');
                }
                //不在特定打卡时间
                $this->replayText("打卡可以帮你记录每天起床睡觉时间，养成良好习惯。\n\n早起打卡时间：4:00-12:00\n\n早睡打卡时间：20:00－4:00");
                $this->messageToUserName('wrfYVIKv0rlCdckEGkN9TV5fvQUf74-GdTB60MsvRMJnvKeG7WdEMjbfpAdPYSn8');
            }else if($key=='NIGHT'){  //晚上打卡  20-4点
                $time = time();
                $Hour = date('H',$time);
                $Minute = date('i',$time);
                if($Hour>=20 && $Hour<=4){
                    $this->replayText("晚上打卡成功\n打卡时间为：".$Hour.':'.$Minute);
                    $this->messageToUserName('5Ri0DoE38jLoKeJdQmK4zIPYDSZTBuGaJrxxdKx0NZO2NPfv6mmL6JjBfbucLKL_');
                }
                $this->replayText("打卡可以帮你记录每天起床睡觉时间，养成良好习惯。\n\n早起打卡时间：4:00-12:00\n\n早睡打卡时间：20:00－4:00");
                $this->messageToUserName('5Ri0DoE38jLoKeJdQmK4zIPYDSZTBuGaJrxxdKx0NZO2NPfv6mmL6JjBfbucLKL_');

            }else if($key=='NEWS'){
                $data=$this->mysql('wx_news');
                $this->replayNews($data);

            }
        }
    }









//=========回复消息=================================================
//    回复文本消息
    public function replayText($txt){

        $xml = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            </xml>";

        printf($xml, $this->fromUsername, $this->toUsername, $this->time, $txt);

    }

//    回复图片消息
    public function replayImage($postObj){
        $xml = "<xml>
                  <ToUserName><![CDATA[%s]]></ToUserName>
                  <FromUserName><![CDATA[%s]]></FromUserName>
                  <CreateTime>%s</CreateTime>
                  <MsgType><![CDATA[image]]></MsgType>
                  <Image>
                    <MediaId><![CDATA[%s]]></MediaId>
                  </Image>
                </xml>";
        printf($xml, $this->fromUsername, $this->toUsername, $this->time, '5Ri0DoE38jLoKeJdQmK4zIPYDSZTBuGaJrxxdKx0NZO2NPfv6mmL6JjBfbucLKL_');
//        $postObj->MediaId
    }

    //回复图文消息
    public function replayNews($data){
        $item = '';
        foreach($data as $v){
            $item .= "<item>
                <Title><![CDATA[".$v['title']."]]></Title>
                <Description><![CDATA[".$v['description']."]]></Description>
                <PicUrl><![CDATA[".'http://wx.cht666.cn/uploads/'.$v['picurl']."]]></PicUrl>
                <Url><![CDATA[".$v['url']."]]></Url>
                </item>";
        }

        $xml = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[news]]></MsgType>
                <ArticleCount>".count($data)."</ArticleCount>
                <Articles>
                ".$item."
                </Articles>
                </xml>";
        printf($xml, $this->fromUsername, $this->toUsername, $this->time);
    }

//恢复视频
    public function replayVideo($media,$title,$description){
        $xml="<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[video]]></MsgType>
                <Video>
                <MediaId><![CDATA[%s]]></MediaId>
                <Title><![CDATA[%s]]></Title>
                <Description><![CDATA[%s]]></Description>
                </Video>
            </xml>";
        printf($xml, $this->fromUsername, $this->toUsername, $this->time,$media,$title,$description);
    }

    public function mysql($table){
        include('cms/include/mysql.class.php');
        $mysql=new mysql('localhost','root','ChenHT12306','wx.cht666.cn','utf8');
        $mysql->sql="SELECT * FROM $table";
        return $mysql->getAll();
    }
}
