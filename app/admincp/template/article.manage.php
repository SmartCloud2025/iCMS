<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: article.manage.php 2405 2014-04-17 06:59:42Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');
iACP::head();
?>
<script type="text/javascript">
var upordurl="<?php echo APP_URI; ?>&do=updateorder";
$(function(){
	<?php if(isset($_GET['pid']) && $_GET['pid']!='-1'){  ?>
	iCMS.select('pid',"<?php echo (int)$_GET['pid'] ; ?>");
	<?php } ?>
	<?php if($_GET['cid']){  ?>
	iCMS.select('cid',"<?php echo $_GET['cid'] ; ?>");
	<?php } ?>
	<?php if($_GET['st']){ ?>
	iCMS.select('st',"<?php echo $_GET['st'] ; ?>");
	<?php } ?>
	<?php if($_GET['orderby']){ ?>
	iCMS.select('orderby',"<?php echo $_GET['orderby'] ; ?>");
	<?php } ?>
  <?php if($_GET['sub']=="on"){ ?>
  iCMS.checked('#sub');
  <?php } ?>
  <?php if($_GET['scid']=="on"){ ?>
    iCMS.checked('#search_scid');
  <?php } ?>
  iCMS.checked('.spic[value=<?php echo $_GET['pic'] ; ?>]');

	var edialog;
	$(".edit").dblclick(function(){
		var a=$(this),aid=a.attr("aid"),box=$('#ed-box'),title=$.trim(a.text());
		$('#edcid,#edpid').empty();
		var edcid	= $("#cid").clone().show().appendTo('#edcid'),
			edpid	= $("#pid").clone().show().appendTo('#edpid'),
			edtitle	= $('#edtitle',box).val(title),
			edtags	= $('#edtags',box),
			edsource= $('#edsource',box),
			eddesc	= $('#eddesc',box);

		$(".chosen-select",box).chosen({disable_search_threshold: 30});
		$.getJSON("<?php echo APP_URI; ?>",{'do':'getjson','id':aid},function(d){
			edcid.val(d.cid).trigger("chosen:updated");	edpid.val(d.pid).trigger("chosen:updated");
			edtags.val(d.tags);	edsource.val(d.source);
			eddesc.val(d.description);
		});
		if(edialog) edialog.close();

		edialog	= $.dialog({
			lock:true,id:'edialog',title: '简易编辑 ['+title+']',content: document.getElementById('ed-box'),
		    button: [{value: '保存',callback: function () {
						var title=edtitle.val(),cid=edcid.val();
						if(title==""){
							iCMS.alert("请填写标题!");
							edtitle.focus();
							return false;
						}
						if(cid==0){
							iCMS.alert("请选择栏目!");
							return false;
						}
						$(box).trigger("chosen:updated");
						$.post("<?php echo APP_URI; ?>&do=updatetitle",{id:aid,cid:cid,title:title,pid:edpid.val(),source:edsource.val(),tags:edtags.val(),description:eddesc.val()},
						function(o){
							if(o=="1"){
								window.location.reload();
							}
						});
					}}]
		});
	});
	$("#<?php echo APP_FORMID;?>").batch({
    scid:function(){
      return $("#scidBatch").clone(true);
    }
  });
});
</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-search"></i> </span>
      <h5>搜索</h5>
    </div>
    <div class="widget-content">
      <form action="<?php echo __SELF__ ; ?>" method="get" class="form-inline">
        <input type="hidden" name="app" value="<?php echo iACP::$app_name;?>" />
        <input type="hidden" name="do" value="<?php echo iACP::$app_do;?>" />
        <input type="hidden" name="pt" value="<?php echo $_GET['pt'] ; ?>" />
        <input type="hidden" name="userid" value="<?php echo $_GET['userid'] ; ?>" />
        <div class="input-prepend"> <span class="add-on">文章属性</span>
          <select name="pid" id="pid" class="span2 chosen-select">
            <option value="-1">所有文章</option>
            <option value="0">普通文章[pid='0']</option>
            <?php echo iACP::getProp("pid") ; ?>
          </select>
        </div>
        <div class="input-prepend input-append"> <span class="add-on">栏目</span>
          <select name="cid" id="cid" class="chosen-select" style="width: 230px;">
            <option value="0">所有栏目</option>
            <?php echo $category_select = $this->categoryApp->select('cs') ; ?>
          </select>
          <span class="add-on tip" title="选中查询所有关联到此栏目的文章">
          <input type="checkbox" name="scid" id="search_scid"/>
          副栏目 </span>
          <span class="add-on tip" title="选中查询此栏目下面所有的子栏目,包含本栏目">
          <input type="checkbox" name="sub" id="sub"/>
          子栏目 </span> </div>
        <div class="input-prepend"> <span class="add-on">排序</span>
          <select name="orderby" id="orderby" class="span2 chosen-select">
            <option value="">默认排序</option>
            <optgroup label="降序">
            <option value="id DESC">ID[降序]</option>
            <option value="hits DESC">点击[降序]</option>
            <option value="good DESC">顶[降序]</option>
            <option value="postime DESC">时间[降序]</option>
            <option value="pubdate DESC">发布时间[降序]</option>
            <option value="comments DESC">评论[降序]</option>
            </optgroup>
            <optgroup label="升序">
            <option value="id ASC">ID[升序]</option>
            <option value="hits ASC">点击[升序]</option>
            <option value="good ASC">顶[升序]</option>
            <option value="postime ASC">时间[升序]</option>
            <option value="pubdate ASC">发布时间[降序]</option>
            <option value="comments ASC">评论[升序]</option>
            </optgroup>
          </select>
        </div>
        <div class="input-prepend input-append"> <span class="add-on">无缩略图
          <input type="radio" name="pic" class="checkbox spic" value="0"/>
          </span> <span class="add-on">缩略图
          <input type="radio" name="pic" class="checkbox spic" value="1"/>
          </span> </div>
        <div class="clearfloat mb10"></div>
        <div class="input-prepend input-append"><span class="add-on"><i class="fa fa-calendar"></i></span>
          <input type="text" style="width:120px;" class="ui-datepicker" name="starttime" value="<?php echo $_GET['starttime'] ; ?>" placeholder="开始时间" />
          <span class="add-on">-</span>
          <input type="text" style="width:120px;" class="ui-datepicker" name="endtime" value="<?php echo $_GET['endtime'] ; ?>" placeholder="结束时间" />
          <span class="add-on"><i class="fa fa-calendar"></i></span> </div>
        <div class="input-prepend input-append"> <span class="add-on">每页</span>
          <input type="text" name="perpage" id="perpage" value="<?php echo $maxperpage ; ?>" style="width:36px;"/>
          <span class="add-on">条记录</span> </div>
        <div class="input-prepend"> <span class="add-on">查找方式</span>
          <select name="st" id="st" class="chosen-select" style="width:120px;">
            <option value="title">标题</option>
            <option value="tag">标签</option>
            <option value="source">出处</option>
            <option value="id">ID</option>
            <option value="top">置顶权重</option>
            <option value="tkd">标题/关键字/简介</option>
            <option value="pic">缩略图</option>
          </select>
        </div>
        <div class="input-prepend input-append"> <span class="add-on">关键字</span>
          <input type="text" name="keywords" class="span2" id="keywords" value="<?php echo $_GET['keywords'] ; ?>" />
          <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> 搜 索</button>
        </div>
      </form>
    </div>
  </div>
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title"> <span class="icon">
      <input type="checkbox" class="checkAll" data-target="#<?php echo APP_BOXID;?>" title="点击全选/反选"/>
      </span>
      <h5>文章列表</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <table class="table table-bordered table-condensed table-hover">
          <thead>
            <tr>
              <th><i class="fa fa-arrows-v"></i></th>
              <th class="span1">排序</th>
              <th>标题</th>
              <th class="span2">日期</th>
              <th style="width:80px;">栏目</th>
              <th style="width:60px;">编辑</th>
              <th class="span1">信息</th>
              <th style="width:120px;">操作</th>
            </tr>
          </thead>
          <tbody>
            <?php for($i=0;$i<$_count;$i++){
                  $ourl = $rs[$i]['url'];
                  $C    = $this->category[$rs[$i]['cid']];
                  $iurl = iURL::get('article',array($rs[$i],$C));
                  empty($ourl) && $htmlurl = $iurl->path;
                  $rs[$i]['url']           = $iurl->href;
            ?>
            <tr id="id<?php echo $rs[$i]['id'] ; ?>">
              <td><input type="checkbox" name="id[]" value="<?php echo $rs[$i]['id'] ; ?>" /></td>
              <td class="ordernum"><input type="text" name="orderNum[<?php echo $rs[$i]['id'] ; ?>]" value="<?php echo $rs[$i]['orderNum'] ; ?>" aid="<?php echo $rs[$i]['id'] ; ?>"/></td>
              <td><div class="edit" aid="<?php echo $rs[$i]['id'] ; ?>">
                  <?php if($rs[$i]['haspic'])echo '<img src="'.ACP_UI.'/image.gif" align="absmiddle">'?>
                  <a href="<?php echo APP_URI; ?>&do=preview&id=<?php echo $rs[$i]['id'] ; ?>" data-toggle="modal" title="预览"><?php echo $rs[$i]['title'] ; ?></a> </div>
                <div class="row-actions"> <a href="<?php echo __ADMINCP__; ?>=files&indexid=<?php echo $rs[$i]['id'] ; ?>&method=database" class="tip-bottom" title="查看文章使用的图片" target="_blank"><i class="fa fa-picture-o"></i></a>
                  <?php if($rs[$i]['status']!="2"){ ?>
                  <a href="<?php echo __ADMINCP__; ?>=comment&aid=<?php echo $rs[$i]['id'] ; ?>" class="tip-bottom" title="文章评论管理" target="_blank"><i class="fa fa-comment"></i></a>
                  <?php } ?>

                  <!-- <a href="<?php echo __ADMINCP__; ?>=chapter&aid=<?php echo $rs[$i]['id'] ; ?>" class="tip-bottom" title="章节管理" target="_blank"><i class="fa fa-sitemap"></i></a> -->

                  <?php if($rs[$i]['status']=="1"){ ?>
                  <a href="<?php echo __ADMINCP__; ?>=push&do=add&title=<?php echo $rs[$i]['title'] ; ?>&pic=<?php echo $rs[$i]['pic'] ; ?>&url=<?php echo $rs[$i]['url'] ; ?>" class="tip-bottom" title="推送此文章"><i class="fa fa-thumb-tack"></i></a> <a href="<?php echo APP_URI; ?>&do=update&id=<?php echo $rs[$i]['id'] ; ?>&iDT=status:0" class="tip-bottom" target="iPHP_FRAME" title="转为草稿"><i class="fa fa-inbox"></i></a> <a href="<?php echo APP_URI; ?>&do=update&id=<?php echo $rs[$i]['id'] ; ?>&iDT=pubdate:now" class="tip-bottom" target="iPHP_FRAME" title="更新文章时间"><i class="fa fa-clock-o"></i></a>
                  <?php } ?>
                  <?php if($rs[$i]['status']=="0"){ ?>
                  <a href="<?php echo APP_FURI; ?>&do=update&id=<?php echo $rs[$i]['id'] ; ?>&iDT=status:1" class="tip-bottom" target="iPHP_FRAME" title="发布文章"><i class="fa fa-share"></i></a> <a href="<?php echo APP_URI; ?>&do=update&id=<?php echo $rs[$i]['id'] ; ?>&iDT=status:1,pubdate:now" class="tip-bottom" target="iPHP_FRAME" title="更新文章时间,并发布"><i class="fa fa-clock-o"></i></a>
                  <?php } ?>
                  <?php if($rs[$i]['status']=="2"){ ?>
                  <a href="<?php echo APP_FURI; ?>&do=update&id=<?php echo $rs[$i]['id'] ; ?>&iDT=status:1" target="iPHP_FRAME" class="tip-bottom" title="从回收站恢复"/><i class="fa fa-reply-all"></i></a>
                  <?php } ?>
                  <a href="<?php echo APP_URI; ?>&do=purge&id=<?php echo $rs[$i]['id'] ; ?>&url=<?php echo $rs[$i]['url'] ; ?>" class="tip-bottom" data-toggle="modal" title="清除WEB缓存"><i class="fa fa-refresh"></i></a>
                  <?php if ($C['mode'] && strstr($C['contentRule'],'{PHP}')===false && $rs[$i]['status']=="1" && empty($ourl) && iMember::$data->gid==1){  ?>
                  <a href="<?php echo __ADMINCP__; ?>=html&do=createArticle&aid=<?php echo $rs[$i]['id'] ; ?>&frame=iPHP" class="tip-bottom" target="iPHP_FRAME" title="生成静态文件"><i class="fa fa-file"></i></a>
                  <?php } ?>
                </div>
                <?php if($rs[$i]['pic'] && iCMS::$config['publish']['showpic']){ ?>
                <a href="<?php echo APP_URI; ?>&do=preview&id=<?php echo $rs[$i]['id'] ; ?>" data-toggle="modal" title="预览"><img src="<?php echo iFS::fp($rs[$i]['pic']); ?>" style="height:120px;"/></a>
                <?php } ?></td>
              <td><?php if($rs[$i]['pubdate']) echo get_date($rs[$i]['pubdate'],'Y-m-d H:i');?><br />
                <?php if($rs[$i]['postime']) echo get_date($rs[$i]['postime'],'Y-m-d H:i');?></td>
              <td><a href="<?php echo APP_DOURI; ?>&cid=<?php echo $rs[$i]['cid'] ; ?>&<?php echo $uri ; ?>"><?php echo $C['name'] ; ?></a><br />
                <?php echo iACP::getProp("pid",$rs[$i]['pid'],'text',APP_DOURI.'&pid={PID}&'.$uri) ; ?></td>
              <td><a href="<?php echo APP_DOURI; ?>&userid=<?php echo $rs[$i]['userid'] ; ?>&<?php echo $uri ; ?>"><?php echo $rs[$i]['editor'] ; ?></a><br /><?php echo $rs[$i]['author'] ; ?></td>
              <td><?php echo $rs[$i]['hits']; ?>/<?php echo _int($rs[$i]['top']); ?></td>
              <td><?php if($rs[$i]['status']=="1"){ ?>
                <a href="<?php echo $rs[$i]['url']; ?>" class="btn btn-success btn-mini" target="_blank">查看</a>
                <?php } ?>
                <!-- <a href="<?php echo APP_URI; ?>&do=add&id=<?php echo $rs[$i]['id'] ; ?>" class="btn btn-primary btn-mini">+章节</a> -->
                <?php if(iACP::CP($rs[$i]['cid'],'ce')){ ?>
                <a href="<?php echo APP_URI; ?>&do=add&id=<?php echo $rs[$i]['id'] ; ?>" class="btn btn-primary btn-mini">编辑</a>
                <?php } ?>
                <?php if(in_array($rs[$i]['status'],array("1","0")) && iACP::CP($rs[$i]['cid'],'cd')){ ?>
                <a href="<?php echo APP_FURI; ?>&do=update&id=<?php echo $rs[$i]['id'] ; ?>&iDT=status:2" target="iPHP_FRAME" class="del btn btn-danger btn-mini" title="移动此文章到回收站" />删除</a>
                <?php } ?>
                <?php if($rs[$i]['status']=="2"){ ?>
                <a href="<?php echo APP_FURI; ?>&do=del&id=<?php echo $rs[$i]['id'] ; ?>" target="iPHP_FRAME" class="del btn btn-danger btn-mini" onclick="return confirm('确定要删除?');"/>永久删除</a>
                <?php } ?>
              </td>
            </tr>
            <?php }  ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="8"><div class="pagination pagination-right" style="float:right;"><?php echo iPHP::$pagenav ; ?></div>
                <div class="input-prepend input-append mt20"> <span class="add-on">全选
                  <input type="checkbox" class="checkAll checkbox" data-target="#<?php echo APP_BOXID;?>" />
                  </span>
                  <div class="btn-group dropup" id="iCMS-batch"> <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"><i class="fa fa-wrench"></i> 批 量 操 作 </a><a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"> <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                      <li><a data-toggle="batch" data-action="pubdate:now"><i class="fa fa-clock-o"></i> 更新发布时间</a></li>
                      <?php if($doType=="inbox"||$doType=="trash"){ ?>
                      <li><a data-toggle="batch" data-action="status:1"><i class="fa fa-share"></i> 发布</a></li>
                      <li><a data-toggle="batch" data-action="status:1,pubdate:now"><i class="fa fa-clock-o"></i> 发布并更新时间</a></li>
                      <?php } ?>
                      <?php if(!$doType){ ?>
                      <li><a data-toggle="batch" data-action="status:0"><i class="fa fa-inbox"></i> 转为草稿</a></li>
                      <?php } ?>
                      <li class="divider"></li>
                      <li><a data-toggle="batch" data-action="prop"><i class="fa fa-puzzle-piece"></i> 设置文章属性</a></li>
                      <li><a data-toggle="batch" data-action="move"><i class="fa fa-fighter-jet"></i> 移动栏目</a></li>
                      <li><a data-toggle="batch" data-action="scid"><i class="fa fa-code-fork"></i> 设置副栏目</a></li>
                      <li><a data-toggle="batch" data-action="thumb"><i class="fa fa-picture-o"></i> 提取缩略图</a></li>
                      <li><a data-toggle="batch" data-action="top"><i class="fa fa-cog"></i> 设置置顶权重</a></li>
                      <li><a data-toggle="batch" data-action="keyword"><i class="fa fa-star"></i> 设置关键字</a></li>
                      <li><a data-toggle="batch" data-action="tag"><i class="fa fa-tags"></i> 设置标签</a></li>
                      <li><a data-toggle="batch" data-action="order"><i class="fa fa-list-ol"></i> 更新排序</a></li>
                      <li class="divider"></li>
                      <li><a data-toggle="batch" data-action="status:2"><i class="fa fa-trash-o"></i> 移入回收站</a></li>
                      <li><a data-toggle="batch" data-action="dels"><i class="fa fa-trash-o"></i> 永久删除</a></li>
                    </ul>
                  </div>
                </div></td>
            </tr>
          </tfoot>
        </table>
      </form>
    </div>
  </div>
</div>
<div id="ed-box" class="hide">
  <div class="input-prepend"> <span class="add-on">栏 目</span> <span id="edcid"></span> </div>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend"> <span class="add-on">属 性</span> <span id="edpid"></span> </div>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend"> <span class="add-on">标 题</span>
    <input type="text" class="span6" id="edtitle"/>
  </div>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend"> <span class="add-on">出 处</span>
    <input type="text" class="span6" id="edsource"/>
  </div>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend"> <span class="add-on">标 签</span>
    <input type="text" class="span6" id="edtags"/>
  </div>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend"><span class="add-on">摘 要</span>
    <textarea id="eddesc" class="span6" style="height: 120px;"></textarea>
  </div>
</div>
<div class='iCMS-batch'>
  <div id="scidBatch">
    <div class="input-prepend">
      <select name="scid[]" id="scid" class="span3" multiple="multiple"  data-placeholder="请选择副栏目(可多选)...">
        <?php echo $category_select;?>
      </select>
    </div>
  </div>
</div>
<?php iACP::foot();?>
