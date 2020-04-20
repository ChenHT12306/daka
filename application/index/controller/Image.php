<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
class Image extends Controller
{
    public function Image($openId){
        $time = time();
        $Hour = date('H',$time);
//        if($Hour>=4 && $Hour<=12){   //用早晨图
            $dstPath = './static/daka/morning.jpg';
//        }else{
//            $dstPath = './static/daka/night.jpg';
//        }


        //二维码
        $User = new User();
//        $srcPath = './static/images/qrcode.png';
        $srcPath = $User->getTemporaryQrcode($openId);
        $width = 480; // 裁剪后的宽度
        $height = 480;// 裁剪后的高度

        // 裁剪后的图片存放目录
        $target = './static/resize/'.time().rand(0000,9999).'.jpg';

        // 裁剪后保存到目标文件夹
        if ($this->image_center_crop($dstPath, $width, $height, $target)) {
        //    echo "<img src='$target'>";die;
            //1.添加二维码到图片
            $tmpfile = tempnam(sys_get_temp_dir(), 'dedemao');
            $dstFile = $this->picMerge($target,$srcPath,165,225,50,50,100,$openId,$tmpfile);

        //2.添加文字到图片
            $font = './static/fonts/hei.ttf';        //字体
            $fontSize = 34;
            $newPic = time().rand(0000,9999).'.jpg';
            $this->addFontToPic($dstFile,$font,$fontSize,'./static/userSends/'.$newPic);

            unlink($dstFile);
            unlink($tmpfile);
            unlink($target);
            return './static/userSends/'.$newPic;
        }
    }
    //生成圆形图
    public function circular_img($imgurl,$dest_path){
        $src = imagecreatefromstring(file_get_contents($imgurl)); //获取网络资源文件
        $wh= getimagesize($imgurl);
        $w=$wh[0];
        $h=$wh[1];
        $w=min($w,$h);
        $h= $w;

        $newpic = imagecreatetruecolor($w,$h);

        // 启用混色模式
        imagealphablending($newpic,false); //设定图像的混色模式

        //imagealphablending() 允许在真彩色图像上使用两种不同的绘画模式。
        // 在混色（blending）模式下，alpha 通道色彩成分提供给所有的绘画函数，例如 imagesetpixel() 决定底层的颜色应在何种程度上被允许照射透过。作为结果，GD 自动将该点现有的颜色和画笔颜色混合，并将结果储存在图像中。结果的像素是不透明的。
        // 在非混色模式下，画笔颜色连同其 alpha 通道信息一起被拷贝，替换掉目标像素。混色模式在画调色板图像时不可用。
        // 如果 blendmode 为 TRUE，则启用混色模式，否则关闭。成功时返回 TRUE， 或者在失败时返回 FALSE。

        $transparent = imagecolorallocatealpha($newpic, 255, 255, 255, 127);//边缘透明

        //imagecolorallocatealpha（resource $image , int $red , int $green , int $blue, int $alpha ）
        // $image 图像资源，通过创造的图像功能，如，一返回imagecreatetruecolor（）。
        // $red 红色分量的价值。
        // $green 价值的绿色成分。
        // $blue 蓝色成分的价值。
        // $alpha 一个介于0和127的价值。 0表示完全不透明，而127表示完全透明。

        $r=$w/2;
        for($x=0;$x<$w;$x++)
            for($y=0;$y<$h;$y++){
                $c = imagecolorat($src,$x,$y);
                $_x = $x - $w/2;
                $_y = $y - $h/2;
                if((($_x*$_x) + ($_y*$_y)) < ($r*$r)){
                    imagesetpixel($newpic,$x,$y,$c);
                }else{
                    imagesetpixel($newpic,$x,$y,$transparent);
                    //imagesetpixel() 在 image 图像中用 color 颜色在 x，y 坐标（图像左上角为 0，0）上画一个点。
                }
            }

        //imagesavealpha() 设置标记以在保存 PNG 图像时保存完整的 alpha 通道信息（与单一透明色相反）
        imagesavealpha($newpic, true);
        // header('Content-Type: image/png');
        imagepng($newpic, $dest_path);
        imagedestroy($newpic);
        imagedestroy($src);

        // unlink() 函数删除文件。
        // 若成功，则返回 true，失败则返回 false。
        // unlink($url);

        return $dest_path;
    }


    /**
     * 居中裁剪图片
     * @param string $source [原图路径]
     * @param int $width [设置宽度]
     * @param int $height [设置高度]
     * @param string $target [目标路径]
     * @return bool [裁剪结果]
     */
    public function image_center_crop($source, $width, $height, $target)
    {
        if (!file_exists($source)) return false;
        /* 根据类型载入图像 */
        switch (exif_imagetype($source)) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($source);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($source);
                break;
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($source);
                break;
        }
        if (!isset($image)) return false;
        /* 获取图像尺寸信息 */
        $target_w = $width;
        $target_h = $height;
        $source_w = imagesx($image);
        $source_h = imagesy($image);
        /* 计算裁剪宽度和高度 */
        $judge = (($source_w / $source_h) > ($target_w / $target_h));
        $resize_w = $judge ? ($source_w * $target_h) / $source_h : $target_w;
        $resize_h = !$judge ? ($source_h * $target_w) / $source_w : $target_h;
        $start_x = $judge ? ($resize_w - $target_w) / 2 : 0;
        $start_y = !$judge ? ($resize_h - $target_h) / 2 : 0;
        /* 绘制居中缩放图像 */
        $resize_img = imagecreatetruecolor($resize_w, $resize_h);
        imagecopyresampled($resize_img, $image, 0, 0, 0, 0, $resize_w, $resize_h, $source_w, $source_h);
        $target_img = imagecreatetruecolor($target_w, $target_h);
        imagecopy($target_img, $resize_img, 0, 0, $start_x, $start_y, $resize_w, $resize_h);
        /* 将图片保存至文件 */
        if (!file_exists(dirname($target))) mkdir(dirname($target), 0777, true);
        switch (exif_imagetype($source)) {
            case IMAGETYPE_JPEG:
                imagejpeg($target_img, $target);
                break;
            case IMAGETYPE_PNG:
                imagepng($target_img, $target);
                break;
            case IMAGETYPE_GIF:
                imagegif($target_img, $target);
                break;
        }
        return boolval(file_exists($target));
    }


    /**
     * 图片合并
     * 将源图片覆盖到目标图片上
     * @param string $dstPath 目标图片路径
     * @param string $srcPath 源图片路径
     * @param int $dstX 源图片覆盖到目标的X轴坐标
     * @param int $dstY 源图片覆盖到目标的Y轴坐标
     * @param int $srcX
     * @param int $srcY
     * @param int $pct 透明度
     * @param string $filename 输出的文件名，为空则直接在浏览器上输出显示
     * @return string $filename 合并后的文件名
     */
    public function picMerge($dstPath,$srcPath,$dstX=0,$dstY=0,$srcX=0,$srcY=0,$pct=100,$openId,$filename='')
    {
        //创建图片的实例
        $dst = imagecreatefromstring(file_get_contents($dstPath));
        $src = imagecreatefromstring(file_get_contents($srcPath));
        //获取水印图片的宽高
//    list($src_w, $src_h) = getimagesize($srcPath);
        //将水印图片复制到目标图片上，最后个参数50是设置透明度，这里实现半透明效果

        //获取用户的头像
        $userHeadImg = DB::name('users')->where('openId',$openId)->find();
        $userHeadImg = $userHeadImg['head_img'];

//        $userHeadImg = './static/images/head.jpg';
        $filename = time().'.png';
//        $headPath = $this->circular_img('./static/images/head.jpg','./static/resize/'.$filename);
        $headPath = $this->circular_img($userHeadImg,'./static/resize/'.$filename);
        $head = imagecreatefromstring(file_get_contents($headPath));
        //输出图片
        $image_head = imagecreatefrompng($headPath);
        imagecopyresampled($dst, $head, 20, 20, 0, 0,70,70,imagesx($image_head), imagesy($image_head));

        //二维码
        //输出图片
        list($dst_w, $dst_h, $dst_type) = getimagesize($srcPath);
        switch ($dst_type) {
            case 1://GIF
                $image_logo = imagecreatefromgif($srcPath);
                break;
            case 2://JPG
                $image_logo = imagecreatefromjpeg($srcPath);
                break;
            case 3://PNG
                $image_logo = imagecreatefrompng($srcPath);
                break;
            default:
                break;
        }
        imagecopyresampled($dst, $src, 370, 370, 0, 0,100,100,imagesx($image_logo), imagesy($image_logo));
//    imagecopymerge($dst, $src, 165, 225, 0, 0, $src_w, $src_h, 100);
        //如果水印图片本身带透明色，则使用imagecopy方法
        //imagecopy($dst, $src, 10, 10, 0, 0, $src_w, $src_h);
        //输出图片
        list($dst_w, $dst_h, $dst_type) = getimagesize($dstPath);
        switch ($dst_type) {
            case 1://GIF
                if(!$filename){
                    header('Content-Type: image/gif');
                    imagegif($dst);
                }else{
                    imagegif($dst,$filename);
                }
                break;
            case 2://JPG
                if(!$filename){
                    header('Content-Type: image/jpeg');
                    imagejpeg($dst);
                }else{
                    imagejpeg($dst,$filename);
                }
                break;
            case 3://PNG
                if(!$filename){
                    header('Content-Type: image/png');
                    imagepng($dst);
                }else{
                    imagepng($dst,$filename);
                }
                break;
            default:
                break;
        }
        imagedestroy($dst);
        imagedestroy($src);
        //删除圆形图
        unlink($headPath);
        return $filename;
    }

    /**
     * 添加文字到图片上
     * @param $dstPath 目标图片
     * @param $fontPath 字体路径
     * @param $fontSize 字体大小
     * @param $text 文字内容
     * @param $dstY 文字Y坐标值
     * @param string $filename 输出文件名，为空则在浏览器上直接输出显示
     * @return string 返回文件名
     */
    public function addFontToPic($dstPath,$fontPath,$fontSize,$filename='')
    {
        //创建图片的实例
        $dst = imagecreatefromstring(file_get_contents($dstPath));
        //打上文字
        $fontColor = imagecolorallocate($dst, 255, 255, 255);//字体颜色
        $width = imagesx ( $dst );
        $height = imagesy ( $dst );
//    $fontBox = imagettfbbox($fontSize, 0, $fontPath, $text);//文字水平居中实质
//    imagettftext ( $dst, $fontSize, 0, ceil(($width - $fontBox[2]) / 2), $dstY, $fontColor, $fontPath, $text);
        date_default_timezone_set('PRC');
        imagettftext ( $dst, 27, 0, 420, 50, $fontColor, $fontPath, date("d",time()));
        imagettftext ( $dst, 10, 0, 412, 75, $fontColor, $fontPath, date("Y.m",time()));
        imagettftext ( $dst, 12, 0, 20, 130, $fontColor, $fontPath, '连续早起');
        imagettftext ( $dst, $fontSize, 0, 20, 180, $fontColor, $fontPath, 7);
        imagettftext ( $dst, 12, 0, 50, 180, $fontColor, $fontPath, '天');
        imagettftext ( $dst, 12, 0, 20, 220, $fontColor, $fontPath, '今日早起');
        imagettftext ( $dst, $fontSize, 0, 20, 270, $fontColor, $fontPath, date('H:i',time()));
        imagettftext ( $dst, 12, 0, 20, 300, $fontColor, $fontPath, '————————');
        imagettftext ( $dst, 12, 0, 20, 320, $fontColor, $fontPath, '11131315人正在参与');
        imagettftext ( $dst, 12, 0, 20, 340, $fontColor, $fontPath, '比58%的人起的早');

        imagettftext ( $dst, 12, 0, 140, 440, $fontColor, $fontPath, '眼泪不是答案，拼搏才是选择');
        imagettftext ( $dst, 12, 0, 220, 460, $fontColor, $fontPath, '扫码和我互道早安');
        //输出图片
        list($dst_w, $dst_h, $dst_type) = getimagesize($dstPath);
        switch ($dst_type) {
            case 1://GIF
                if(!$filename){
                    header('Content-Type: image/gif');
                    imagegif($dst);
                }else{
                    imagegif($dst,$filename);
                }
                break;
            case 2://JPG
                if(!$filename){
                    header('Content-Type: image/jpeg');
                    imagejpeg($dst);
                }else{
                    imagejpeg($dst,$filename);
                }
                break;
            case 3://PNG
                if(!$filename){
                    header('Content-Type: image/png');
                    imagepng($dst);
                }else{
                    imagepng($dst,$filename);
                }
                break;
            default:
                break;
        }
        imagedestroy($dst);
        return $filename;
    }
}
