<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: project.manage.php 738 2013-04-07 11:27:17Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');
iACP::head();
?>
<script type="text/javascript">
$(function(){
	<?php if($_GET['cid']){  ?>
	iCMS.select('cid',"<?php echo $_GET['cid'] ; ?>");
	<?php } ?>
	<?php if($_GET['sub']=="on"){ ?>
	$("#sub").prop("checked",true);
	<?php } ?>
	$("#<?php echo APP_FORMID;?>").batch();
});
</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-search"></i> </span>
      <h5>搜索</h5>
    </div>
    <div class="widget-content">
      <form action="<?php echo __SELF__; ?>" method="get" class="form-inline">
        <input type="hidden" name="app" value="<?php echo iACP::$app_name;?>" />
        <input type="hidden" name="do" value="<?php echo iACP::$app_do;?>" />
        <div class="input-prepend input-append"> <span class="add-on">栏目</span>
          <select name="cid" id="cid" class="span3 chosen-select">
            <option value="0">所有栏目</option>
            <?php echo $categoryApp->select(); ?>
          </select>
          <span class="add-on">
          <input type="checkbox" name="sub" id="sub"/>
          子栏目 </span> </div>
        <div class="input-prepend input-append"> <span class="add-on">每页</span>
          <input type="text" name="perpage" id="perpage" value="<?php echo $_GET['perpage'] ? $_GET['perpage'] : 20; ?>" style="width:36px;"/>
          <span class="add-on">条记录</span> </div>
        <div class="input-prepend input-append"> <span class="add-on">关键字</span>
          <input type="text" name="keywords" class="span2" id="keywords" value="<?php echo $_GET['keywords']; ?>" />
          <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> 搜 索</button>
        </div>
      </form>
    </div>
  </div>
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title"> <span class="icon">
      <input type="checkbox" class="checkAll" data-target="#<?php echo APP_BOXID;?>" />
      </span>
      <h5>方案列表</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <table class="table table-bordered table-condensed table-hover">
          <thead>
            <tr>
              <th><i class="fa fa-arrows-v"></i></th>
              <th>ID</th>
              <th>名称</th>
              <th>规则</th>
              <th>栏目</th>
              <th>发布</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <?php for ($i = 0; $i < $_count; $i++) {
                    $C = $category[$rs[$i]['cid']];
		?>
            <tr id="tr<?php echo $rs[$i]['id']; ?>">
              <td><input type="checkbox" name="id[]" value="<?php echo $rs[$i]['id']; ?>" /></td>
              <td><?php echo $rs[$i]['id']; ?></td>
              <td><a href="<?php echo APP_URI; ?>&do=inbox&pid=<?php echo $rs[$i]['id']; ?>"><?php echo $rs[$i]['name']; ?></a></td>
              <td><a href="<?php echo APP_URI; ?>&do=project&rid=<?php echo $rs[$i]['rid']; ?>&<?php echo $uri; ?>"><?php echo $ruleArray[$rs[$i]['rid']]; ?></a></td>
              <td><a href="<?php echo APP_URI; ?>&do=project&cid=<?php echo $rs[$i]['cid']; ?>&<?php echo $uri; ?>"><?php echo $C['name']; ?></a></td>
              <td><a href="<?php echo APP_URI; ?>&do=project&poid=<?php echo $rs[$i]['poid']; ?>&<?php echo $uri; ?>"><?php echo $postArray[$rs[$i]['poid']]; ?></a></td>
              <td><a href="<?php echo APP_FURI; ?>&do=copyproject&pid=<?php echo $rs[$i]['id']; ?>" class="btn btn-small" target="iPHP_FRAME"><i class="fa fa-copy"></i> 复制</a> <a href="<?php echo APP_URI; ?>&do=testrule&pid=<?php echo $rs[$i]['id']; ?>" class="btn btn-small" data-toggle="modal" title="测试规则"><i class="fa fa-keyboard-o"></i> 测试</a> <a href="<?php echo APP_URI; ?>&do=listpub&pid=<?php echo $rs[$i]['id']; ?>" class="btn btn-small btn-primary" data-toggle="modal" title="采集列表,手动发布"><i class="fa fa-hand-o-up"></i> 手动采集</a> <a href="<?php echo APP_FURI; ?>&do=start&pid=<?php echo $rs[$i]['id']; ?>" class="btn btn-small btn-danger tip" target="iPHP_FRAME" title="自动采集列表,并发布"><i class="fa fa-play"></i> 采集</a> <a href="<?php echo APP_URI; ?>&do=addproject&pid=<?php echo $rs[$i]['id']; ?>" class="btn btn-small"><i class="fa fa-edit"></i> 编辑</a> <a href="<?php echo APP_FURI; ?>&do=dropurl&pid=<?php echo $rs[$i]['id']; ?>&type=all" class="btn btn-small" target="iPHP_FRAME"><i class="fa fa-trash-o"></i> 清空数据</a> <a href="<?php echo APP_FURI; ?>&do=dropurl&pid=<?php echo $rs[$i]['id']; ?>&type=0" class="btn btn-small" target="iPHP_FRAME"><i class="fa fa-inbox"></i> 清除未发</a> <a href="<?php echo APP_FURI; ?>&do=delproject&pid=<?php echo $rs[$i]['id']; ?>" target="iPHP_FRAME" class="del btn btn-small" title='永久删除'  onclick="return confirm('确定要删除?');"/><i class="fa fa-trash-o-sign"></i> 删除</a></td>
            </tr>
            <?php } ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="7"><div class="pagination pagination-right" style="float:right;"><?php echo iPHP::$pagenav; ?></div>
                <div class="input-prepend input-append mt20"> <span class="add-on">全选
                  <input type="checkbox" class="checkAll checkbox" data-target="#<?php echo APP_BOXID;?>" />
                  </span>
                  <div class="btn-group dropup" id="batch"> <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"><i class="fa fa-wrench"></i> 批 量 操 作 </a><a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"> <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                      <li><a data-toggle="batch" data-action="delproject"><i class="fa fa-trash-o"></i> 删除</a></li>
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
<?php iACP::foot(); ?>
