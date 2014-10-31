<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: public.app.php 1392 2013-05-20 12:28:08Z coolmoo $
 */
class publicApp {
	public $methods	= array('weixin','sitemapindex','sitemap','seccode','agreement','crontab','time');
    public function API_agreement(){
    	iPHP::view('iCMS://agreement.htm');
    }
    public function API_sitemapindex(){
        header("Content-type:text/xml");
        iPHP::view('iCMS://sitemap.index.htm');
    }
    public function API_sitemap(){
        header("Content-type:text/xml");
        iPHP::assign('cid',(int)$_GET['cid']);
        iPHP::view('iCMS://sitemap.baidu.htm');
    }
    public function API_weixin(){
        if(iPHP_DEBUG){
            // ob_start();
            // iDB::$show_errors = true;
        }

        if ($_GET["api_token"]!=iCMS::$config['api']['weixin']['token']) {
            throw new Exception('TOKEN is error!');
        }

        if($_GET["echostr"] && !$_GET['msg_signature']){
            if($this->weixin_checkSignature()){
                echo $_GET["echostr"];
                exit;
            }
        }
        $input = file_get_contents("php://input");
        if ($input){
            $xml          = simplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
            $FromUserName = $xml->FromUserName;
            $ToUserName   = $xml->ToUserName;
            $content      = trim($xml->Content);
            $CreateTime   = time();
            $dayline      = get_date('','Y-m-d H:i:s');
            $fields       = array('ToUserName', 'FromUserName', 'CreateTime', 'content', 'dayline');
            $data         = compact($fields);
            $content && iDB::insert('weixin_api_log',$data);
            iPHP::assign('weixin',$data);
            iPHP::view("iCMS://weixin.api.htm");
        }
        if(iPHP_DEBUG){
            // $output = ob_get_contents();
            // ob_end_clean();
            // echo $output;
            // iFS::write('weixin.api.debug.log',$output,1,'ab+');
        }
    }
    private function weixin_checkSignature(){
        // you must define TOKEN by yourself
        if (!iCMS::$config['api']['weixin']['token']) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce     = $_GET["nonce"];

        $token  = iCMS::$config['api']['weixin']['token'];
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    public function API_crontab(){
        $timeline = iCMS::timeline();
        //var_dump($timeline);
        $pieces = array();
        foreach ($timeline as $key => $bool) {
            $field = "hits_{$key}";
            if($key=='yday'){
                if($bool==1){
                    $pieces[]="`hits_yday` = hits_today";
                }elseif ($bool>1) {
                    $pieces[]="`hits_yday` = 0";
                }
                continue;
            }
            $bool OR $pieces[]="`{$field}` = 0";
        }
        $sql = implode(',', $pieces);
        if($sql){
        	//点击初始化
        	iDB::query("UPDATE `#iCMS@__article` SET {$sql}");
        	iDB::query("UPDATE `#iCMS@__user` SET {$sql}");
        }
    }
    public function API_seccode(){
        @header("Expires: -1");
        @header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
        @header("Pragma: no-cache");
        iPHP::loadClass("Seccode");
        iSeccode::run();
    }

    public function qrcode(){
        iPHP::QRcode();
    }
}
