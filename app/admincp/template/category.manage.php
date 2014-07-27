<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: category.manage.php 2381 2014-03-21 04:03:07Z coolmoo $
 */
defined('iCMS') OR exit('What are you doing?'); 
iPHP::set_cookie(iACP::$app_name.'_tabs',iACP::$app_do);
iACP::head();
?>
<?php if(iACP::$app_do=='tree'){ ?>
<link rel="stylesheet" href="<?php echo ACP_UI;?>/jquery/treeview-0.1.0.css" type="text/css" />
<link rel="stylesheet" href="<?php echo ACP_UI;?>/jquery/ui-1.10.3.min.css" type="text/css" />
<script type="text/javascript" src="<?php echo ACP_UI;?>/jquery/ui-1.10.3.min.js"></script>
<script type="text/javascript" src="<?php echo ACP_UI;?>/jquery/treeview-0.1.0.js"></script>
<script type="text/javascript" src="<?php echo ACP_UI;?>/jquery/treeview-0.1.0.async.js"></script>
<script type="text/javascript">
var upordurl="<?php echo APP_URI; ?>&do=updateorder";
$(function(){
    $("#tree").treeview({
    	url:'<?php echo APP_URI; ?>&do=ajaxtree&hasChildren=<?php echo iACP::$app_do=='all'?'1':'0';?>',
        collapsed: false,
        animated: "medium",
        control:"#treecontrol",
    }).sortable({
        helper: "clone",
        placeholder: "ui-state-highlight",
        delay: 100,
        start: function(event, ui) {
            $(ui.item).show().css({'opacity': 0.5});
        },
        stop: function(event, ui) {
            $(ui.item).css({'opacity': 1});
            var pt = ui.item.parent();
            var ord = $(".ordernum > input",pt);
            var ordernum = new Array();
            ord.each(function(i) {
                $(this).val(i);
            	var id = $(this).attr("data-id");
            	ordernum.push(id);
            });
            $.post(upordurl,{ordernum: ordernum}); 
        }
    }).disableSelection();
});
</script>
<?php } ?>
<?php if(iACP::$app_do=='list'){ ?>
<script type="text/javascript">
$(function(){
<?php if($_GET['st']){ ?>
iCMS.select('st',"<?php echo $_GET['st'] ; ?>");
<?php } ?>
  $("#<?php echo APP_FORMID;?>").batch({
    move:function(){
      return $("#mergeBatch").clone(true);
    }
  });
});
</script>
<?php } ?>
<div class="iCMS-container">
  <?php if(iACP::$app_do=='list'){ ?>
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-search"></i> </span>
      <h5>搜索</h5>
    </div>
    <div class="widget-content">
      <form action="<?php echo __SELF__ ; ?>" method="get" class="form-inline">
        <input type="hidden" name="app" value="<?php echo iACP::$app_name;?>" />
        <input type="hidden" name="do" value="<?php echo iACP::$app_do;?>" />
        <div class="input-prepend input-append"> <span class="add-on">每页</span>
          <input type="text" name="perpage" id="perpage" value="<?php echo $_GET['perpage']?$_GET['perpage']:20 ; ?>" style="width:36px;"/>
          <span class="add-on">条记录</span> </div>
        <div class="input-prepend"> <span class="add-on">查找方式</span>
          <select name="st" id="st" class="chosen-select" style="width:120px;">
            <option value="name"><?php echo $this->name_text;?>名</option>
            <option value="cid">CID</option>
            <option value="tkd">标题/关键字/简介</option>
          </select>
        </div>
        <div class="input-prepend input-append"> <span class="add-on">关键字</span>
          <input type="text" name="keywords" class="span2" id="keywords" value="<?php echo $_GET['keywords'] ; ?>" />
          <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> 搜 索</button>
        </div>
      </form>
    </div>
  </div>
  <?php } ?>
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-list"></i> </span>
      <ul class="nav nav-tabs" id="category-tab">
        <li<?php if(iACP::$app_do=='tree'){ ?> class="active" <?php } ?>><a href="<?php echo APP_URI; ?>&do=tree"><i class="fa fa-tasks"></i> 树模式</a></li>
        <li<?php if(iACP::$app_do=='list'){ ?> class="active" <?php } ?>><a href="<?php echo APP_URI; ?>&do=list"><i class="fa fa-list"></i> 列表模式</a></li>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <?php if(iACP::$app_do=='tree'){ ?>
      <form action="<?php echo APP_FURI; ?>&do=update" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <div id="category-list" class="tab-content">
          <div id="category-tree" class="row-fluid category-treeview">
            <ul id="tree">
            </ul>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
        </div>
      </form>
      <?php } ?>
      <?php if(iACP::$app_do=='list'){ ?>
      <form action="<?php echo APP_FURI; ?>&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <table class="table table-bordered table-condensed table-hover">
          <thead>
            <tr>
              <th style="width:10px;"><i class="fa fa-arrows-v"></i></th>
              <th style="width:24px;">cid</th>
              <th>pid</th>
              <th style="width:40px;">rootid</th>
              <th class="span4"><?php echo $this->name_text;?></th>
              <th style="width:40px;">记录数</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <?php for($i=0;$i<$_count;$i++){?>
            <tr id="tr<?php echo $rs[$i]['cid'] ; ?>" class="status<?php echo $rs[$i]['status'] ; ?>">
              <td><input type="checkbox" name="id[]" value="<?php echo $rs[$i]['cid'] ; ?>" /></td>
              <td><?php echo $rs[$i]['cid'] ; ?></td>
              <td><?php echo $rs[$i]['pid'] ; ?></td>
              <td><?php echo $rs[$i]['rootid'] ; ?></td>
              <td><input <?php if($rs[$i]['rootid']=="0"){ ?> style="font-weight:bold"<?php } ?> type="text" name="name[<?php echo $rs[$i]['cid'] ; ?>]" value="<?php echo $rs[$i]['name'] ; ?>">
                <?php if(!$rs[$i]['status']){ ?>
                <i class="fa fa-eye-slash" title="隐藏<?php echo $this->name_text;?>"></i>
                <?php } ?></td>
              <td><?php echo $rs[$i]['count'] ; ?></td>
              <td><?php echo $this->listbtn($rs[$i]) ; ?> <a href="<?php echo APP_URI; ?>&do=add&rootid=<?php echo $rs[$i]['cid'] ; ?>" class="btn btn-small"><i class="fa fa-plus-square"></i> 添加子<?php echo $this->name_text;?></a> <a href="<?php echo APP_URI; ?>&do=add&cid=<?php echo $rs[$i]['cid'] ; ?>" class="btn btn-small"><i class="fa fa-edit"></i> 编辑</a> <a href="<?php echo APP_FURI; ?>&do=del&cid=<?php echo $rs[$i]['cid'] ; ?>" target="iPHP_FRAME" class="del btn btn-small" title='永久删除'  onclick="return confirm('确定要删除?');"/><i class="fa fa-trash-o"></i> 删除</a></td>
            </tr>
            <?php }  ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="8"><div class="pagination pagination-right" style="float:right;"><?php echo iPHP::$pagenav ; ?></div>
                <div class="input-prepend input-append mt20"> <span class="add-on">全选
                  <input type="checkbox" class="checkAll checkbox" data-target="#<?php echo APP_BOXID;?>" />
                  </span>
                  <div class="btn-group" id="batch"> <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"><i class="fa fa-wrench"></i> 批 量 操 作 </a><a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"> <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                      <li><a data-toggle="batch" data-action="merge"><i class="fa fa-random"></i> 合并<?php echo $this->name_text;?></a></li>
                      <li><a data-toggle="batch" data-action="move"><i class="fa fa-fighter-jet"></i> 移动<?php echo $this->name_text;?></a></li>
                      <li><a data-toggle="batch" data-action="recount"><i class="fa fa-refresh"></i> 更新记录数</a></li>
                      <li><a data-toggle="batch" data-action="name"><i class="fa fa-info-circle"></i> 更新名称</a></li>
                      <li><a data-toggle="batch" data-action="status"><i class="fa fa-square"></i> <?php echo $this->name_text;?>状态</a></li>
                      <?php echo $this->batchbtn();?>
                      <li class="divider"></li>
                      <li><a data-toggle="batch" data-action="dels"><i class="fa fa-trash-o"></i> 删除</a></li>
                    </ul>
                  </div>
                </div></td>
            </tr>
          </tfoot>
        </table>
      </form>
      <div class='iCMS-batch'>
        <div id="modeBatch">
          <div class="input-prepend"> <span class="add-on">访问模式</span>
            <select name="mode">
              <option value="0">动态</option>
              <option value="1">静态</option>
              <option value="2">伪静态</option>
            </select>
          </div>
        </div>
        <div id="mergeBatch">
          <div class="input-prepend"> <span class="add-on">请选择目标<?php echo $this->name_text;?></span>
            <select name="tocid" class="span3">
              <option value="0">===顶级<?php echo $this->name_text;?>===</option>
              <?php echo $this->category->select(0,0,1,'all',true);?>
            </select>
          </div>
        </div>
        <div id="statusBatch">
          <div class="switch" data-on-label="显示" data-off-label="隐藏">
            <input type="checkbox" data-type="switch" name="status" id="status"/>
          </div>
        </div>
        <div id="categoryRuleBatch">
          <div class="input-append" style="margin-right: 70px;">
            <input type="text" name="categoryRule" class="span4" id="categoryRule" value="{CDIR}/index{EXT}">
            <div class="btn-group"> <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"><i class="fa fa-question-circle"></i> 帮助</a>
              <ul class="dropdown-menu">
                <li><a href="{CID}" data-toggle="insertContent" data-target="#categoryRule"><span class="label label-important">{CID}</span> <?php echo $this->name_text;?>ID</a></li>
                <li><a href="{CDIR}" data-toggle="insertContent" data-target="#categoryRule"><span class="label label-important">{CDIR}</span> <?php echo $this->name_text;?>目录</a></li>
                <li><a href="{0xCID}" data-toggle="insertContent" data-target="#categoryRule"><span class="label label-inverse">{0xCID}</span> <?php echo $this->name_text;?>ID补零（8位）</a></li>
                <li class="divider"></li>
                <li><a href="{MD5}" data-toggle="insertContent" data-target="#categoryRule"><span class="label label-inverse">{MD5}</span> 文章ID(16位)</a></li>
                <li class="divider"></li>
                <li><a href="{P}" data-toggle="insertContent" data-target="#categoryRule"><span class="label label-inverse">{P}</span> 分页数</a></li>
                <li><a href="{EXT}" data-toggle="insertContent" data-target="#categoryRule"><span class="label label-inverse">{EXT}</span> 后缀</a></li>
                <li class="divider"></li>
                <li><a href="{PHP}" data-toggle="insertContent" data-target="#categoryRule"><span class="label label-inverse">{PHP}</span> 动态程序</a></li>
              </ul>
            </div>
          </div>
          <span class="help-inline">伪静态模式时规则一定要包含<span class="label label-important">{CID}</span>或<span class="label label-important">{CDIR}</span>或直接填写URL</span> </div>
        <div id="contentRuleBatch">
          <div class="input-append" style="margin-right: 70px;">
            <input type="text" name="contentRule" class="span5" value=""/>
            <div class="btn-group"> <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"><i class="fa fa-question-circle"></i> 帮助</a>
              <ul class="dropdown-menu">
                <li><a href="{CID}" data-toggle="insertContent" data-target="#contentRule"><span class="label label-inverse">{CID}</span> <?php echo $this->name_text;?>ID</a></li>
                <li><a href="{CDIR}" data-toggle="insertContent" data-target="#contentRule"><span class="label label-inverse">{CDIR}</span> <?php echo $this->name_text;?>目录</a></li>
                <li><a href="{FPDIR}" data-toggle="insertContent" data-target="#contentRule"><span class="label label-inverse">{FPDIR}</span> <?php echo $this->name_text;?>目录(含父目录)</a></li>
                <li class="divider"></li>
                <li><a href="{YYYY}" data-toggle="insertContent" data-target="#contentRule"><span class="label label-inverse">{YYYY}</span> 4位数年份2012</a></li>
                <li><a href="{YY}" data-toggle="insertContent" data-target="#contentRule"><span class="label label-inverse">{YY}</span> 2位数年份12</a></li>
                <li><a href="{MM}" data-toggle="insertContent" data-target="#contentRule"><span class="label label-inverse">{MM}</span> 月份 01-12月份</a></li>
                <li><a href="{M}" data-toggle="insertContent" data-target="#contentRule"><span class="label label-inverse">{M}</span> 月份 1-12 月份</a></li>
                <li><a href="{DD}" data-toggle="insertContent" data-target="#contentRule"><span class="label label-inverse">{DD}</span> 日期 01-31</a></li>
                <li><a href="{D}" data-toggle="insertContent" data-target="#contentRule"><span class="label label-inverse">{D}</span> 日期1-31</a></li>
                <li><a href="{TIME}" data-toggle="insertContent" data-target="#contentRule"><span class="label label-inverse">{TIME}</span> 文章发布时间戳</a></li>
                <li class="divider"></li>
                <li><a href="{ID}" data-toggle="insertContent" data-target="#contentRule"><span class="label label-important">{ID}</span> 文章ID</a></li>
                <li><a href="{0xID}" data-toggle="insertContent" data-target="#contentRule"><span class="label label-important">{0xID}</span> 文章ID补零（8位）</a></li>
                <li><a href="{0x3ID}" data-toggle="insertContent" data-target="#contentRule"><span class="label label-inverse">{0x3ID}</span> 文章ID补零(8位前3位)</a></li>
                <li><a href="{0x3,2ID}" data-toggle="insertContent" data-target="#contentRule"><span class="label label-inverse">{0x3,2ID}</span> 文章ID补零(8位从第3位起两位)</a></li>
                <li class="divider"></li>
                <li><a href="{TITLE}" data-toggle="insertContent" data-target="#contentRule"><span class="label label-inverse">{TITLE}</span> 文章标题</a></li>
                <li><a href="{LINK}" data-toggle="insertContent" data-target="#contentRule"><span class="label label-inverse">{LINK}</span> 文章自定义链接</a></li>
                <li><a href="{MD5}" data-toggle="insertContent" data-target="#contentRule"><span class="label label-inverse">{MD5}</span> 文章ID(16位)</a></li>
                <li class="divider"></li>
                <li><a href="{P}" data-toggle="insertContent" data-target="#contentRule"><span class="label label-inverse">{P}</span> 分页数</a></li>
                <li><a href="{EXT}" data-toggle="insertContent" data-target="#contentRule"><span class="label label-inverse">{EXT}</span> 后缀</a></li>
                <li class="divider"></li>
                <li><a href="{PHP}" data-toggle="insertContent" data-target="#contentRule"><span class="label label-inverse">{PHP}</span> 动态程序</a></li>
              </ul>
            </div>
          </div>
          <span class="help-inline">伪静态模式时规则一定要包含<span class="label label-important">{ID}</span>或<span class="label label-important">{0xID}</span></span> </div>
        <div id="urlRuleBatch">
          <div class="input-append" style="margin-right: 70px;">
            <input type="text" name="urlRule" class="span5" value=""/>
            <div class="btn-group"> <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"><i class="fa fa-question-circle"></i> 帮助</a>
              <ul class="dropdown-menu">
                <li><a href="{ID}" data-toggle="insertContent" data-target="#urlRule"><span class="label label-important">{ID}</span> 标签ID</a></li>
                <li><a href="{TKEY}" data-toggle="insertContent" data-target="#urlRule"><span class="label label-important">{TKEY}</span> 标签标识</a></li>
                <li><a href="{ZH_CN}" data-toggle="insertContent" data-target="#urlRule"><span class="label label-important">{ZH_CN}</span> 标签名(中文)</a></li>
                <li><a href="{NAME}" data-toggle="insertContent" data-target="#urlRule"><span class="label label-important">{NAME}</span> 标签名</a></li>
                <li class="divider"></li>
                <li><a href="{TCID}" data-toggle="insertContent" data-target="#urlRule"><span class="label label-inverse">{TCID}</span> 分类ID</a></li>
                <li><a href="{TCDIR}" data-toggle="insertContent" data-target="#urlRule"><span class="label label-inverse">{TCDIR}</span> 分类目录</a></li>
                <li><a href="{CDIR}" data-toggle="insertContent" data-target="#urlRule"><span class="label label-inverse">{CDIR}</span> <?php echo $this->name_text;?>目录</a></li>
                <li class="divider"></li>
                <li><a href="{P}" data-toggle="insertContent" data-target="#urlRule"><span class="label label-inverse">{P}</span> 分页数</a></li>
                <li><a href="{EXT}" data-toggle="insertContent" data-target="#urlRule"><span class="label label-inverse">{EXT}</span> 后缀</a></li>
                <li class="divider"></li>
                <li><a href="{PHP}" data-toggle="insertContent" data-target="#urlRule"><span class="label label-inverse">{PHP}</span> 动态程序</a></li>
              </ul>
            </div>
          </div>
          <span class="help-inline">用于标签等其它应用</span> </div>
        <div id="indexTPLBatch">
          <div class="input-append">
            <input type="text" name="indexTPL" class="span3" id="indexTPL" value=""/>
            <a href="<?php echo __ADMINCP__; ?>=files&do=seltpl&from=modal&click=file&target=indexTPL" class="btn" data-toggle="modal" title="选择模板文件"><i class="fa fa-search"></i> 选择</a> </div>
        </div>
        <div id="listTPLBatch">
          <div class="input-append">
            <input type="text" name="listTPL" class="span3" id="listTPL" value="<?php echo $rs['listTPL'] ; ?>"/>
            <a href="<?php echo __ADMINCP__; ?>=files&do=seltpl&from=modal&click=file&target=listTPL" class="btn" data-toggle="modal" title="选择模板文件"><i class="fa fa-search"></i> 选择</a> </div>
        </div>
        <div id="contentTPLBatch">
          <div class="input-append">
            <input type="text" name="contentTPL" class="span3" id="contentTPL" value="<?php echo $rs['contentTPL'] ; ?>"/>
            <a href="<?php echo __ADMINCP__; ?>=files&do=seltpl&from=modal&click=file&target=contentTPL" class="btn" data-toggle="modal" title="选择模板文件"><i class="fa fa-search"></i> 选择</a> </div>
        </div>
      </div>
      <?php } ?>
    </div>
  </div>
</div>
<?php iACP::foot();?>
