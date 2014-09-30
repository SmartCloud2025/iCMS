<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: public.app.php 1392 2013-05-20 12:28:08Z coolmoo $
 */
class publicApp {
	public $methods	= array('seccode','agreement','crontab','time');
	public function API_seccode(){
		@header("Expires: -1");
		@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
		@header("Pragma: no-cache");
		iPHP::loadClass("Seccode");
		iSeccode::run();
	}
    public function API_agreement(){
    	iPHP::view('iCMS://agreement.htm');
    }
    public function API_time(){
		$time      = $_SERVER['REQUEST_TIME'];
		$toady     = get_date($time,"Ymd H:i:s");
		$week      = get_date($time,"YW");
		$month     = get_date($time,"Ym");
		$year      = get_date($time,"Y");

		$yesterday  = get_date($time-86400+1,"Ymd H:i:s");
		$last_week  = get_date(mktime(1,0,0,date("m"),date("d")-date("w")+1-7,date("Y")),"YW");
		$last_month = get_date(mktime(1,0,0,date("m")-1,1,date("Y")),"Ym");
		$last_year  = $year-1;
    	echo $time,'<hr />';
    	echo $toady,'<br />',$week,'<br />',$month,'<br />',$year,'<hr />';
    	echo $yesterday,'<br />',$last_week,'<br />',$last_month,'<br />',$last_year,'<br />';
    }
    public function API_crontab(){

    }
}
