<?php
namespace app\index\controller;

use think\Controller;

class Menu extends Controller
{
    //创建菜单
    public function createMenu()
    {
        $WxApi = new WxApi();
        $access_token = $WxApi->get_access_token();

        $url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$access_token";

        $data='{
              "button":[{
                   "name":"早晚安",
                   "sub_button":[
                    {
                       "type":"click",
                       "name":"早起打卡",
                       "key":"MORNING"
                    },
                    {
                      "type":"click",
                      "name":"晚起打卡",
                      "key":"NIGHT"
                    }
                    ]
               }]
         }';

        $menu=http_request($url,$data);
        //$menu=$wxapi->definedItems();
        var_dump($menu);
    }


}
