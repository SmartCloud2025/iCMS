<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: files.manage.php 179 2013-03-29 03:21:28Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');
iACP::head();
?>
<script type="text/javascript">
$(function(){
	<?php if($_GET['st']){ ?>
	iCMS.select('st',"<?php echo $_GET['st'] ; ?>");
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
      <form action="<?php echo __SELF__ ; ?>" method="get" class="form-inline">
        <input type="hidden" name="app" value="<?php echo iACP::$app_name;?>" />
        <input type="hidden" name="indexid" value="<?php echo $_GET['indexid'] ; ?>" />
        <input type="hidden" name="userid" value="<?php echo $_GET['userid'] ; ?>" />
        <div class="input-prepend input-append"><span class="add-on"><i class="fa fa-calendar"></i></span>
          <input type="text" class="span2 ui-datepicker" name="starttime" value="<?php echo $_GET['starttime'] ; ?>" placeholder="开始时间" />
          <span class="add-on">-</span>
          <input type="text" class="span2 ui-datepicker" name="endtime" value="<?php echo $_GET['endtime'] ; ?>" placeholder="结束时间" />
          <span class="add-on"><i class="fa fa-calendar"></i></span> </div>
        <div class="input-prepend"> <span class="add-on">查找方式</span>
          <select name="st" id="st" class="span2 chosen-select">
            <option value="filename">文件名</option>
            <option value="indexid">关联ID</option>
            <option value="userid">用户ID</option>
            <option value="ofilename">源文件</option>
            <option value="size">文件大小</option>
          </select>
        </div>
        <div class="clearfloat mb10"></div>
        <div class="input-prepend input-append"> <span class="add-on">关键字</span>
          <input type="text" name="keywords" class="span2" id="keywords" value="<?php echo $_GET['keywords'] ; ?>" />
          <span class="add-on">每页</span>
          <input type="text" name="perpage" id="perpage" value="<?php echo $maxperpage ; ?>" style="width:36px;"/>
          <span class="add-on">条记录</span>
          <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> 搜 索</button>
        </div>
      </form>
    </div>
  </div>
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title"> <span class="icon">
      <input type="checkbox" class="checkAll" data-target="#<?php echo APP_BOXID;?>" />
      </span>
      <h5>文件列表</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <table class="table table-bordered table-condensed table-hover">
          <thead>
            <tr>
              <th><i class="fa fa-arrows-v"></i></th>
              <th>ID</th>
              <th style="width:50px;">关联ID</th>
              <th style="width:50px;">用户ID</th>
              <th >路径</th>
              <th style="width:80px;">文件大小</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <?php for($i=0;$i<$_count;$i++){
              $filepath = $rs[$i]['path'].$rs[$i]['filename'].'.'.$rs[$i]['ext'];
              $href     = iFS::fp($filepath,"+http");
            ?>
            <tr id="tr<?php echo $rs[$i]['id'] ; ?>">
              <td><input type="checkbox" name="id[]" value="<?php echo $rs[$i]['id'] ; ?>" /></td>
              <td><?php echo $rs[$i]['id'] ; ?></td>
              <td><?php echo $rs[$i]['indexid'] ; ?></td>
              <td><?php echo $rs[$i]['userid'] ; ?></td>
              <td>
                <a href="<?php echo $href; ?>" title="点击查看" target="_blank"><?php echo iFS::icon($filepath,'./app/admincp/ui');?></a>
                <a class="tip" title="<?php echo $filepath ; ?><hr />源文件名:<?php echo $rs[$i]['ofilename'] ; ?>"><?php echo $rs[$i]['filename'].'.'.$rs[$i]['ext']; ?></a>
              </td>
              <td><?php echo iFS::sizeUnit($rs[$i]['size']);?><br/><?php echo get_date($rs[$i]['time'],'Y-m-d');?></td>
              <td>
                <a class="btn btn-small" href="<?php echo $href; ?>" data-toggle="modal" title="查看"><i class="fa fa-eye"></i> 查看</a>
                <?php if(iACP::MP('FILE.EDIT')){?>
                <a class="btn btn-small tip" href="<?php echo APP_FURI;?>&do=editpic&from=modal&pic=<?php echo $filepath ; ?>" data-toggle="modal" title="使用美图秀秀编辑图片"><i class="fa fa-edit"></i> 编辑</a>
                <?php }?>
                <?php if(strstr($rs[$i]['ofilename'],'http://')){?>
                <a href="<?php echo APP_FURI; ?>&do=download&id=<?php echo $rs[$i]['id'] ; ?>" class="btn btn-small" title="正常重新下载" target="iPHP_FRAME"><i class="fa fa-download"></i> 下载</a>
                <a href="<?php echo APP_FURI; ?>&do=download&id=<?php echo $rs[$i]['id'] ; ?>&unwatermark=0" class="btn btn-small" title="重新下载 不添加水印" target="iPHP_FRAME"><i class="fa fa-download"></i> 下载2</a>
                <?php }?>
                <?php if(iACP::MP('FILE.UPLOAD')){?>
                <a href="<?php echo APP_URI; ?>&do=add&from=modal&id=<?php echo $rs[$i]['id'] ; ?>" class="btn btn-small" data-toggle="modal" data-meta='{"width":"500px","height":"300px"}' title="重新上传"><i class="fa fa-upload"></i> 上传</a>
                <?php }?>
                <?php if(iACP::MP('FILE.DELETE')){?>
                <a href="<?php echo APP_FURI; ?>&do=del&id=<?php echo $rs[$i]['id'] ; ?>&indexid=<?php echo $rs[$i]['indexid'] ; ?>" target="iPHP_FRAME" class="del btn btn-small" title='永久删除'  onclick="return confirm('确定要删除?');"/><i class="fa fa-trash-o"></i> 删除</a>
                <?php }?>
              </td>
            </tr>
            <?php }  ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="10"><div class="pagination pagination-right" style="float:right;"><?php echo iPHP::$pagenav ; ?></div>
                <div class="input-prepend input-append mt20"> <span class="add-on">全选
                  <input type="checkbox" class="checkAll checkbox" data-target="#<?php echo APP_BOXID;?>" />
                  </span>
                  <div class="btn-group dropup" id="iCMS-batch"> <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"><i class="fa fa-wrench"></i> 批 量 操 作 </a><a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"> <span class="caret"></span></a>
                    <ul class="dropdown-menu">
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
<?php iACP::foot();?>
