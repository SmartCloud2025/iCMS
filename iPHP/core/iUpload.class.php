<?php
/**
* iPHP - i PHP Framework
* Copyright (c) 2012 iiiphp.com. All rights reserved.
*
* @author coolmoo <iiiphp@qq.com>
* @site http://www.iiiphp.com
* @licence http://www.iiiphp.com/license
* @version 1.0.1
* @package iUpload
* @$Id: iUpload.class.php 2408 2014-04-30 18:58:23Z coolmoo $
*/
class iUpload{
    public static $isValidext      = true;
    protected static $config       = null;
    protected static $FileRootPath = null;
	public static function init($config=null){
		iPHP::loadClass('iFS',"need FileSystem class");
		self::$config=$config;
	}
	public static function exec($field,$_dir='',$FileName=''){
        $RootPath = iFS::path_join(iPATH,($_dir?$_dir:self::$config['dir']));//绝对路径
        if($_FILES[$field]['name']) {
            $tmp_file = $_FILES[$field]['tmp_name'];
            !is_uploaded_file($tmp_file) && exit("What are you doing?");
            if($_FILES[$field]['error'] > 0) {
                switch((int)$_FILES[$field]['error']) {
                    case UPLOAD_ERR_NO_FILE:
                        unlink($tmp_file);
                        self::alert('请选择上传文件!');
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        unlink($tmp_file);
                        self::alert('上传的文件大小超过系统最大值!');
                        break;
                }
                return false;
            }
            $_FileSize 	= filesize($tmp_file);
            
	        $oFileName	= $_FILES[$field]['name'];
            $FileExt	= iFS::CheckValidExt($oFileName);	//判断过滤文件类型
            $file_md5	= md5_file($tmp_file);
            $frs	  	= iFS::getFileData('filename',$file_md5);
			if($frs){
 	           return array('fid'=>$frs->id,'md5'=>$frs->filename,'size'=>$frs->size,'oname'=>$frs->ofilename,'name'=>$frs->filename,'fname'=>$frs->filename.".".$frs->ext,'dir'=>$frs->path,'ext'=>$frs->ext,'RootPath'=>$RootPath.'/'.$frs->path.'/'.$frs->filename.".".$frs->ext,'path'=>$frs->path.'/'.$frs->filename.".".$frs->ext,'dirRootPath'=>$RootPath.'/'.$frs->path);
	       	}
            $FileName OR $FileName = $file_md5.".".$FileExt;
            // 文件保存目录方式
            $FileDir = "";
            if(empty($_dir)){
                if(self::$config['dir_format']){
                    $FileDir = str_replace(array('Y','y','m','n','d','j','H','EXT'),
                            array(get_date(0,'Y'),get_date(0,'y'),get_date(0,'m'),get_date(0,'n'),get_date(0,'d'),get_date(0,'j'),get_date(0,'H'),$FileExt),
                            self::$config['dir_format']);
                }
            }else {
                $FileDir=$_dir;
            }
            $RootPath		= $RootPath.'/'.$FileDir.'/';
            //创建目录
            iFS::mkdir($RootPath);
            //文件名
            $FilePath		= $FileDir.'/'.$FileName;
            self::$FileRootPath	= $RootPath.$FileName;
            self::saveUpload($tmp_file,self::$FileRootPath);
            @unlink($tmp_file);
            $fid	= iFS::insFileData(array('filename'=>$file_md5,'ofilename'=>$oFileName,'path'=>$FileDir,'ext'=>$FileExt,'size'=>$_FileSize),1);
            return array('fid'=>$fid,'size'=>$_FileSize,'oname'=>$oFileName,'name'=>$FileName,'dir'=>$FileDir,'ext'=>$FileExt,'RootPath'=>self::$FileRootPath,'path'=>$FilePath,'dirRootPath'=>$RootPath);
        }else {
            return false;
        }
	}
	function del(){
		@unlink(self::$FileRootPath);
	}
    //保存文件
    function saveUpload($tn,$fp) {
        if (function_exists('move_uploaded_file') && @move_uploaded_file($tn, $fp)) {
            @chmod ($fp, 0777);
        }elseif (@copy($tn, $fp)) {
            @chmod ($fp, 0777);
        }elseif (is_readable($tn)){
            print_r($tn);
            exit;

            if ($fp = @fopen($tn,'rb')) {
                @flock($fp,2);
                $filedata = @fread($fp,@filesize($tn));
                @fclose($fp);
            }
            if ($fp = @fopen($fp, 'wb')) {
                @flock($fp, 2);
                @fwrite($fp, $filedata);
                @fclose($fp);
                @chmod ($fp, 0777);
            } else {
                self::alert("Upload Unknown Error (fopen)");
                return false;
            }
        }else {
            self::alert("Upload Unknown Error");
            return false;
        }
    }
	function alert($msg){
		exit('<script type="text/javascript">alert("'.$msg.'");</script>');
	}
}