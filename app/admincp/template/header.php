<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: header.php 2412 2014-05-04 09:52:07Z coolmoo $
 */
defined('iCMS') OR exit('What are you doing?');
if(iCMS::$config['other']['sidebar_enable']){ 
	iCMS::$config['other']['sidebar'] OR $bodyClass	= 'sidebar-mini';
	$bodyClass	= $_COOKIE['iCMS_APC_sidebar_mini']?'sidebar-mini':'';
}else{
	$bodyClass	= 'sidebar-display';
}
$navbar OR $bodyClass	= 'iframe ';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>iCMS Administrator's Control Panel</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta content="iDreamSoft Inc." name="Copyright" />
<link rel="stylesheet" href="<?php echo ACP_UI;?>/bootstrap-2.3.1/css/bootstrap.min.css" type="text/css" />
<link rel="stylesheet" href="<?php echo ACP_UI;?>/bootstrap-2.3.1/css/bootstrap-responsive.min.css" type="text/css" />
<link rel="stylesheet" href="<?php echo ACP_UI;?>/bootstrap-2.3.1/css/datepicker.css" type="text/css" />
<link rel="stylesheet" href="<?php echo ACP_UI;?>/bootstrap-2.3.1/css/bootstrap-switch.css" type="text/css" />
<link rel="stylesheet" href="<?php echo ACP_UI;?>/bootstrap-2.3.1/css/font-awesome.min.css" type="text/css" />
<link rel="stylesheet" href="<?php echo ACP_UI;?>/jquery/uniform-2.1.2.min.css" type="text/css" />
<link rel="stylesheet" href="<?php echo ACP_UI;?>/jquery/chosen-1.0.0.min.css" type="text/css" />
<link rel="stylesheet" href="<?php echo ACP_UI;?>/artDialog-5.0.4/skins/default.css" type="text/css" />
<link rel="stylesheet" href="<?php echo ACP_UI;?>/iCMS-6.0.0.css" type="text/css" />
<link rel="stylesheet" href="<?php echo ACP_UI;?>/admincp-6.0.0.css" type="text/css" />
<script type="text/javascript" src="<?php echo ACP_UI;?>/jquery/jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="<?php echo ACP_UI;?>/jquery/migrate-1.2.1.js"></script>
<script type="text/javascript" src="<?php echo ACP_UI;?>/jquery/scrollUp-1.1.0.min.js"></script>
<script type="text/javascript" src="<?php echo ACP_UI;?>/jquery/uniform-2.1.2.min.js"></script>
<script type="text/javascript" src="<?php echo ACP_UI;?>/jquery/chosen-1.0.0.js"></script>
<script type="text/javascript" src="<?php echo ACP_UI;?>/artDialog-5.0.4/artDialog.min.js"></script>
<script type="text/javascript" src="<?php echo ACP_UI;?>/artDialog-5.0.4/artDialog.plugins.min.js"></script>
<script type="text/javascript" src="<?php echo ACP_UI;?>/bootstrap-2.3.1/js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo ACP_UI;?>/bootstrap-2.3.1/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo ACP_UI;?>/bootstrap-2.3.1/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="<?php echo ACP_UI;?>/iCMS-6.0.0.js"></script>
<script type="text/javascript" src="<?php echo ACP_UI;?>/admincp-6.0.0.js"></script>
<script type="text/javascript">
window.iCMS.DIR		= "/<?php echo trim(iCMS::$config['router']['DIR'],'/');?>/";
window.iCMS.APP		= "<?php echo __ADMINCP__;?>";
window.iCMS.UI		= "<?php echo ACP_UI;?>";
window.iCMS.URL		= "<?php echo iCMS::$config['router']['URL'];?>";
window.iCMS.PUBLIC	= "<?php echo iCMS::$config['router']['publicURL'];?>";
window.iCMS.DEFTPL	= "<?php echo iCMS::$config['site']['PC_TPL'];?>";

$(function(){
	var a = $("#iCMS-menu"),b = $("#sidebar");$("[data-menu='m<?php echo iACP::$menu->rootid; ?>']",a).addClass("active");
	var c = $("[data-menu='m<?php echo iACP::$menu->parentid; ?>']",b).addClass("active");
	if(c.hasClass("submenu")){
		c.addClass("open");
		$("ul",c).show();
	}
})

</script>
</head>
<body class="<?php echo $bodyClass; ?>">
<iframe width="0" height="0" style="display:none" id="iPHP_FRAME" name="iPHP_FRAME"></iframe>
<div id="iPHP_DIALOG" title="iPHP提示" class="hide"><img src="<?php echo ACP_UI;?>/loading.gif" /></div>
<div id="iCMS_MODAL" class="modal hide">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3>iCMS 提示</h3>
  </div>
  <div class="modal-body">
    <p><img src="<?php echo ACP_UI;?>/loading.gif" /></p>
  </div>
</div>
