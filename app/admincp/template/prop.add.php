<?php /**
* @package iCMS
* @copyright 2007-2010, iDreamSoft
* @license http://www.idreamsoft.com iDreamSoft
* @author coolmoo <idreamsoft@qq.com>
* @$Id: prop.add.php 2379 2014-03-19 02:37:47Z coolmoo $
*/
defined('iPHP') OR exit('What are you doing?');
iACP::head();
?>
<script type="text/javascript">
$(function(){
iCMS.select('cid',"<?php echo $rs['cid'] ; ?>");
});
</script>
<div class="iCMS-container">
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-plus-square"></i> </span>
      <h5><?php echo empty($this->pid)?'添加':'修改' ; ?>属性</b></h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="iCMS-prop" target="iPHP_FRAME">
        <input name="pid" type="hidden" value="<?php echo $this->pid ; ?>" />
        <div id="<?php echo APP_BOXID;?>" class="tab-content">
          <div class="input-prepend"> <span class="add-on">所属栏目</span>
            <select name="cid" id="cid" class="span3 chosen-select">
              <option value="0"> ==== 暂无所属栏目 ==== </option>
              <?php echo $this->categoryApp->select('ca',$rs['cid'],0,1,true);?>
            </select>
          </div>
          <span class="help-inline">本属性所属的栏目</span>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend input-append"> <span class="add-on">属性类型</span>
            <input type="text" name="type" class="span4" id="type" value="<?php echo $rs['type'];?>"/>
            <div class="btn-group">
              <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"> <span class="caret"></span> 选择</a>
              <ul class="dropdown-menu">
                <li><a href="javascript:;" data-value='article' data-toggle="insert" data-target="#type">article:文章</a></li>
                <li><a href="javascript:;" data-value='push' data-toggle="insert" data-target="#type">push:推送</a></li>
                <li><a href="javascript:;" data-value='category' data-toggle="insert" data-target="#type">category:栏目</a></li>
                <li><a href="javascript:;" data-value='tags' data-toggle="insert" data-target="#type">tags:标签</a></li>
                <li><a href="javascript:;" data-value='user' data-toggle="insert" data-target="#type">user:用户</a></li>
              </ul>
            </div>
          </div>
          <span class="help-inline">article:文章 push:推送 category:栏目</span>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend input-append"> <span class="add-on">属性字段</span>
            <input type="text" name="field" class="span4" id="field" value="<?php echo $rs['field'];?>"/>
            <div class="btn-group">
              <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"> <span class="caret"></span> 选择</a>
              <ul class="dropdown-menu">
                <li><a href="javascript:;" data-value='pid' data-toggle="insert" data-target="#field">文章:属性:pid</a></li>
                <li><a href="javascript:;" data-value='author' data-toggle="insert" data-target="#field">文章:作者:author</a></li>
                <li><a href="javascript:;" data-value='source' data-toggle="insert" data-target="#field">文章:出处:source</a></li>
                <li class="divider"></li>
                <li><a href="javascript:;" data-value='pid' data-toggle="insert" data-target="#field">推送:属性:pid</a></li>
                <li class="divider"></li>
                <li><a href="javascript:;" data-value='pid' data-toggle="insert" data-target="#field">栏目:属性:pid</a></li>
                <li class="divider"></li>
                <li><a href="javascript:;" data-value='pid' data-toggle="insert" data-target="#field">标签:属性:pid</a></li>
                <li class="divider"></li>
                <li><a href="javascript:;" data-value='pid' data-toggle="insert" data-target="#field">用户:属性:pid</a></li>
                <li class="divider"></li>
                <li><a href="javascript:;" data-value='pid' data-toggle="insert" data-target="#field">推送:属性:pid</a></li>

              </ul>
            </div>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">属性名称</span>
            <input type="text" name="name" class="span4" id="name" value="<?php echo $rs['name'];?>"/>
          </div>
          <span class="help-inline">可填写中文</span>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">属 性 值</span>
            <input type="text" name="val" class="span4" id="val" value="<?php echo $rs['val'];?>"/>
          </div>
          <span class="help-inline">pid:只能填写数字</span>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">排序</span>
            <input type="text" name="ordernum" class="span2" id="ordernum" value="<?php echo $rs['ordernum'];?>"/>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 添加</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php iACP::foot();?>
