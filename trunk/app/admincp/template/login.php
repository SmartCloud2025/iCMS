<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: login.php 2379 2014-03-19 02:37:47Z coolmoo $
 */
defined('iCMS') OR exit('What are you doing?'); 
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>iCMS Administrator's Control Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta content="iDreamSoft Inc." name="Copyright" />
<link rel="stylesheet" href="<?php echo iCMS_UI;?>/bootstrap-2.3.1/css/bootstrap.min.css" type="text/css" />
<link rel="stylesheet" href="<?php echo iCMS_UI;?>/bootstrap-2.3.1/css/bootstrap-responsive.min.css" type="text/css" />
<link rel="stylesheet" href="<?php echo iCMS_UI;?>/iCMS-6.0.0.css" type="text/css" />
<!--[if lt IE 9]>
  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script type="text/javascript" src="<?php echo iCMS_UI;?>/jquery/jquery-1.11.0.min.js"></script>
<style>
body { background-color:#f8f8f8;}
.iCMS-login { margin: 240px auto 0; width:720px; }
.btn { -webkit-box-shadow: none !important; -moz-box-shadow: none !important; box-shadow: none !important; background-image: none !important; }
.info { float:left; width:360px; }
.login { margin-left:380px; }
.login label { float: left; padding: 3px 10px 3px 5px; height:30px; line-height: 30px; }
.login input { width: 200px; height:28px; line-height: 30px; }
.login label i { background-repeat: no-repeat; background-attachment: scroll; background-position: center; background-color: transparent; width: 16px; display: inline-block; border-right: 1px solid #dddddd; margin-right: 10px; padding: 10px; vertical-align: middle; }
.login label span { text-align: center !important; color: #666666; text-shadow: 0 1px 0 #ffffff; }
.ipt_uname { margin-bottom:20px !important; }
.ipt_uname i { background-image: url('<?php echo iCMS_UI;?>/img/icons/16/user.png'); }
.ipt_pass { margin-bottom:20px !important; }
.ipt_pass i { background-image: url('<?php echo iCMS_UI;?>/img/icons/16/lock.png'); }
.login .controls { margin-left: 120px; }
</style>
<script type="text/javascript">
$(function(){
	$("form").submit(function(){
		var username = $("#username").val(),password = $("#password").val();
		if(username==""){
			alert("请填写账号!!");
			$("#username").focus();
			return false;
		}
		if(password==""){
			alert("请填写密码!!");
			$("#password").focus();
			return false;
		}
		$.post("<?php echo __SELF__; ?>", { username: username, password: password, ajax: 1 },
			function(json){
				if(json.code=="1"){
					window.location.href ='<?php echo __SELF__; ?>';
				}else{
					alert("账号或密码错误!!");
				}
		},'json');
		return false;
	}); 
})
</script>
</head>
<body>
<div class="container">
  <div class="iCMS-login">
    <div class="info"> <a class="iCMS-logo" href="http://www.idreamsoft.com" target="_blank"><img src="<?php echo iCMS_UI;?>/iCMS.login-6.0.png" /></a>
      <p>iCMS 是一套采用 PHP 和 MySQL 构建的高效简洁的内容管理系统,为您的网站提供一个完美的开源解决方案</p>
    </div>
    <div class="login">
      <form action="<?php echo __SELF__; ?>" method="post" enctype="multipart/form-data" class="form-horizontal" id="iCMS-Login" target="iPHP_FRAME">
        <div class="ipt_uname">
          <label for="username"><i></i><span>账 号</span></label>
          <input type="text" name="username" id="username" />
        </div>
        <div class="ipt_pass">
          <label for="password"><i></i><span>密 码</span></label>
          <input type="password" name="password" id="password" />
        </div>
        <div class="control-group">
          <div class="controls">
            <button class="btn btn-primary" type="submit"><i class="icon-ok icon-white"></i> 登 陆</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>