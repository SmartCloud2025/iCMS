<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: navbar.php 2412 2014-05-04 09:52:07Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');
?>

<div id="header" class="navbar navbar-static-top">
  <div class="navbar-inner">
    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse"> <span class="fa fa-bars"></span> </a> <a class="brand iCMS-logo" href="http://www.idreamsoft.com" target="_blank"><img src="<?php echo ACP_UI;?>/iCMS.logo-6.0.png" /></a>
      <div class="nav-collapse collapse">
        <ul class="nav" id="iCMS-menu">
          <?php echo iACP::$menu->show(); ?>
        </ul>
        <ul class="nav pull-right">
          <li><a href="<?php echo iCMS_URL;?>" target="_blank"><i class="fa fa-home fa-lg"></i> 网站首页</a></li>
          <li class="divider-vertical"></li>
          <li class="dropdown"> <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-users"></i> <?php echo iMember::$group->name;?> <b class="caret"></b></a>
            <ul class="dropdown-menu">
              <li><a href="###"><?php echo iMember::$data->nickname;?>,你好!</a></li>
              <li class="divider"></li>
              <li><a href="<?php echo __ADMINCP__; ?>=job"><i class="fa fa-bar-chart-o"></i> 工作统计</a></li>
              <li><a href="<?php echo __ADMINCP__; ?>=account&do=edit"><i class="fa fa-user"></i> 更改信息</a></li>
              <li class="divider"></li>
              <!--li class="nav-header">导航头</li-->
              <li><a href="<?php echo __ADMINCP__; ?>=home&do=logout&frame=iPHP" target="iPHP_FRAME"><i class="fa fa-sign-out"></i> 注销</a></li>
            </ul>
          </li>
        </ul>
      </div>
  </div>
</div>
<?php if(iCMS::$config['other']['sidebar_enable']){?>
<div id="sidebar" class="navbar">
	<div id="sidebartop" class="navbar-inner"><a class="brand iCMS-logo" href="http://www.idreamsoft.com" target="_blank"><img src="<?php echo ACP_UI;?>/iCMS.logo-6.0.png" /></a></div>
  <ul>
    <?php echo iACP::$menu->sidebar(); ?>
    <li class="last"></li>
  </ul>
  <div class="clearfloat"></div>
  <span id="mini"> <i class="fa fa-arrow-circle-left"></i> </span>
</div>
<?php }?>
<div id="content">
<div id="breadcrumb"> <a href="<?php echo __SELF__; ?>" title="返回管理首页" class="tip-bottom"><i class="fa fa-home"></i> 管理中心</a> <?php echo iACP::$menu->breadcrumb(); ?> </div>
