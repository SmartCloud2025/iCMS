<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: groups.add.php 179 2013-03-29 03:21:28Z coolmoo $
 */
defined('iCMS') OR exit('What are you doing?'); 
iACP::head();
?>
<script type="text/javascript">
$(function(){
  iCMS.select('type',"<?php echo $rs->type ; ?>");
});
</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-user"></i> </span>
      <h5 class="brs"><?php echo empty($this->gid)?'添加':'修改' ; ?>角色</h5>
      <ul class="nav nav-tabs" id="groups-tab">
        <li class="active"><a href="#groups-info" data-toggle="tab"><b>基本信息</b></a></li>
        <li><a href="#groups-admincp" data-toggle="tab"><b>后台权限</b></a></li>
        <li><a href="#groups-category" data-toggle="tab"><b>栏目权限</b></a></li>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="iCMS-groups" target="iPHP_FRAME">
        <input name="gid" type="hidden" value="<?php echo $this->gid; ?>" />
        <div id="groups-add" class="tab-content">
          <div id="groups-info" class="tab-pane active">
            <div class="input-prepend"> <span class="add-on">角色类型</span>
              <select name="type" id="type" class="chosen-select" data-placeholder="请选择角色类型">
                <option value='0'>会员组[type:0] </option>
                <option value='1'>管理组[type:1] </option>
              </select>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on"> 角 色 名</span>
              <input type="text" name="name" class="span3" id="name" value="<?php echo $rs->name ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
          </div>
          <div id="groups-admincp" class="tab-pane hide"> </div>
          <div id="groups-category" class="tab-pane hide"> </div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php iACP::foot();?>
