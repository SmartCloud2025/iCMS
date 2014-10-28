<?php

/**
 * iPHP - i PHP Framework
 * Copyright (c) 2012 iiiphp.com. All rights reserved.
 *
 * @author coolmoo <iiiphp@qq.com>
 * @site http://www.iiiphp.com
 * @licence http://www.iiiphp.com/license
 * @version 1.0.1
 * @package FileSystem
 * @$Id: iFileSystem.class.php 2412 2014-05-04 09:52:07Z coolmoo $
 *
 * CREATE TABLE `iPHP_filedata` (
 *   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 *   `indexid` int(10) unsigned NOT NULL DEFAULT '0',
 *   `userid` int(10) unsigned NOT NULL DEFAULT '0',
 *   `filename` varchar(255) NOT NULL DEFAULT '',
 *   `ofilename` varchar(255) NOT NULL DEFAULT '',
 *   `path` varchar(255) NOT NULL DEFAULT '',
 *   `intro` varchar(255) NOT NULL DEFAULT '',
 *   `ext` varchar(10) NOT NULL DEFAULT '',
 *   `size` int(10) unsigned NOT NULL DEFAULT '0',
 *   `time` int(10) unsigned NOT NULL DEFAULT '0',
 *   `type` tinyint(1) NOT NULL DEFAULT '0',
 *   PRIMARY KEY (`id`),
 *   KEY `ext` (`ext`),
 *   KEY `path` (`path`),
 *   KEY `ofilename` (`ofilename`),
 *   KEY `indexid` (`indexid`),
 *   KEY `fn_userid` (`filename`,`userid`)
 * ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8
 */
class iFS {

    public static $TABLE         = null;
    public static $forceExt      = false;
    public static $isRedirect    = false;
    public static $checkFileData = true;
    public static $isValidext    = true;
    public static $config        = null;
    public static $userid        = 0;
    public static $callback      = false;
    public static $FileData      = null;
    public static $watermark     = true;

    public static function init($config,$table='') {
        self::$config = $config;
        $_table_name  = $config['table'];
        if(empty($_table_name)){
            self::$TABLE  = $table ? $table : 'filedata';//文件记录表
        }
    }

    public static function config($config) {
        self::$config = array_merge(self::$config, $config);
    }

    public static function ex($f) {
        return @stat($f) === false ? false : true;
    }

    public static function is_file($file) {
        return @is_file($file);
    }

    public static function is_dir($path) {
        return @is_dir($path);
    }

    public static function is_readable($file) {
        return @is_readable($file);
    }

    public static function is_writable($file) {
        return @is_writable($file);
    }

    public static function atime($file) {
        return @fileatime($file);
    }

    public static function mtime($file) {
        return @filemtime($file);
    }

    public static function check($fn) {
        strpos($fn, '..') !== false && exit('What are you doing?');
    }

    public static function del($fn, $check = 1) {
        $check && self::check($fn);
        @chmod($fn, 0777);
        return @unlink($fn);
    }

    public static function read($fn, $check = 1, $method = "rb") {
        $check && self::check($fn);
        if (function_exists('file_get_contents') && $method != "rb") {
            $filedata = file_get_contents($fn);
        } else {
            if ($handle = @fopen($fn, $method)) {
                flock($handle, LOCK_SH);
                $filedata = @fread($handle, filesize($fn));
                fclose($handle);
            }
        }
        return $filedata;
    }

    public static function write($fn, $data, $check = 1, $method = "rb+", $iflock = 1, $chmod = 1) {
        $check && self::check($fn);
        touch($fn);
        $handle = fopen($fn, $method);
        $iflock && flock($handle, LOCK_EX);
        fwrite($handle, $data);
        $method == "rb+" && ftruncate($handle, strlen($data));
        fclose($handle);
        $chmod && @chmod($fn, 0777);
    }

    //创建目录
    public static function mkdir($d) {
        $d = str_replace('//', '/', $d);
        if (file_exists($d))
            return @is_dir($d);

        // Attempting to create the directory may clutter up our display.
        if (@mkdir($d)) {
//            $stat = @stat(dirname($d));
//            $dir_perms = $stat['mode'] & 0007777;  // Get the permission bits.
            @chmod($d, 0777);
            return true;
        } elseif (is_dir(dirname($d))) {
            return false;
        }

        // If the above failed, attempt to create the parent node, then try again.
        if (( $d != '/' ) && ( self::mkdir(dirname($d))))
            return self::mkdir($d);

        return false;
    }

    //删除目录
    public static function rmdir($dir, $df = true, $ex = NULL) {
        $exclude = array('.', '..');
        $ex && $exclude = array_merge($exclude, (array) $ex);
        if ($dh = @opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if (!in_array($file, $exclude)) {
                    $path = $dir . '/' . $file;
                    is_dir($path) ? self::rmdir($path, $df) : ($df ? @unlink($path) : null);
                }
            }
            closedir($dh);
        }
        return @rmdir($dir);
    }

    public static function info($path) {
        return (OBJECT) pathinfo($path);
    }

    public static function path($p = '') {
        $a = explode('/', $p);
        $o = array();
        $c = count($a);
        for ($i = 0; $i < $c; $i++) {
            if ($a[$i] == '.' || $a[$i] == '')
                continue;
            if ($a[$i] == '..' && $i > 0 && end($o) != '..') {
                array_pop($o);
            } else {
                $o[] = $a[$i];
            }
        }
        $o['0'] == 'http:' && $o['0'] = 'http:/';
        return ($p{0} == '/' ? '/' : '') . implode('/', $o);
    }

    public static function path_is_absolute($path) {
        // this is definitive if true but fails if $path does not exist or contains a symbolic link
        if (realpath($path) == $path)
            return true;

        if (strlen($path) == 0 || $path{0} == '.')
            return false;

        // windows allows absolute paths like this
        if (preg_match('#^[a-zA-Z]:\\\\#', $path))
            return true;

        // a path starting with / or \ is absolute; anything else is relative
        return (bool) preg_match('#^[/\\\\]#', $path);
    }

    public static function path_join($base, $path) {

        if (!self::path_is_absolute($base))
            $path = rtrim($base, '/') . '/' . ltrim($path, '/');

        return self::path($path).'/';
    }

    //获取远程页面的内容
    public static function remote($url, $_referer = false, $_count = 0) {
        if (function_exists('curl_init')) {
		    if(empty($url)){
				echo 'remote:('.$_count.')'.$url."\n";
		        echo "url:empty\n";
		        return false;
		    }
            $uri = parse_url($url);
            $curlopt_referer = $uri['scheme'] . '://' . $uri['host'];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_REFERER, $_referer ? $_referer : $curlopt_referer);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1) ; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729)');
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_NOSIGNAL, true);
            $responses	= curl_exec($ch);
            $info 		= curl_getinfo($ch);
            $errno 		= curl_errno($ch);
			if ($errno > 0) {
	            if ($_count < 5) {
	                $_count++;
					curl_close($ch);
					unset($responses,$info);
	                return self::remote($url, $_referer, $_count);
	            }else{
					$curl_error = curl_error($ch);
					curl_close($ch);
					unset($responses,$info);
                    echo $url." remote:{$_count}\n";
					echo "cURL Error ($errno): $curl_error\n";
					return false;
	            }
			}
            if ($info['http_code'] == 301 || $info['http_code'] == 302) {
                $newurl = $info['redirect_url'];
		        if(empty($newurl)){
			    	curl_setopt($ch, CURLOPT_HEADER, 1);
			    	$header		= curl_exec($ch);
			    	preg_match ('|Location: (.*)|i',$header,$matches);
			    	$newurl 	= ltrim($matches[1],'/');
				    if(empty($newurl)) return false;

			    	if(!strstr($newurl,'http://')){
				    	$host	= $uri['scheme'].'://'.$uri['host'];
			    		$newurl = $host.'/'.$newurl;
			    	}
		        }
		        $newurl	= trim($newurl);
				curl_close($ch);
				unset($responses,$info);
                return self::remote($newurl, $_referer,1);
            }
            if ((empty($responses)||empty($info['http_code'])) && $_count < 5) {
                $_count++;
				curl_close($ch);
				unset($responses,$info);
                return self::remote($url, $_referer, $_count);
            }
            curl_close($ch);
        } elseif (ini_get('allow_url_fopen') && ($handle = fopen($url, 'rb'))) {
            if (function_exists('stream_get_contents')) {
                $responses = stream_get_contents($handle);
            } else {
                while (!feof($handle) && connection_status() == 0) {
                    $responses.= fread($handle, 8192);
                }
            }
            fclose($handle);
        } else {
            $responses = file_get_contents(urlencode($url));
        }
        return $responses;
    }

    //文件名
    public static function name($fn) {
        $_fn = substr(strrchr($fn, "/"), 1);
        return array('name' => substr($_fn, 0, strrpos($_fn, ".")),
            'path' => substr($fn, 0, strrpos($fn, "."))
        );
    }

    // 获得文件扩展名
    public static function getExt($fn) {
        return pathinfo($fn, PATHINFO_EXTENSION);
        //return substr(strrchr($fn, "."), 1);
    }

    // 获取文件大小
    public static function sizeUnit($filesize) {
        $SU = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        $n = 0;
        while ($filesize >= 1024) {
            $filesize /= 1024;
            $n++;
        }
        return round($filesize, 2) . ' ' . $SU[$n];
    }

    public static function icon($fn, $icondir = '') {
        $ext = strtoupper(self::getExt($fn));
        $extArray = array(
            "TXT" => "txt.gif", "XLS" => "xls.gif", "XML" => "xls.gif",
            "CHM" => "hlp.gif", "HLP" => "hlp.gif",
            "DOC" => "doc.gif", "PPS" => "ppt.gif", "PPT" => "ppt.gif", "PDF" => "pdf.gif",
            "MDB" => "mdb.gif",
            "GIF" => "gif.gif", "JPG" => "jpg.gif", "JPEG" => "jpg.gif", "BMP" => "bmp.gif", "PNG" => "pic.gif",
            "ASP" => "code.gif", "JSP" => "code.gif", "JS" => "js.gif", "PHP" => "php.gif", "PHP3" => "php.gif", "ASPX" => "code.gif",
            "HTM" => "htm.gif", "CSS" => "code.gif", "HTML" => "htm.gif", "SHTML" => "htm.gif",
            "ZIP" => "zip.gif", "RAR" => "rar.gif",
            "EXE" => "exe.gif",
            "AVI" => "wmv.gif", "MPG" => "wmv.gif", "MPEG" => "wmv.gif", "ASF" => "mp.gif", "RA" => "rm.gif", "RM" => "rm.gif", "MP3" => "mp3.gif", "MID" => "wmv.gif", "MIDI" => "mid.gif", "WAV" => "audio.gif",
            "PHPFILE" => "php.gif",
            "FILE" => "common.gif",
            "SWF" => "swf.gif",
        );
        $src = $extArray[$ext];
        $src OR $src = "unknow.gif";
        return '<img border="0" src="' . $icondir . '/fileicons/' . $src . '" align="absmiddle" class="icon">';
    }

//-----------upload-------------
    public static function get_dir() {
        return self::path(iPATH . '/' . self::$config['dir']) . '/';
    }

    public static function mk_udir($_dir='') {
        $FileDir  = $_dir ? $_dir : get_date(0, self::$config['dir_format']);
        $FileDir  = rtrim($FileDir,'/').'/';
        $FileDir  = ltrim($FileDir,'./');
        $RootPath = self::get_dir() . $FileDir;
        $RootPath = rtrim($RootPath,'/').'/';
        self::mkdir($RootPath);
        return array($RootPath,$FileDir);
    }

    public static function save_ufile($tn, $fp) {
        if (function_exists('move_uploaded_file') && @move_uploaded_file($tn, $fp)) {
            @chmod($fp, 0777);
        } elseif (@copy($tn, $fp)) {
            @chmod($fp, 0777);
        } elseif (is_readable($tn)) {
            if ($fp = @fopen($tn, 'rb')) {
                @flock($fp, 2);
                $filedata = @fread($fp, @filesize($tn));
                @fclose($fp);
            }
            if ($fp = @fopen($fp, 'wb')) {
                @flock($fp, 2);
                @fwrite($fp, $filedata);
                @fclose($fp);
                @chmod($fp, 0777);
            } else {
                return self::a(array('code'=>0,'state'=>'Error'));
            }
        } else {
            return self::a(array('code'=>0,'state'=>'UNKNOWN'));
        }
    }
    public static function _array($code,$frs,$RP){
        return array('code' =>$code,
            'fid'         => $frs->id,
            'md5'         => $frs->filename,
            'size'        => $frs->size,
            'oname'       => $frs->ofilename,
            'name'        => $frs->filename,
            'fname'       => $frs->filename . "." . $frs->ext,
            'dir'         => $frs->path,
            'ext'         => $frs->ext,
            'RootPath'    => $RP . '/' . $frs->path.$frs->filename . "." . $frs->ext,
            'path'        => $frs->path . $frs->filename . "." . $frs->ext,
            'dirRootPath' => $RP . '/' . $frs->path
        );
    }
    public static function watermark($ext,$frp){
        if (in_array($ext, array('gif', 'jpg', 'jpeg', 'png')) && self::$watermark) {
            iPHP::LoadClass('Pic');
            iPic::watermark($frp);
        }
    }
    public static function IO($FileName='',$udir='',$FileExt='jpg'){
        list($RootPath,$FileDir) = self::mk_udir($udir); // 文件保存目录方式
        $filedata = file_get_contents('php://input');
        if(empty($filedata)){ return false; }

        $file_md5 = md5($filedata);
        $FileName OR $FileName = $file_md5;
        $FileSize = strlen($filedata);
        $FileExt  = self::CheckValidExt($FileName . "." . $FileExt); //判断文件类型
        if(self::$callback && is_array($FileExt) && $FileExt['code']=="0") return $FileExt;
        $FilePath     = $FileDir . $FileName . "." . $FileExt;
        $FileRootPath = $RootPath . $FileName . "." . $FileExt;
        self::write($FileRootPath,$filedata);
        self::watermark($FileExt,$FileRootPath);

        $fid = self::insFileData(array(
            'filename'  => $FileName,
            'ofilename' => '',
            'path'      => $FileDir,
            'ext'       => $FileExt,
            'size'      => $FileSize
        ), 3);
        return array(
            'code'        => 1,
            'fid'         => $fid,
            'md5'         => $file_md5,
            'size'        => $FileSize,
            'oname'       => '',
            'name'        => $FileName,
            'fname'       => $FileName . "." . $FileExt,
            'dir'         => $FileDir,
            'ext'         => $FileExt,
            'RootPath'    => $FileRootPath,
            'path'        => $FilePath,
            'dirRootPath' => $RootPath
        );
    }
    public static function base64ToFile($base64Data,$udir='',$FileExt='png'){
        list($RootPath,$FileDir) = self::mk_udir($udir); // 文件保存目录方式
		$filedata = base64_decode( $base64Data );
        if(empty($filedata)){ return false; }
        $file_md5 = md5($filedata);
        $FileName = $file_md5;
        $FileSize = strlen($filedata);
        $FileExt  = self::CheckValidExt($FileName . "." . $FileExt); //判断文件类型
        if(self::$callback && is_array($FileExt) && $FileExt['code']=="0") return $FileExt;
        $FilePath     = $FileDir . $FileName . "." . $FileExt;
        $FileRootPath = $RootPath . $FileName . "." . $FileExt;
		self::write($FileRootPath,$filedata);
        self::watermark($FileExt,$FileRootPath);
        $fid = self::insFileData(array(
            'filename'  => $file_md5,
            'ofilename' => '',
            'path'      => $FileDir,
            'ext'       => $FileExt,
            'size'      => $FileSize
        ), 2);
        return array(
            'code'        => 1,
            'fid'         => $fid,
            'md5'         => $file_md5,
            'size'        => $FileSize,
            'oname'       => '',
            'name'        => $FileName,
            'fname'       => $FileName . "." . $FileExt,
            'dir'         => $FileDir,
            'ext'         => $FileExt,
            'RootPath'    => $FileRootPath,
            'path'        => $FilePath,
            'dirRootPath' => $RootPath
        );
	}

    public static function upload($field, $udir = '', $FileName = '',$ext='') {
        list($RootPath,$FileDir) = self::mk_udir($udir); // 文件保存目录方式

        if ($_FILES[$field]['name']) {
            $tmp_file = $_FILES[$field]['tmp_name'];
            if(!is_uploaded_file($tmp_file)){
            	return self::a(array('code'=>0,'state'=>'UNKNOWN'));
            }
            if ($_FILES[$field]['error'] > 0) {
                switch ((int) $_FILES[$field]['error']) {
                    case UPLOAD_ERR_NO_FILE:
                        @unlink($tmp_file);
                        return self::a(array('code'=>0,'state'=>'NOFILE'));
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        @unlink($tmp_file);
                        return self::a(array('code'=>0,'state'=>'UPLOAD_MAX'));
                        break;
                }
                return self::a(array('code'=>0,'state'=>'UNKNOWN'));
            }
            $oFileName = $_FILES[$field]['name'];
            $FileExt   = self::CheckValidExt($oFileName); //判断文件类型
            if(self::$callback && is_array($FileExt) && $FileExt['code']=="0") return $FileExt;
            if(self::$FileData){
                $fid                   = self::$FileData->id;
                $file_md5              = self::$FileData->filename;
                $oFileName             = self::$FileData->ofilename;
                $FileDir               = self::$FileData->path;
                $FileExt               = self::$FileData->ext;
                $FileSize              = self::$FileData->size;
                $FileName OR $FileName = $file_md5;
                $FilePath              = self::$FileData->filepath;
                $FileRootPath          = self::fp($FilePath,"+iPATH");
            }else{
                $file_md5 = md5_file($tmp_file);
                $frs      = self::getFileData('filename', $file_md5);
	            if ($frs) {
	                return self::_array(1,$frs,$RootPath);
	            }
                $FileName OR $FileName = $file_md5;
                $ext && $FileExt       = $ext;
                $FileSize     = @filesize($tmp_file);
                $FilePath     = $FileDir . $FileName . "." . $FileExt;
                $FileRootPath = $RootPath . $FileName . "." . $FileExt;
            }
            $ret	= self::save_ufile($tmp_file, $FileRootPath);
            if(self::$callback && is_array($ret) && $ret['code']=="0") return $ret;

            @unlink($tmp_file);
            self::watermark($FileExt,$FileRootPath);

            $fid OR $fid = self::insFileData(array(
                'filename'  => $file_md5,
                'ofilename' => $oFileName,
                'path'      => $FileDir,
                'ext'       => $FileExt,
                'size'      => $FileSize
            ), 0);
            return array('code' =>1,
                'fid'         => $fid,
                'md5'         => $file_md5,
                'size'        => $FileSize,
                'oname'       => $oFileName,
                'name'        => $FileName,
                'fname'       => $FileName . "." . $FileExt,
                'dir'         => $FileDir,
                'ext'         => $FileExt,
                'RootPath'    => $FileRootPath,
                'path'        => $FilePath,
                'dirRootPath' => $RootPath);
        } else {
            return false;
        }
    }

    public static function filterExt($ext, $check = false) {
        if ($check) {
            $ext = strtolower(self::getExt($ext));
            if (stristr($ext, 'ph') || in_array($ext, array('cer', 'htr', 'cdx', 'asa', 'asp', 'jsp', 'aspx', 'cgi'))) {
                return false;
            } else {
                return true;
            }
        }
        stristr($ext, 'ph') && $ext = "phpfile";
        in_array($ext, array('cer', 'htr', 'cdx', 'asa', 'asp', 'jsp', 'aspx', 'cgi')) && $ext = "file";
        return $ext;
    }

    public static function CheckValidExt($fn) {
        $FileExt = strtolower(self::getExt($fn));
        if (self::$forceExt !== false) {
            (empty($FileExt) || strlen($FileExt) > 4) && $FileExt = self::$forceExt;
            return $FileExt;
        }
        if (!self::$isValidext)
            return $FileExt;

        $aExt = explode(',', strtolower(self::$config['allow_ext']));
        if (in_array($FileExt, $aExt)) {
            return self::filterExt($FileExt);
        } else {
            return self::a(array('code'=>0,'state'=>'TYPE'));
        }
    }

    public static function fp($f, $m = '+http', $_config = null) {
        $config = $_config ? $_config : self::$config;
        switch ($m) {
            case '+http':
                $fp = $config['url']. $f;
                break;
            case '-http':
                $fp = str_replace($config['url'], '', $f);
                break;
            case 'http2iPATH':
                $f = str_replace($config['url'], '', $f);
                $fp = self::path_join(iPATH, $config['dir']). $f;
                break;
            case 'iPATH2http':
                $f = str_replace(self::path_join(iPATH, $config['dir']),'', $f);
                $fp = $config['url']. $f;
                break;
            case '+iPATH':
                $fp = self::path_join(iPATH, $config['dir']).$f;
                break;
            case '-iPATH':
                $fp = str_replace(self::path_join(iPATH, $config['dir']), '', $f);
                break;
        }
        return $fp;
    }

//--------upload---end-------------------------------
    public static function insFileData($data, $type = 0) {
        if (!self::$checkFileData) return;

        $userid = self::$userid === false ? 0 : self::$userid;
        $data['userid'] = $userid;
        $data['time']   = time();
        $data['type']   = $type;
        iDB::insert(self::$TABLE,$data);
        return iDB::$insert_id;
    }

    public static function getFileData($f, $v) {
        if (!self::$checkFileData) return;

        $sql = self::$userid === false ? '' : " AND `userid`='" . self::$userid . "'";
        $rs = iDB::row("SELECT * FROM ".iPHP_DB_PREFIX.self::$TABLE." WHERE `$f`='$v' {$sql} LIMIT 1");
        $rs && $rs->filepath = $rs->path . '/' . $rs->filename . '.' . $rs->ext;
        return $rs;
    }

    public static function http($http, $ret='',$times = 0) {
        list($RootPath,$FileDir) = self::mk_udir($udir); // 文件保存目录方式
        $frs = self::getFileData('ofilename', $http);

        if ($frs) {
            if($ret=='array'){
                return self::_array(1,$frs,$RootPath);
            }
            return $frs->path . "/" . $frs->filename . "." . $frs->ext;
        }

        $FileExt = self::CheckValidExt($http); //判断过滤文件类型
		if(self::$callback && is_array($FileExt) && $FileExt['code']=="0") return $FileExt;
        $fileresults = self::remote($http);
//        $fileresults	= self::mremote($http);

        if ($fileresults) {
            $file_md5 = md5($fileresults);
            $frs = self::getFileData('filename', $file_md5);
            if (empty($frs)) {
                $FileName = $file_md5 . "." . $FileExt;
                $FilePath = $FileDir .$FileName;
                $FileRootPath = $RootPath .$FileName;
                self::write($FileRootPath, $fileresults);
                self::watermark($FileExt,$FileRootPath);
                $FileSize = @filesize($FileRootPath);
                empty($FileSize) && $FileSize = 0;
                $fid = self::insFileData(array(
                    'filename'  => $file_md5,
                    'ofilename' => $http,
                    'path'      => $FileDir,
                    'intro'     => $intro,
                    'ext'       => $FileExt,
                    'size'      => $FileSize
                ), 1);
                if($ret=='array'){
                    return array(
                        'code'        => 1,
                        'fid'         => $fid,
                        'md5'         => $file_md5,
                        'size'        => $FileSize,
                        'oname'       => $http,
                        'name'        => $FileName,
                        'fname'       => $FileName . "." . $FileExt,
                        'dir'         => $FileDir,
                        'ext'         => $FileExt,
                        'RootPath'    => $FileRootPath,
                        'path'        => $FilePath,
                        'dirRootPath' => $RootPath
                    );
                }
            } else {
                if($ret=='array'){
                    return self::_array(1,$frs,$RootPath);
                }
                $FilePath = $frs->path . "/" . $frs->filename . "." . $frs->ext;
            }
            return $FilePath;
        } else {
            if ($times < 3) {
                $times++;
                return self::http($http,$ret,$times);
            } else {
                return false;
            }
        }
    }

    //获取远程页面的内容
    public static function mremote($url, $_referer = false) {
        $uri = parse_url($url);
        $curlopt_referer = $uri['scheme'] . '://' . $uri['host'];
        $mch = curl_multi_init();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_REFERER, $_referer ? $_referer : $curlopt_referer);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1) ; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729)');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
        curl_multi_add_handle($mch, $ch);
        $flag = null;
        do {
            curl_multi_exec($mch, $flag);
        } while ($flag > 0);

        $responses = curl_multi_getcontent($ch);
        $info = curl_getinfo($ch);

        if ($info['http_code'] == 301 || $info['http_code'] == 302) {
            $newurl = $info['redirect_url'];
	        if(empty($newurl)){
		    	curl_setopt($ch, CURLOPT_HEADER, 1);
		    	$header		= curl_exec($ch);
		    	preg_match ('|Location: (.*)|i',$header,$matches);
		    	$newurl 	= ltrim($matches[1],'/');
			    if(empty($newurl)) return false;

		    	if(!strstr($newurl,'http://')){
			    	$host	= $uri['scheme'].'://'.$uri['host'];
		    		$newurl = $host.'/'.$newurl;
		    	}
	        }
	        $newurl	= trim($newurl);
            $responses = self::mremote($newurl, false);
        }
        curl_multi_remove_handle($mch, $ch);
        curl_multi_close($mch);
        return $responses;
    }

    public static function remotepic(&$content, $remote = false, $autopic = false) {
        if (!$remote && !$autopic)
            return;

        $content = stripslashes($content);
        $img = array();
        preg_match_all("/<img.*?src\s*=[\"|'](.*?)[\"|']/is", $content, $match);

        $_array = (array) array_unique($match[1]);
        $uri = parse_url(self::$config['url']);
        foreach ($_array AS $_k => $imgurl) {
            if (strstr(strtolower($imgurl), $uri['host']))
                unset($_array[$_k]);
        }
        if (empty($_array)) {
            $content = addslashes($content);
            return;
        }
        self::$forceExt = "jpg";
        foreach ($_array as $key => $value) {
            $filepath = self::http($value);
            $fArray[$key] = $filepath ? self::fp($filepath, '+http') : $value;
        }
        $content = str_replace($_array, $fArray, $content);
        $content = addslashes($content);
    }

    function a($a,$break=false) {
		$stateMap = array(
            "UPLOAD_MAX"    => "文件大小超出 upload_max_filesize 限制" ,
            "MAX_FILE_SIZE" => "文件大小超出 MAX_FILE_SIZE 限制" ,
            "文件未被完整上传" ,
            "没有文件被上传" ,
            "NOFILE"        => "上传文件为空" ,
            "POST"          => "文件大小超出 post_max_size 限制" ,
            "SIZE"          => "文件大小超出网站限制" ,
            "TYPE"          => "不允许的文件类型" ,
            "DIR"           => "目录创建失败" ,
            "IO"            => "输入输出错误" ,
            "UNKNOWN"       => "未知错误" ,
            "Error"         => "Upload Unknown Error (fopen)" ,
            "MOVE"          => "文件保存时出错"
	    );
		$msg	= $stateMap[$a['state']];
    	if(self::$callback){
    		$a['state']	= $msg;
    		return $a;
    	}else{
        	exit('<script type="text/javascript">window.top.alert("' . $msg . '");</script>');
        }
    }

}
