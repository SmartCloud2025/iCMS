<?php
/**
* iPHP - i PHP Framework
* Copyright (c) 2012 iiiphp.com. All rights reserved.
*
* @author coolmoo <iiiphp@qq.com>
* @site http://www.iiiphp.com
* @licence http://www.iiiphp.com/license
* @version 1.0.1
* @package iPic
* @$Id: iPic.class.php 2290 2013-11-21 03:49:19Z coolmoo $
*/
class iPic {
    protected static $config    = null;
    protected static $watermark = null;

    public static function init($config) {
		self::$config    = $config;
		self::$watermark = iPHP_APP_CONF;
    }
    public static function watermark($pf) {
        if(!self::$config['enable']) return;


        list($width, $height,$imagetype) = @getimagesize($pf);


        if ( $width < self::$config['width'] || $height<self::$config['height'] ) {
            return FALSE;
        }
        $isWaterImage	= FALSE;
        $formatMsg		= "暂不支持该文件格式，请用图片处理软件将图片转换为GIF、JPG、PNG等格式。";
        //读取水印文件
        $waterImgPath	= self::$watermark.'/'.self::$config['img'];

        if(self::$config['img'] && file_exists($waterImgPath)) {
            list($water_w, $water_h,$water_imagetype) = @getimagesize($waterImgPath);
            $water_im	 = self::imagecreate($water_imagetype,$waterImgPath);
            $water_im OR die($formatMsg);
            $isWaterImage = TRUE;
        }else {
            $fontfile	= self::$watermark.'/'.self::$config['font'];
        }

        //读取背景图片
        if($pf && file_exists($pf)) {
        	list($ground_w, $ground_h,$ground_imagetype) = @getimagesize($pf);
            $ground_info = @getimagesize($pf);
			$ground_im	 = self::imagecreate($ground_imagetype,$pf);//取得背景图片的格式
        }else {
            die("需要加水印的图片不存在！");
        }
        //水印位置
        if($isWaterImage){ //图片水印
            $w = $water_w;
            $h = $water_h;
        }else { //文字水印
            if(self::$config['font']){
                $temp = imagettfbbox(self::$config['fontsize'],0,$fontfile,self::$config['text']);//取得使用 TrueType 字体的文本的范围
                $w = $temp[2] - $temp[6];
                $h = $temp[3] - $temp[7];
                unset($temp);
            }else {
                $w = self::$config['fontsize']*cstrlen(self::$config['text']);
                $h = self::$config['fontsize']+5;
            }
        }
        if( ($ground_w<$w) || ($ground_h<$h) ){
            //       echo "需要加水印的图片的长度或宽度比水印".$label."还小，无法生成水印！";
            return;
        }
        switch(self::$config['pos']) {
            case '-1'://自定义
                $posX = $ground_w - $w-self::$config['x'];
                $posY = $ground_h - $h-self::$config['y'];
                break;
            case 1://1为顶端居左
                $posX = 0;
                $posY = 0;
                break;
            case 2://2为顶端居中
                $posX = ($ground_w - $w) / 2;
                $posY = 0;
                break;
            case 3://3为顶端居右
                $posX = $ground_w - $w;
                $posY = 0;
                break;
            case 4://4为中部居左
                $posX = 0;
                $posY = ($ground_h - $h) / 2;
                break;
            case 5://5为中部居中
                $posX = ($ground_w - $w) / 2;
                $posY = ($ground_h - $h) / 2;
                break;
            case 6://6为中部居右
                $posX = $ground_w - $w;
                $posY = ($ground_h - $h) / 2;
                break;
            case 7://7为底端居左
                $posX = 0;
                $posY = $ground_h - $h;
                break;
            case 8://8为底端居中
                $posX = ($ground_w - $w) / 2;
                $posY = $ground_h - $h;
                break;
            case 9://9为底端居右
                $posX = $ground_w - $w;
                $posY = $ground_h - $h;
                break;
            default://随机
                $posX = rand(0,($ground_w - $w));
                $posY = rand($h,($ground_h - $h));
                break;
        }
        $posX = $posX-self::$config['x'];
        $posY = $posY-self::$config['y'];

        //设定图像的混色模式
        imagealphablending($ground_im, true);

        if($isWaterImage) { //图片水印
        	if(strtolower(substr(strrchr($waterImgPath, "."),1))=='png'){
	            imagecopy ($ground_im,$water_im,$posX, $posY, 0,0,$water_w,$water_h);
        	}else{
				imagecopymerge($ground_im, $water_im, $posX, $posY, 0, 0, $water_w,$water_h,self::$config['transparent']);//拷贝水印到目标文件
        	}
        }else{//文字水印
            self::$config['color'] OR self::$config['color']="#FFFFFF";
            if(!empty(self::$config['color']) && (strlen(self::$config['color'])==7) ) {
                $R = hexdec(substr(self::$config['color'],1,2));
                $G = hexdec(substr(self::$config['color'],3,2));
                $B = hexdec(substr(self::$config['color'],5));
                $textcolor = imagecolorallocate($ground_im, $R, $G, $B);
            }else {
                die("水印文字颜色格式不正确！");
            }
            if(self::$config['font']) {
                imagettftext($ground_im,self::$config['fontsize'], 0, $posX, $posY, $textcolor,$fontfile, self::$config['text']);
            }else {
                imagestring ($ground_im, self::$config['fontsize'], $posX, $posY, self::$config['text'],$textcolor);
            }
        }

        //生成水印后的图片
        @unlink($pf);
        self::image($ground_im,$ground_info[2],$pf);
        //释放内存
        unset($water_info);
        isset($water_im) && imagedestroy($water_im);
        unset($ground_info);
    }
    public static function thumbnail($src,$tw="0",$th="0",$scale=true) {
    	if(!self::$config['thumb']['enable']) return;

        $rs	= array();
        $tw	= empty($tw)?self::$config['thumb']['width']:(int)$tw;
        $th	= empty($th)?self::$config['thumb']['height']:(int)$th;

        if ($tw && $th){
            list($width, $height,$type) = getimagesize($src);
            if ( $width < 1 && $height < 1 ) {
                $rs['width']	= $tw;
                $rs['height']   = $th;
                $rs['src'] 		= $src;
                return $rs;
            }

            if ( $width > $tw || $height >$th ) {
	            $rs['src'] = $src.'_'.$tw.'x'.$th.'.jpg';
				if (in_array('Gmagick', get_declared_classes() )){
					$image = new Gmagick();
					$image->readImage($src);
					$im = self::scale(array("tw"  => $tw,"th" => $th,"w"  => $image->getImageWidth(),"h" => $image->getImageHeight()));
					$image->resizeImage($im['w'],$im['h'], null, 1);
					$image->cropImage($tw,$th, 0, 0);
					//$image->thumbnailImage($gm_w,$gm_h);
					$image->writeImage($rs['src']);
					$image->destroy();
				}else{
	                $im = self::scale(array("tw"  => $tw,"th" => $th,"w"  => $width,"h" => $height ),$scale);
	                $ret= self::imagecreate($type,$src);
	                $rs['width']   = $im['w'];
	                $rs['height']  = $im['h'];
	                if ($ret) {
	                    $thumb = imagecreatetruecolor($im['w'], $im['h']);
	                    imagecopyresampled($thumb,$ret, 0, 0, 0, 0, $im['w'], $im['h'], $width, $height);
	                    self::image($thumb,$type,$rs['src']);
	                } else {
	                    $rs['src'] = $src;
	                }
                }
            } else {
                $rs['src'] 		= $src;
                $rs['width']	= $width;
                $rs['height']   = $height;
            }
            return $rs;
        }

    }
    function image($res,$type,$fn) {
    	switch($type){
    		case 1:imagegif($res,$fn);break;
    		case 2:imagejpeg($res,$fn);break;
    		case 3:imagepng($res,$fn);break;
    	}
        imagedestroy($res);
    }
    function imagecreate($type,$src) {
    	switch($type){
    		case 1:$res = imagecreatefromgif($src);break;
    		case 2:$res = imagecreatefromjpeg($src);break;
    		case 3:$res = imagecreatefrompng($src);break;
    	}
        return $res;
    }
	function scale($a) {
		if( $a['w']/$a['h'] > $a['tw']/$a['th']  && $a['w'] >$a['tw'] ){
			$a['h'] = ceil($a['h'] * ($a['tw']/$a['w']));
			$a['w'] = $a['tw'];
		}else if( $a['w']/$a['h'] <= $a['tw']/$a['th'] && $a['h'] >$a['th']){
			$a['w'] = ceil($a['w'] * ($a['th']/$a['h']));
			$a['h'] = $a['th'];
		}
		return $a;
	}
}
