<?php
// @header("Expires: -1");
// @header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
// @header("Pragma: no-cache");
// //框架初始化
// define('SECPATH',dirname(strtr(__FILE__,'\\','/'))."/");//绝对路径
// require SECPATH.'../config.php';	//框架初始化配置
// require SECPATH.'../iPHP.php';		//框架文件
// error_reporting(E_ALL ^ E_NOTICE);

// seccode::run();

class iSeccode {
    public static $im     = null;
    public static $code   = null;
    public static $color  = null;
    public static $config = array (
        'width'      => 80,
        'height'     => 30,
        'angle'      => '0',//随机倾斜度
        'shadow'     => '0',//阴影
    );
    public static function run(){
        self::$code OR self::$code = self::__mkcode();
        //设定cookie
        iPHP::set_cookie('seccode', authcode(self::$code, 'ENCODE'));
        self::__image();
    }
    private static function __image(){
        if(function_exists('imagecreate') && function_exists('imagecolorset') && function_exists('imagecopyresized') &&
                function_exists('imagecolorallocate') && function_exists('imagechar') && function_exists('imagecolorsforindex') &&
                function_exists('imageline') && function_exists('imagecreatefromstring') && (function_exists('imagegif') || function_exists('imagepng') || function_exists('imagejpeg'))) {

            $bgcontent = self::__background();

            self::$im = imagecreatefromstring($bgcontent);
            self::__adulterate();
            self::__giffont();
            if(function_exists('imagepng')) {
                header('Content-type: image/png');
                imagepng(self::$im);
            } else {
                header('Content-type: image/jpeg');
                imagejpeg(self::$im, '', 100);
            }
            imagedestroy(self::$im);

        } else {

            $numbers = array(
                    'B' => array('00','fc','66','66','66','7c','66','66','fc','00'),
                    'C' => array('00','38','64','c0','c0','c0','c4','64','3c','00'),
                    'E' => array('00','fe','62','62','68','78','6a','62','fe','00'),
                    'F' => array('00','f8','60','60','68','78','6a','62','fe','00'),
                    'G' => array('00','78','cc','cc','de','c0','c4','c4','7c','00'),
                    'H' => array('00','e7','66','66','66','7e','66','66','e7','00'),
                    'J' => array('00','f8','cc','cc','cc','0c','0c','0c','7f','00'),
                    'K' => array('00','f3','66','66','7c','78','6c','66','f7','00'),
                    'M' => array('00','f7','63','6b','6b','77','77','77','e3','00'),
                    'P' => array('00','f8','60','60','7c','66','66','66','fc','00'),
                    'Q' => array('00','78','cc','cc','cc','cc','cc','cc','78','00'),
                    'R' => array('00','f3','66','6c','7c','66','66','66','fc','00'),
                    'T' => array('00','78','30','30','30','30','b4','b4','fc','00'),
                    'V' => array('00','1c','1c','36','36','36','63','63','f7','00'),
                    'W' => array('00','36','36','36','77','7f','6b','63','f7','00'),
                    'X' => array('00','f7','66','3c','18','18','3c','66','ef','00'),
                    'Y' => array('00','7e','18','18','18','3c','24','66','ef','00'),
                    '2' => array('fc','c0','60','30','18','0c','cc','cc','78','00'),
                    '3' => array('78','8c','0c','0c','38','0c','0c','8c','78','00'),
                    '4' => array('00','3e','0c','fe','4c','6c','2c','3c','1c','1c'),
                    '6' => array('78','cc','cc','cc','ec','d8','c0','60','3c','00'),
                    '7' => array('30','30','38','18','18','18','1c','8c','fc','00'),
                    '8' => array('78','cc','cc','cc','78','cc','cc','cc','78','00'),
                    '9' => array('f0','18','0c','6c','dc','cc','cc','cc','78','00')
            );

            foreach($numbers as $i => $number) {
                for($j = 0; $j < 6; $j++) {
                    $a1 = substr('012', rand(0, 2), 1).substr('012345', rand(0, 5), 1);
                    $a2 = substr('012345', rand(0, 5), 1).substr('0123', rand(0, 3), 1);
                    rand(0, 1) == 1 ? array_push($numbers[$i], $a1) : array_unshift($numbers[$i], $a1);
                    rand(0, 1) == 0 ? array_push($numbers[$i], $a1) : array_unshift($numbers[$i], $a2);
                }
            }

            $bitmap = array();
            for($i = 0; $i < 20; $i++) {
                for($j = 0; $j < 4; $j++) {
                    $n = substr(self::$code, $j, 1);
                    $bytes = $numbers[$n][$i];
                    $a = rand(0, 14);
                    array_push($bitmap, $bytes);
                }
            }

            for($i = 0; $i < 8; $i++) {
                $a = substr('012345', rand(0, 2), 1) . substr('012345', rand(0, 5), 1);
                array_unshift($bitmap, $a);
                array_push($bitmap, $a);
            }

            $image = pack('H*', '424d9e000000000000003e000000280000002000000018000000010001000000'.
                    '0000600000000000000000000000000000000000000000000000FFFFFF00'.implode('', $bitmap));

            header('Content-Type: image/bmp');
            echo $image;
        }
    }
    //生成随机
    private static function __mkcode() {
        $seccode      = random(6, 1);
        $s            = sprintf('%04s', base_convert($seccode, 10, 24));
        $seccode      = '';
        $seccodeunits = 'BCEFGHJKMPQRTVWXY2346789';
        for($i = 0; $i < 4; $i++) {
            $unit = ord($s{$i});
            $seccode .= ($unit >= 0x30 && $unit <= 0x39) ? $seccodeunits[$unit - 0x30] : $seccodeunits[$unit - 0x57];
        }
        return $seccode;
    }

    //背景
    private static function __background() {
        $im = imagecreatetruecolor(self::$config['width'], self::$config['height']);
        $backgroundcolor = imagecolorallocate($im, 255, 255, 255);

        for($i = 0;$i < 3;$i++) {
            $start[$i]       = rand(200, 255);
            $end[$i]         = rand(100, 245);
            $step[$i]        = ($end[$i] - $start[$i]) / self::$config['width'];
            self::$color[$i] = $start[$i];
        }
        //$color = imagecolorallocate($im, 235, 235, 235);
        for($i = 0;$i < self::$config['width'];$i++) {
            $color = imagecolorallocate($im, self::$color[0], self::$color[1], self::$color[2]);
            imageline($im, $i, 0, $i-$angle, self::$config['height'], $color);
            self::$color[0] += $step[0];
            self::$color[1] += $step[1];
            self::$color[2] += $step[2];
        }
        self::$color[0] -= 20;
        self::$color[1] -= 20;
        self::$color[2] -= 20;

        self::__obclean();
        if(function_exists('imagepng')) {
            imagepng($im);
        } else {
            imagejpeg($im, '', 100);
        }
        imagedestroy($im);
        $bgcontent = ob_get_contents();
        self::__obclean();

        return $bgcontent;
    }

    private static function __adulterate() {
        $linenums = rand(4, 8);
        for($i=0; $i <= $linenums; $i++) {
            $color = imagecolorallocate(self::$im, self::$color[0], self::$color[1], self::$color[2]);
            $x  = rand(0, self::$config['width']-10);
            $y  = 0;
            $x2 = rand(0,self::$config['width']);
            $y2 = self::$config['height'];

            if($i%2) {
                imagearc(self::$im, $x, $y, $x2,$y2,rand(0, 360), rand(0, 360), $color);
            } else {
                imageline(self::$im, $x, $y,$x2,$y2, $color);
                imageline(self::$im, $x+1, $y,$x2+1,$y2, $color);
            }
        }
    }

    private static function __giffont() {

        $seccodedir = array();
        if(function_exists('imagecreatefromgif')) {
            $seccoderoot = iPHP_CORE.'/seccode/';
            $dirs = opendir($seccoderoot);
            while($dir = readdir($dirs)) {
                if($dir != '.' && $dir != '..' && file_exists($seccoderoot.$dir.'/9.gif')) {
                    $seccodedir[] = $dir;
                }
            }
        }
        $widthtotal = 0;
        for($i = 0; $i <= 3; $i++) {
            $imcodefile = $seccodedir ? $seccoderoot.$seccodedir[array_rand($seccodedir)].'/'.strtolower(self::$code[$i]).'.gif' : '';
            if(!empty($imcodefile) && file_exists($imcodefile)) {
                $font[$i]['file'] = $imcodefile;
                $font[$i]['data'] = getimagesize($imcodefile);
                $font[$i]['width'] = $font[$i]['data'][0] + rand(0, 6) - 4;
                $font[$i]['height'] = $font[$i]['data'][1] + rand(0, 6) - 4;
                $font[$i]['width'] += rand(0, self::$config['width'] / 5 - $font[$i]['width']);
                $widthtotal += $font[$i]['width'];
            } else {
                $font[$i]['file'] = '';
                $font[$i]['width'] = 8 + rand(0, self::$config['width'] / 5 - 5);
                $widthtotal += $font[$i]['width'];
            }
        }
        $x = rand(1, self::$config['width'] - $widthtotal);
        for($i = 0; $i <= 3; $i++) {
            if($font[$i]['file']) {
                $imcode = imagecreatefromgif($font[$i]['file']);
                $y = rand(0, self::$config['height'] - $font[$i]['height']);
                if(self::$config['shadow']) {
                    $imcodeshadow = $imcode;
                    imagecolorset($imcodeshadow, 0 , 255 - self::$color[0], 255 - self::$color[1], 255 - self::$color[2]);
                    imagecopyresized(self::$im, $imcodeshadow, $x + 1, $y + 1, 0, 0, $font[$i]['width'], $font[$i]['height'], $font[$i]['data'][0], $font[$i]['data'][1]);
                }
                imagecolorset($imcode, 0 , self::$color[0], self::$color[1], self::$color[2]);
                imagecopyresized(self::$im, $imcode, $x, $y, 0, 0, $font[$i]['width'], $font[$i]['height'], $font[$i]['data'][0], $font[$i]['data'][1]);
            } else {
                $y = rand(0, self::$config['height'] - 20);
                if(self::$config['shadow']) {
                    $text_shadowcolor = imagecolorallocate(self::$im, 255 - self::$color[0], 255 - self::$color[1], 255 - self::$color[2]);
                    imagechar(self::$im, 5, $x + 1, $y + 1, self::$code[$i], $text_shadowcolor);
                }
                $text_color = imagecolorallocate(self::$im, self::$color[0], self::$color[1], self::$color[2]);
                imagechar(self::$im, 5, $x, $y, self::$code[$i], $text_color);
            }
            $x += $font[$i]['width'];
        }
    }
    //ob
    private static function __obclean() {
        ob_end_clean();
        function_exists('ob_gzhandler')?ob_start('ob_gzhandler'):ob_start();
    }
}
