<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: tags.manage.php 2393 2014-04-09 13:14:23Z coolmoo $
 */
defined('iCMS') OR exit('What are you doing?'); 
iACP::head();
?>
<script type="text/javascript">
var upordurl="<?php echo APP_URI; ?>&do=updateorder";
$(function(){
	<?php if(isset($_GET['pid']) && $_GET['pid']!='-1'){  ?>
	iCMS.select('pid',"<?php echo (int)$_GET['pid'] ; ?>");
	<?php } if($_GET['cid']){  ?>
	iCMS.select('cid',"<?php echo $_GET['cid'] ; ?>");
	<?php } if($_GET['tcid']){  ?>
	iCMS.select('tcid',"<?php echo $_GET['tcid'] ; ?>");
	<?php } if($_GET['orderby']){ ?>
	iCMS.select('orderby',"<?php echo $_GET['orderby'] ; ?>");
	<?php } if($_GET['sub']=="on"){ ?>
	$("#sub").prop("checked",true);
	<?php } if($_GET['tfsub']=="on"){ ?>
	$("#tfsub").prop("checked",true);
	<?php } ?>
	$("#<?php echo APP_FORMID;?>").batch({
		mvtcid: function(){
			var select	= $("#tcid").clone().show()
				.removeClass("chosen-select")
				.attr("id",iCMS.random(3));
			$("option:first",select).remove();
			return select;
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
        <input type="hidden" name="uid" value="<?php echo $_GET['uid'] ; ?>" />
        <div class="input-prepend"> <span class="add-on">标签属性</span>
          <select name="pid" id="pid" class="span2 chosen-select">
            <option value="-1">所有标签</option>
            <?php echo iACP::getProp("pid") ; ?>
          </select>
        </div>
        <div class="input-prepend input-append"> <span class="add-on">栏目</span>
          <select name="cid" id="cid" class="span3 chosen-select">
            <option value="0">所有栏目</option>
            <?php echo $this->category->select(0,0,1,'all') ; ?>
          </select>
          <span class="add-on">
          <input type="checkbox" name="sub" id="sub"/>
          子栏目 </span> </div>
        <div class="input-prepend input-append"> <span class="add-on">分类</span>
          <select name="tcid" id="tcid" class="chosen-select">
            <option value="0">所有分类</option>
            <?php echo $this->tagcategory->select(0,0,1,'all') ; ?>
          </select>
          <span class="add-on">
          <input type="checkbox" name="tfsub" id="tfsub"/>
          子分类 </span> </div>
        <div class="clearfloat mb10"></div>
        <div class="input-prepend input-append"><span class="add-on"><i class="fa fa-calendar"></i></span>
          <input type="text" class="span2 ui-datepicker" name="starttime" value="<?php echo $_GET['starttime'] ; ?>" placeholder="开始时间" />
          <span class="add-on">-</span>
          <input type="text" class="span2 ui-datepicker" name="endtime" value="<?php echo $_GET['endtime'] ; ?>" placeholder="结束时间" />
          <span class="add-on"><i class="fa fa-calendar"></i></span> </div>
        <div class="input-prepend"> <span class="add-on">排序</span>
          <select name="orderby" id="orderby" class="span2 chosen-select">
            <option value="">默认排序</option>
            <optgroup label="降序">
            <option value="id DESC">ID[降序]</option>
            <option value="pubdate DESC">时间[降序]</option>
            <option value="`count` DESC">使用数[降序]</option>
            </optgroup>
            <optgroup label="升序">
            <option value="id ASC">ID[升序]</option>
            <option value="pubdate ASC">时间[升序]</option>
            <option value="`count` ASC">使用数[升序]</option>
            </optgroup>
          </select>
        </div>
        <div class="input-prepend input-append"> <span class="add-on">每页</span>
          <input type="text" name="perpage" id="perpage" value="<?php echo $_GET['perpage']?$_GET['perpage']:20 ; ?>" style="width:36px;"/>
          <span class="add-on">条记录</span> </div>
        <div class="input-prepend input-append"> <span class="add-on">无缩略图
          <input type="checkbox" name="nopic" id="nopic"/>
          </span> <span class="add-on">缩略图
          <input type="checkbox" name="haspic" id="haspic"/>
          </span> </div>
        <div class="clearfloat mb10"></div>
        <div class="input-prepend input-append"> <span class="add-on">关键字</span>
          <input type="text" name="keywords" class="span2" id="keywords" value="<?php echo $_GET['keywords'] ; ?>" />
          <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> 搜 索</button>
        </div>
      </form>
    </div>
  </div>
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title"> <span class="icon">
      <input type="checkbox" class="checkAll" data-target="#<?php echo APP_BOXID;?>" />
      </span>
      <h5>标签列表</h5>
    </div>
    <div class="widget-content nopadding">
    <form action="<?php echo APP_FURI; ?>&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
      <table class="table table-bordered table-condensed table-hover">
        <thead>
          <tr>
            <th><i class="fa fa-arrows-v"></i></th>
            <th>ID</th>
            <th>排序</th>
            <th>标签</th>
            <th>栏目</th>
            <th>分类</th>
            <th>属性</th>
            <th style="width:28px;">使用</th>
            <th class="span2">最后更新时间</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <?php for($i=0;$i<$_count;$i++){
              $C             = $this->category->category[$rs[$i]['cid']];
              $TC            = $this->tagcategory->category[$rs[$i]['tcid']];
              $iurl          = iURL::get('tag',array($rs[$i],$C,$TC));
              $rs[$i]['url'] = $iurl->href;
    	   ?>
          <tr id="tr<?php echo $rs[$i]['id'] ; ?>">
            <td><input type="checkbox" name="id[]" value="<?php echo $rs[$i]['id'] ; ?>" /></td>
            <td><?php echo $rs[$i]['id'] ; ?></td>
            <td class="ordernum"><input type="text" name="ordernum[<?php echo $rs[$i]['id'] ; ?>]" value="<?php echo $rs[$i]['ordernum'] ; ?>" tid="<?php echo $rs[$i]['id'] ; ?>"/></td>
            <td><?php if($rs[$i]['ispic'])echo '<img src="'.ACP_UI.'/image.gif" align="absmiddle">';?>
              <a href="<?php echo $rs[$i]['url'] ; ?>" class="noneline" target="_blank"><?php echo $rs[$i]['name'] ; ?></a>
          </div>
        
        <?php if($rs[$i]['ispic']){ ?>
        <a href="<?php echo APP_URI; ?>&do=preview&id=<?php echo $rs[$i]['id'] ; ?>" data-toggle="modal" title="预览"><img src="<?php echo iCMS::$config['FS']['url'] ; ?>/<?php echo $rs[$i]['pic'] ; ?>" style="height:120px;"/></a>
        <?php } ?>
          </td>
          <td><a href="<?php echo APP_DOURI; ?>&cid=<?php echo $rs[$i]['cid'] ; ?><?php echo $uri ; ?>"><?php echo $C['name'] ; ?></a></td>
          <td><a href="<?php echo APP_DOURI; ?>&tcid=<?php echo $rs[$i]['tcid'] ; ?><?php echo $uri ; ?>"><?php echo $TC['name'] ; ?></a></td>
          <td><a href="<?php echo APP_DOURI; ?>&pid=<?php echo $rs[$i]['pid'] ; ?><?php echo $uri ; ?>"><?php echo iACP::getProp("pid",$rs[$i]['pid'],'text') ; ?></a></td>
          <td><?php echo $rs[$i]['count']; ?></td>
          <td><?php echo get_date($rs[$i]['pubdate'],'Y-m-d H:i');?></td>
          <td>
          	<?php if($rs[$i]['status']=="1"){ ?>
            <a href="<?php echo APP_FURI; ?>&do=update&id=<?php echo $rs[$i]['id'] ; ?>&iDT=status:0" class="btn btn-small btn-danger tip" target="iPHP_FRAME" title="当前状态:启用,点击可禁用此标签"><i class="fa fa-power-off"></i> 禁用</a>
            <a href="<?php echo __ADMINCP__; ?>=keywords&do=add&keyword=<?php echo $rs[$i]['name'] ; ?>&url=<?php echo $rs[$i]['url'] ; ?>" class="btn btn-small"><i class="fa fa-paperclip"></i> 内链</a>
            <a href="<?php echo APP_FURI; ?>&do=cache&id=<?php echo $rs[$i]['id'] ; ?>" class="btn btn-small" target="iPHP_FRAME"><i class="fa fa-refresh"></i> 更新缓存</a> 
            <?php } ?>
            <?php if($rs[$i]['status']=="0"){ ?>
            <a href="<?php echo APP_FURI; ?>&do=update&id=<?php echo $rs[$i]['id'] ; ?>&iDT=status:1" class="btn btn-small btn-success tip " target="iPHP_FRAME" title="当前状态:禁用,点击可启用此标签"><i class="fa fa-play-circle"></i> 启用</a>
            <?php } ?>
             <a href="<?php echo APP_URI; ?>&do=add&id=<?php echo $rs[$i]['id'] ; ?>" class="btn btn-small"><i class="fa fa-edit"></i> 编辑</a> <a href="<?php echo APP_FURI; ?>&do=del&id=<?php echo $rs[$i]['id'] ; ?>" target="iPHP_FRAME" class="del btn btn-small" title='永久删除'  onclick="return confirm('确定要删除?');"/><i class="fa fa-trash-o"></i> 永久删除</a></td>
        </tr>
        <?php }  ?>
          </tbody>
        
        <tfoot>
          <tr>
            <td colspan="10"><div class="pagination pagination-right" style="float:right;"><?php echo iPHP::$pagenav ; ?></div>
              <div class="input-prepend input-append mt20"> <span class="add-on">全选
                <input type="checkbox" class="checkAll checkbox" data-target="#<?php echo APP_BOXID;?>" />
                </span>
                <div class="btn-group dropup" id="batch"> <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"><i class="fa fa-wrench"></i> 批 量 操 作 </a><a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"> <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                    <li><a data-toggle="batch" data-action="status:1"><i class="fa fa-play-circle"></i> 启用</a></li>
                    <li><a data-toggle="batch" data-action="status:0"><i class="fa fa-power-off"></i> 禁用</a></li>
                    <li class="divider"></li>
                    <li><a data-toggle="batch" data-action="move"><i class="fa fa-fighter-jet"></i> 移动栏目</a></li>
                    <li><a data-toggle="batch" data-action="mvtcid"><i class="fa fa-fighter-jet"></i> 移动分类</a></li>
                    <li><a data-toggle="batch" data-action="prop"><i class="fa fa-puzzle-piece"></i> 设置属性</a></li>
                    <li><a data-toggle="batch" data-action="top"><i class="fa fa-cog"></i> 设置权重</a></li>
                    <li><a data-toggle="batch" data-action="keyword"><i class="fa fa-star"></i> 设置关键字</a></li>
                    <li><a data-toggle="batch" data-action="tag"><i class="fa fa-tags"></i> 设置相关标签</a></li>
                    <li class="divider"></li>
                    <li><a data-toggle="batch" data-action="dels"><i class="fa fa-trash-o"></i> 删除</a></li>
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
<div id="iCMS_mdiv" style="display:none;"> </div>
<?php iACP::foot();?>
