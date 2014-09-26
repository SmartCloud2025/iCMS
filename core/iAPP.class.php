<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
class app {
	public static $table   = 'article';
	public static $primary = 'id';
	public static $appid   = '1';

	public static function init($table = 'article',$appid='1',$primary = 'id'){
		self::$table   = $table;
		self::$primary = $primary;
		self::$appid   = $appid;
		return self;
	}
	public static function cache(){
        $rs = iDB::all("SELECT `id`,`name`,`title`,`table`,`field` FROM `#iCMS@__app`",ARRAY_A);

        foreach((array)$rs AS $a) {
        	$tb_array = json_decode($a['table']);
        	$table = array(
				'name'    => '#iCMS@__'.$tb_array[0][0],
				'primary' => $tb_array[0][1],
        	);
        	if($tb_array[1]){
				$table['join'] = '#iCMS@__'.$tb_array[1][0];
				$table['on']   = $tb_array[1][1];
        	}
        	$a['table'] = $table;
			$app_id_array[$a['id']]     = $a;
			$app_name_array[$a['name']] = $a;

			iCache::delete('iCMS/app/'.$a['id']);
			iCache::set('iCMS/app/'.$a['id'],$a,0);

			iCache::delete('iCMS/app/'.$a['name']);
			iCache::set('iCMS/app/'.$a['name'],$a,0);

        }
        iCache::set('iCMS/app/cache_id',  $app_id_array,0);
        iCache::set('iCMS/app/cache_name',$app_name_array,0);
	}
	public static function get_app($appid=1){
		$rs	= iCache::get('iCMS/app/'.$appid);
       	$rs OR iPHP::throwException('app no exist', 0005);
       	return $rs;
	}
	public static function get_url($appid=1,$primary=''){
		$rs	= self::get_app($appid);
		return iCMS_URL.'/'.$rs['name'].'.php?'.$rs['table']['primary'].'='.$primary;
	}
	public static function get_table($appid=1){
		$rs	= self::get_app($appid);
       	return $rs['table'];
	}


}
