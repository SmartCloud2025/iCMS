<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: login.php 2379 2014-03-19 02:37:47Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>iCMS Permission Denied!</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta content="iDreamSoft Inc." name="Copyright" />
<link rel="stylesheet" href="./app/ui/common/bootstrap/2.3.2/css/bootstrap.min.css" type="text/css" />
<link rel="stylesheet" href="./app/ui/common/bootstrap/2.3.2/css/bootstrap-responsive.min.css" type="text/css" />
<link rel="stylesheet" href="./app/ui/common/iCMS-6.0.0.css" type="text/css" />
<!--[if lt IE 9]>
  <script src="./app/ui/common/ie/html5shiv.min.js"></script>
  <script src="./app/ui/common/ie/respond.min.js"></script>
<![endif]-->
<script type="text/javascript" src="./app/ui/common/jquery-1.11.0.min.js"></script>
<style>
body { background-color:#f8f8f8;}
.iCMS-permission { margin: 240px auto 0; width:720px; }
</style>
</head>
<body>
<div class="container">
  <div class="iCMS-permission">
    <div class="alert">
      <h4>Warning!</h4> 您没有<?php echo "[$p]";?>相关权限!
    </div>
  </div>
</div>
</body>
</html>
