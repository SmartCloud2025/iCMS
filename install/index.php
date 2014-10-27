<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: install.php 2330 2014-01-03 05:19:07Z coolmoo $
*/
define('iPHP',TRUE);
define('iPHP_APP','iCMS'); //应用名
define('iPATH',dirname(strtr(__FILE__,'\\','/'))."/../");
//框架初始化
require iPATH.'iPHP/iPHP.php';		//iPHP框架文件

$_URI      = $_SERVER['PHP_SELF'];
$_DIR      = substr(dirname($_URI),0,-8);
$_DIR      = trim($_DIR,'/').'/';
$_DIR =='/' OR $_DIR = '/'.$_DIR;
$_URL      = 'http://'.$_SERVER['HTTP_HOST'].trim($_DIR,'/');
$lock_file = iPATH.'cache/install.lock';
?>
<!DOCTYPE html>
<html lang="zh-CN">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>iCMS V6 - 安装向导</title>
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta content="iDreamSoft Inc." name="Copyright" />
		<link href="../app/ui/common/bootstrap/2.3.2/css/bootstrap.min.css" type="text/css" rel="stylesheet"/>
		<link href="../app/ui/common/bootstrap/2.3.2/css/bootstrap-responsive.min.css" type="text/css" rel="stylesheet"/>
		<link href="../app/ui/common/font-awesome/4.2.0/css/font-awesome.min.css" type="text/css" rel="stylesheet"/>
		<link href="../app/ui/common/artDialog/6.0.3/ui-dialog.css" type="text/css" rel="stylesheet"/>
		<link href="../app/ui/common/iCMS-6.0.0.css" type="text/css" rel="stylesheet"/>
		<link href="./install-6.0.0.css" type="text/css" rel="stylesheet"/>
		<!--[if lt IE 9]>
		<script src="../app/ui/common/ie/html5shiv.min.js"></script>
		<script src="../app/ui/common/ie/respond.min.js"></script>
		<![endif]-->
		<script src="../app/ui/common/jquery-1.11.0.min.js"></script>
		<script src="../app/ui/common/bootstrap/2.3.2/js/bootstrap.min.js"></script>
		<script src="../app/ui/common/artDialog/6.0.3/dialog-plus-min.js"></script>
		<script src="../app/ui/common/iCMS-6.0.0.js"></script>
		<script>
		var install = {
			start:function () {
				$(".step").hide();
				this.step(0,1);
			},
			step1:function (a,b) {
				this.step(1,2);
			},
			step2:function (a,b) {
				this.step(2,3);
			},
			step3:function (a,b) {
				this.step(3,4);
			},
			step4:function (a,b) {
				this.step(4,5);
			},
			step:function (a,b) {
				$("#step"+b).show();
				$("#step"+a).hide();
				$('body').animate({
                    scrollTop: 570
                });
			},
		}
		$(function() {
			$('[data-toggle]').click(function(event) {
				event.preventDefault();
				var action = $(this).attr('data-toggle');
				install[action]();
			});
			<?php if($_GET['step']){?>
				$(".step").hide();
				$("#step<?php echo $_GET['step'];?>").show();
				$('body').animate({
                    scrollTop: 570
                });
			<?php }?>
			$("#install_btn").click(function(event) {
				event.preventDefault();

				var db_host    = $('#DB_HOST').val(),
				db_user        = $('#DB_USER').val(),
				db_password    = $('#DB_PASSWORD').val(),
				db_name        = $('#DB_NAME').val(),
				admin_name     = $('#ADMIN_NAME').val(),
				admin_password = $('#ADMIN_PASSWORD').val();

				if(db_host==''){
					iCMS.alert('请填写数据库服务器地址');
					$('#DB_HOST').focus();
					return false;
				}
				if(db_user==''){
					iCMS.alert('请填写数据库用户名');
					$('#DB_USER').focus();
					return false;
				}

				if(db_password==''){
					iCMS.alert('请填写数据库密码');
					$('#DB_PASSWORD').focus();
					return false;
				}

				if(db_name==''){
					iCMS.alert('请填写数据库名');
					$('#DB_NAME').focus();
					return false;
				}

				if(admin_name==''){
					iCMS.alert('请填写超级管理账号');
					$('#ADMIN_NAME').focus();
					return false;
				}
				if(admin_password==''){
					iCMS.alert('请填写超级管理员密码');
					$('#ADMIN_PASSWORD').focus();
					return false;
				}
		        if (admin_password.length < 6) {
					iCMS.alert('请设置至少6位以上带字母、数字及符号的密码');
					$('#ADMIN_PASSWORD').focus();
					return false;
		        }
				$(this).button('loading');
				$("#install_form").submit();
			});

		})
		function callback(el){
			if(el){
				$(el).focus();
			}
			$("#install_btn").button('reset');
		}
		</script>
	</head>
	<body>
		<div class="jumbotron masthead">
			<div class="container">
				<h1>iCMS V6</h1>
				<p>简洁、高效、开源的内容管理系统，让网站管理更高效、简单。</p>
				<p>
					<?php if(file_exists($lock_file)){ ?>
					<button type="button" class="btn btn-large" disabled>开始安装</button>
					<?php }else{;?>
					<a class="btn btn-primary btn-large" data-toggle="start"> 开始安装</a>
					<?php };?>
				</p>
				<ul class="masthead-links">
					<li>
						<a href="http://github.com/idreamsoft/icms" target="_blank">源码</a>
					</li>
					<li>
						<a href="http://www.idreamsoft.com" target="_blank">官网</a>
					</li>
					<li>
						<a href="http://www.idreamsoft.com/examples" target="_blank">案例</a>
					</li>
					<li><a href="https://www.linode.com/?r=35103ee1524aaff9b3edcca8cf7de8fab6e5cf9e" target="_blank">VPS推荐</a></li>
					<li><a href="http://s.click.taobao.com/t?e=m%3D2%26s%3D7JZW1mRrlBccQipKwQzePCperVdZeJviEViQ0P1Vf2kguMN8XjClAmpVu972RyTOt3h8CUvoNV%2Fh102VLn%2Bh5EqjLczoYHWIfyEaJ8bL%2F82Uatkzf5yweOdn1BbglxZYxUhy8exlzcq9AmARIwX9K%2BnbtOD3UdznPV1H2z0iQv9NkKVMHClW0QbMqOpFMIvnvjQXzzpXdTHGJe8N%2FwNpGw%3D%3D" target="_blank">阿里云</a></li>
					<li>
						Version 6.0.0
					</li>
				</ul>
			</div>
		</div>
		<div class="social">
			<div class="container">
			</div>
		</div>
		<div class="container">
			<div class="clearfix mt60"></div>
			<?php if(file_exists($lock_file)){ ?>
			<div class="alert">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Warning!</strong> 您已经安装过iCMS了，如果想要重新安装，请先删除 <span class="label label-info">cache/install.lock</span>这个文件。
			  <br />
			  如果已经安装完成，请马上删除本安装程序<span class="label label-important">install</span>目录，以免存在安全隐患
			</div>
			<?php };?>
			<div class="marketing step" id="step0">
				<h1>iCMS V6介绍。</h1>
				<p class="marketing-byline">需要为爱上iCMS找N多理由吗？ 就在眼前。</p>
				<div class="row-fluid">
					<div class="span4">
						<img class="marketing-img" src="./img/Development.png">
						<h2>人人为我，我为人人。</h2>
						<p>由<a href="http://t.qq.com/idreamsoft">@艾梦软件</a> 历时两年多开发，并在实际项目中高效运行。iCMS 使用了
						<a href="http://www.idreamsoft.com/iPHP/">iPHP</a>、
						<a href="http://github.com/twbs/bootstrap">Bootstrap</a>、
						<a href="http://jquery.com">jQuery</a>、
						<a href="https://github.com/aui/artDialog">artDialog</a>等开源软件，
						并托管在 <a href="http://github.com">GitHub</a> 上，方便大家使用这一套程序构建更好的web应用。
						</p>
					</div>
					<div class="span4">
						<img class="marketing-img" src="./img/responsive-design.png">
						<h2>一套程序、多种设备。</h2>
						<p>你的网站能在 <a href="http://www.idreamsoft.com" target="_blank">iCMS</a> 的帮助下通过同一套内容管理系统快速、有效适配手机、平板、PC 设备，这一切都是归于 iCMS 多终端适配功能。</p>
					</div>
					<div class="span4">
						<img class="marketing-img" src="./img/Enterprise-Features.jpg">
						<h2>功能齐全。</h2>
						<p><a href="http://www.idreamsoft.com" target="_blank">iCMS</a> 提供了网站运营所需的基本功能。也提供了功能强大标签(TAG)系统、内容多属性多栏目归属、自定义内链、高负载、整合第三方登陆</p>
					</div>
				</div>
			</div>

			<div class="license well hide step" id="step1">
				<h1>iCMS使用许可协议</h1>
				<p></p>
				<p>感谢您选择iCMS <?php echo iCMS_VER ;?>。希望我们的努力能为您提供一个高效快速和强大的内容管理解决方案。</p>
				<p>本软件为开源软件，遵循 <a href="http://www.gnu.org/licenses/lgpl-2.1.html">LGPL</a> (GNU Lesser General Public License)开源协议</p>
				<p>本软件版权归 iCMS 官方所有，且受《中华人民共和国计算机软件保护条例》等知识产权法律及国际条约与惯例的保护。</p>
				<p>无论个人或组织、盈利与否、用途如何，均需仔细阅读本协议，在理解、同意、并遵守本协议的全部条款后，方可开始使用本软件。 </p>
				<h2>开源协议</h2>
				<p>iCMS 采用 <a href="http://www.gnu.org/licenses/lgpl-2.1.html">LGPL</a> 开源协议：</p>
				<ul>
					<li>基于 GPL 的软件允许商业化销售，但不允许封闭源代码。</li>
					<li>如果您对遵循 GPL 的软件进行任何改动和/或再次开发并予以发布，则您的产品必须继承 GPL 协议，不允许封闭源代码。</li>
					<li>基于 LGPL 的软件也允许商业化销售，但不允许封闭源代码。</li>
					<li>如果您对遵循 LGPL 的软件进行任何改动和/或再次开发并予以发布，则您的产品必须继承 LGPL 协议，不允许封闭源代码。<br />但是如果您的程序对遵循 LGPL 的软件进行任何连接、调用而不是包含，则允许封闭源代码。</li>
				</ul>
				<h2>免责声明</h2>
				<ol>
					<li>本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的。</li>
					<li>您拥有使用本软件构建的网站全部内容所有权，并独立承担与这些内容的相关法律义务。 </li>
					<li>用户出于自愿而使用本软件，您必须了解使用本软件的风险，iCMS 不承担任何因使用本软件而产生问题的相关责任。</li>
					<li>iCMS 不对使用本软件构建的网站中的文章或信息承担责任。</li>
				</ol>
				<h2>商业授权</h2>
				<p>您享有的权利</p>
				<ul>
					<li>您可以将 iCMS 程序直接使用在自己的商业或者非商业网站或者软件产品中。</li>
					<li>您可以对 iCMS 进行修改和美化，可以去除 iCMS 版权注释或改变程序名称，无需公开您修改或美化过的 iCMS 程序与界面。</li>
					<li>商业授权每个公司只需要购买一次，而不限制产品域名。适用于 iCMS 现有版本和所有后续版本，永久有效。</li>
					<li>您享有反映和提出意见的优先权，相关意见将被作为首要考虑。</li>
				</ul>
				<hr />
				<p>电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和等同的法律效力。</p>
				<div class="alert alert-error">您一旦开始安装 iCMS，即被视为完全理解并接受本协议的各项条款，在享有上述条款授予的权力的同时，受到相关的约束和限制。违反本授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。</div>
				<div class="form-actions">
					<button type="button" class="btn btn-large btn-primary" data-toggle="step1">我同意并遵循以上协议，继续安装</button>
					<a type="button" class="btn btn-link" onclick="javascript:window.open(location, '_self').close();">不接受</a>
				</div>
			</div>
			<div class="well hide step" id="step2">
				<h1>第一步：安装须知</h1>
				<p>欢迎使用 iCMS <?php echo iCMS_VER ;?>，本向导将帮助您将程序完整地安装在您的服务器内。</p>
				<h2>请您先确认以下安装配置: </h2>
				<ul>
					<li>MySQL 主机名称/IP 地址 </li>
					<li>MySQL 用户名和密码 </li>
					<li>MySQL 数据库名称 </li>
				</ul>
				<p class="alert alert-block">如果您无法确认以上的配置信息, 请与您的主机服务商联系, 我们无法为您提供任何帮助.</p>
				<h2>服务器配置: </h2>
				<table class="table">
					<thead>
						<tr>
							<th>#</th>
							<th>运行环境</th>
							<th>推荐版本</th>
							<th>当前版本</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>1</td>
							<td>操作系统</td>
							<td>推荐 Linux OR FreeBSD</td>
							<td><?php echo PHP_OS ; ?></td>
						</tr>
						<tr>
							<td>2</td>
							<td>WEB服务器</td>
							<td>推荐 NGINX OR APACHE</td>
							<td><?php echo $_SERVER['SERVER_SOFTWARE'] ; ?></td>
						</tr>
						<tr>
							<td>3</td>
							<td>PHP版本</td>
							<td>推荐 PHP 5.3以上</td>
							<td>PHP <?php echo PHP_VERSION ; ?></td>
						</tr>
						<tr>
							<td>4</td>
							<td>MySQL数据库</td>
							<td>推荐 MySQL 5.1以上</td>
							<td>MySQL</td>
						</tr>
						<tr>
							<td>5</td>
							<td>GD库</td>
							<td>支持</td>
							<td><?php
											$gd_info = @gd_info ();
											if($gd_info){
												echo $gd_info['GD Version'];
											}else{
												echo '<font style="color:red;">× 不支持</font>';
											}
							?></td>
						</tr>
					</tbody>
				</table>
				<div class="form-actions">
					<button type="button" class="btn btn-large btn-primary" data-toggle="step2">确认，继续安装</button>
				</div>
			</div>
			<div class="well hide step" id="step3">
				<h1>第二步：程序环境检测</h1>
				<p class="alert alert-info">检查必要目录和文件是否可写，如果发生错误，请更改文件/目录属性 777</p>
				<?php
				$check      = 1;
				$correct    = '<span class="chk" style="color:green;">√</span>';
				$incorrect  = '<span class="chk" style="color:red;">× 777属性检测不通过</span>';
				$uncorrect  = '<span class="chk" style="color:red;">× 文件不存在请上传此文件</span>';
				$check_list = array(
					array('config.php','系统配置文件'),
					array('conf','网站配置目录'),
					array('conf/iCMS','网站配置目录'),
					array('cache','缓存目录'),
					array('cache/iCMS','系统缓存目录'),
					array('cache/template','模板编译目录'),
					array('res','资源上传目录'),
					array('html','静态生成目录'),
				);
				if($fp=@fopen(iPATH.'iCMS.txt',"wb")) {
					$state = $correct;
					fclose($fp);
				} else {
					$state = $incorrect.'程序根目录无法书写,请速将根目录属性设置为777';
				}
				foreach ($check_list as $key => $value) {
					$file = iPATH.$value[0];
					if(!file_exists($file)) {
						$check_list[$key][2]= $uncorrect;
						$check = 0;
					} elseif(is_writable($file)) {
						$check_list[$key][2]= $correct;
					} else {
						$check_list[$key][2]= $incorrect;
						$check = 0;
					}
				}
				$check && @unlink(iPATH.'iCMS.txt');
				?>
				<table class="table">
					<thead>
						<tr>
							<th>#</th>
							<th>项目</th>
							<th>路径</th>
							<th>检查结果</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>0</td>
							<td>程序根目录</td>
							<td><?php echo $_DIR; ?></td>
							<td><?php echo $state; ?></td>
						</tr>
						<?php foreach($check_list as $key=>$value) { ?>
						<tr>
							<td><?php echo $key+1 ; ?></td>
							<td><?php echo $value[1]; ?></td>
							<td><?php echo $value[0]; ?></td>
							<td><?php echo $value[2]; ?></td>
						</tr>
						<?php }?>
					</tbody>
				</table>
				<div class="form-actions">
					<?php if($check) { ?>
					<button type="button" class="btn btn-large btn-primary" data-toggle="step3">下一步</button>
					<?php }else {?>
					<button type="button" class="btn btn-large btn-primary" onclick='window.location="<?php echo $_URI;?>?step=3"'>重新检查</button>
					<?php }?>
				</div>
			</div>
			<div class="well hide step" id="step4">
				<h1>第三步：配置信息</h1>
				<h2>数据库配置</h2>
				<form class="form-horizontal" action="install.php" method="post" id="install_form" target="iCMS_FRAME">
					<input name="action" type="hidden" value="install" />
					<div class="control-group">
						<label class="control-label" for="DB_HOST">服务器地址</label>
						<div class="controls">
							<input type="text" class="span4" id="DB_HOST" name="DB_HOST" value="localhost">
							<span class="help-block">数据库服务器名或服务器ip，一般为localhost</span>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="DB_USER">数据库用户名</label>
						<div class="controls">
							<input type="text" class="span4" id="DB_USER" name="DB_USER" placeholder="数据库用户名">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="DB_PASSWORD">数据库密码</label>
						<div class="controls">
							<input type="text" class="span4" id="DB_PASSWORD" name="DB_PASSWORD" placeholder="数据库密码">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="DB_NAME">数据库名</label>
						<div class="controls">
							<input type="text" class="span4" id="DB_NAME" name="DB_NAME" placeholder="数据库名">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="DB_PREFIX">数据表名前缀</label>
						<div class="controls">
							<input type="text" class="span4" id="DB_PREFIX" name="DB_PREFIX" value="icms_">
							<span class="help-block">数据表名前缀，同一数据库安装多个请修改此处。<span class="label label-important">如果存在同名数据表，程序将自动删除</span></span>
						</div>
					</div>
					<h2>设置超级管理员</h2>
					<div class="control-group">
						<label class="control-label" for="ADMIN_NAME">账号</label>
						<div class="controls">
							<input type="text" id="ADMIN_NAME" name="ADMIN_NAME" placeholder="管理员账号">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="ADMIN_PASSWORD">密码</label>
						<div class="controls">
							<input type="text" id="ADMIN_PASSWORD" name="ADMIN_PASSWORD" placeholder="管理员密码">
							<span class="help-block">管理员密码，请设置至少6位以上带字母、数字及符号的密码</span>
						</div>
					</div>
					<h2>网站配置</h2>
					<p>一般不用修改，向导自动获取</p>
					<div class="control-group">
						<label class="control-label" for="ROUTER_DIR">安装目录</label>
						<div class="controls">
							<input type="text" name="ROUTER_DIR" class="span4" id="ROUTER_DIR" value="<?php echo $_DIR ; ?>">
							<span class="help-block">iCMS安装目录，如：http://www.idreamsoft.com/iCMS/ 则安装目录为:iCMS/ 根目录请输入/</span>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="ROUTER_URL">网站URL</label>
						<div class="controls">
							<input type="text" name="ROUTER_URL" class="span4" id="ROUTER_URL" value="<?php echo $_URL ; ?>"/>
						</div>
					</div>
					<div class="form-actions">
						<button type="button" class="btn btn-large btn-primary" id="install_btn" data-loading-text="安装中，请稍候...">下一步</button>
					</div>
				</form>
			</div>
			<div class="well hide step" id="step5">
				<h1>第四步：恭喜您！顺利安装完成。</h1>
				<div style="width: 300px;margin:50px auto;">
					<a href="../admincp.php" class="btn btn-large btn-block btn-success" target="_blank">管理后台 »</a>
					<hr />
					<a href="../index.php" class="btn btn-large btn-block btn-primary" target="_blank">网站首页 »</a>
				</div>
			</div>
		</div>
		<iframe class="hide" id="iCMS_FRAME" name="iCMS_FRAME"></iframe>
		<footer class="footer">
			<div class="container">
				<p>艾梦软件(<a href="http://www.idreamsoft.com" target="_blank">iDreamSoft.com</a>) 版权所有  &copy; 2007-2014</p>
				<p>iCMS 源码受 <a href="https://github.com/idreamsoft/iCMS/blob/master/LICENSE.md" target="_blank">LGPL</a> 开源协议保护</p>
				<ul class="footer-links">
					<li><a href="http://www.idreamsoft.com" target="_blank">iCMS</a></li>
					<li class="muted">·</li>
					<li><a href="http://www.idreamsoft.com/feedback" target="_blank">反馈问题</a></li>
					<li class="muted">·</li>
					<li><a href="http://www.idreamsoft.com/releases" target="_blank">历史版本</a></li>
				</ul>
			</div>
		</footer>
		<script type="text/javascript">
		var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
		document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3F33b8f88e01a95cd0c6f835078ea248d0' type='text/javascript'%3E%3C/script%3E"));
		</script>
	</body>
</html>
