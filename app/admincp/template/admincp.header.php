<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: header.php 2412 2014-05-04 09:52:07Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>iCMS Administrator's Control Panel</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<!-- <meta name="viewport" content="width=device-width,user-scalable=yes, minimum-scale=0.4, initial-scale=0.8,target-densitydpi=low-dpi" /> -->
<meta content="iDreamSoft Inc." name="Copyright" />
<link rel="stylesheet" href="./app/ui/common/bootstrap/2.3.1/css/bootstrap.min.css" type="text/css" />
<link rel="stylesheet" href="./app/ui/common/bootstrap/2.3.1/css/bootstrap-responsive.min.css" type="text/css" />
<link rel="stylesheet" href="./app/ui/common/bootstrap/2.3.1/css/datepicker.css" type="text/css" />
<link rel="stylesheet" href="./app/ui/common/bootstrap/2.3.1/css/bootstrap-switch.css" type="text/css" />
<link rel="stylesheet" href="./app/ui/common/font-awesome/4.2.0/css/font-awesome.min.css" type="text/css" />
<link rel="stylesheet" href="./app/ui/common/artDialog/5.0.4/skins/default.css" type="text/css" />
<link rel="stylesheet" href="./app/ui/common/iCMS-6.0.0.css" type="text/css" />
<!--[if lt IE 9]>
  <script src="./app/ui/common/ie/html5shiv.min.js"></script>
  <script src="./app/ui/common/ie/respond.min.js"></script>
<![endif]-->
<script src="./app/ui/common/jquery-1.11.0.min.js" type="text/javascript"></script>
<script src="./app/ui/common/artDialog/5.0.4/artDialog.min.js" type="text/javascript"></script>
<script src="./app/ui/common/artDialog/5.0.4/artDialog.plugins.min.js" type="text/javascript"></script>
<script src="./app/ui/common/bootstrap/2.3.1/js/bootstrap.min.js" type="text/javascript"></script>
<script src="./app/ui/common/bootstrap/2.3.1/js/bootstrap-datepicker.js" type="text/javascript"></script>
<script src="./app/ui/common/bootstrap/2.3.1/js/bootstrap-switch.min.js" type="text/javascript"></script>
<script src="./app/ui/common/iCMS-6.0.0.js" type="text/javascript"></script>

<!-- admincp ui -->
<link href="./app/admincp/ui/jquery/uniform-2.1.2.min.css" type="text/css" rel="stylesheet"/>
<link href="./app/admincp/ui/jquery/chosen-1.1.0.min.css" type="text/css" rel="stylesheet"/>
<link href="./app/admincp/ui/admincp-6.0.0.css" type="text/css" rel="stylesheet"/>

<script src="./app/admincp/ui/jquery/migrate-1.2.1.js" type="text/javascript"></script>
<script src="./app/admincp/ui/jquery/scrollUp-1.1.0.min.js" type="text/javascript"></script>
<script src="./app/admincp/ui/jquery/uniform-2.1.2.min.js" type="text/javascript"></script>
<script src="./app/admincp/ui/jquery/chosen-1.1.0.js" type="text/javascript"></script>
<script src="./app/admincp/ui/admincp-6.0.0.js" type="text/javascript"></script>

<script type="text/javascript">
window.iCMS.init({
  API:'<?php echo __SELF__;?>',
  UI:'./app/ui/common',
  URL:'<?php echo iCMS_URL;?>',
  PUBLIC:'<?php echo iCMS_PUBLIC_URL;?>',
  DEFTPL:'<?php echo iPHP_TPL_DEFAULT;?>',
  COOKIE:'<?php echo iPHP_COOKIE_PRE;?>',
});
$(function(){
	<?php if($_GET['tab']){?>
	var $itab = $("#<?php echo iACP::$app_name; ?>-tab");
	$("li",$itab).removeClass("active");
	$(".tab-pane").removeClass("active").addClass("hide");
	$("a[href ='#<?php echo iACP::$app_name; ?>-<?php echo $_GET['tab']; ?>']",$itab).parent().addClass("active");
	$("#<?php echo iACP::$app_name; ?>-<?php echo $_GET['tab']; ?>").addClass("active").removeClass("hide");
	<?php }?>
})
</script>
</head>
<body class="<?php echo $body_class; ?>">
<iframe class="hide" id="iPHP_FRAME" name="iPHP_FRAME"></iframe>
<div id="iPHP-DIALOG" title="iPHP提示" class="hide"><img src="./app/admincp/ui/loading.gif" /></div>
<div id="iCMS-MODAL" class="modal">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 class="modal-title">iCMS 提示</h3>
  </div>
  <div class="modal-body">
    <p><img src="./app/admincp/ui/loading.gif" /></p>
  </div>
</div>
