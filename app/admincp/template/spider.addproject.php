<?php /**
* @package iCMS
* @copyright 2007-2010, iDreamSoft
* @license http://www.idreamsoft.com iDreamSoft
* @author coolmoo <idreamsoft@qq.com>
* @$Id: spider.php 586 2013-04-02 14:44:18Z coolmoo $
*/
defined('iPHP') OR exit('What are you doing?');
iACP::head();
?>
<script>
$(function(){
  $('#makeurls').click(function(){
      $.dialog({id: 'mkurldiv',width:"320px",lock: true,
        title:'添加采集地址',content:document.getElementById("mkurls"),
          okValue: '确定',ok: function () {
            return true;
          },
          cancelValue: "取消",cancel: function(){
            return true;
        }
      });
  });
})
</script>
<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-plus-square"></i> </span>
      <h5><?php echo empty($this->pid)?'添加':'修改' ; ?>方案</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=saveproject" method="post" class="form-inline" id="iCMS-spider" target="iPHP_FRAME">
        <div id="addproject" class="tab-content">
          <input name="id" type="hidden" value="<?php echo $this->pid ; ?>" />
          <div class="input-prepend"><span class="add-on">方案名称</span>
            <input type="text" name="name" class="span6" id="name" value="<?php echo $rs['name']; ?>"/>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend input-append"><span class="add-on">列表网址</span>
            <textarea name="urls" id="urls" class="span6" style="height: 90px;"><?php echo $rs['urls'] ; ?></textarea>
            <!--a class="btn" id="makeurls">添加采集地址</a-->
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend input-append"><span class="add-on">网址合成</span>
            <input type="text" name="list_url" class="span6" id="list_url" value="<?php echo $rs['list_url'] ; ?>"/>
            <a class="btn" href="<%url%>" data-toggle="insertContent" data-target="#list_url">网址</a>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">绑定栏目</span>
            <select name="cid" id="cid" class="chosen-select span3">
              <option value="0"> == 请选择采集绑定的栏目 == </option>
              <?php echo $cata_option;?>
            </select>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">采集规则</span>
            <select name="rid" id="rid" class="chosen-select span3">
              <option value="0"> == 请选择采集规则 == </option>
              <?php echo $rule_option;?>
            </select>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">发布模块</span>
            <select name="poid" id="poid" class="chosen-select span3">
              <option value="0"> == 请选择发布模块 == </option>
              <?php echo $post_option;?>
            </select>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"><span class="add-on">采集间隔</span>
            <input type="text" name="sleep" class="span2" id="sleep" value="<?php echo $rs['sleep']; ?>"/>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"><span class="add-on">自动采集</span>
            <div class="switch">
              <input type="checkbox" data-type="switch" name="auto" id="auto" <?php echo $rs['auto']?'checked':''; ?>/>
            </div>
          </div>
          <div class="clearfloat mb10"></div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
        <a class="btn"><i class="fa fa-keyboard-o"></i> 测试</a> </div>
      </form>
    </div>
  </div>
</div>
<div id="mkurls" class="hide">
  <div class="input-prepend input-append"><span class="add-on">地址格式</span>
    <input type="text" class="span6" id="url_pattern"/>
    <a class="btn" href="(*)" data-toggle="insertContent" data-target="#url_pattern">格式</a>
  </div>
  <hr>
  <div class="input-prepend input-append">
    <span class="add-on"><input type="radio" name="format" value="0"/> 等差数列</span>
    <span class="add-on">初始值</span>
    <input type="text" class="span1" id="begin" value="1"/>
    <span class="add-on">项数</span>
    <input type="text" class="span1" id="num" value="5"/>
    <span class="add-on">步长</span>
    <input type="text" class="span1" id="step" value="1"/>
    <span class="add-on">
    <input type="checkbox"/>
    补零</span>
    <span class="add-on">
    <input type="checkbox"/>
    倒序</span>
  </div>
  <hr>
  <div class="input-prepend input-append">
    <span class="add-on"><input type="radio" name="format" value="1"/> 等比数列</span>
    <span class="add-on">初始值</span>
    <input type="text" class="span1" id="begin" value="1"/>
    <span class="add-on">项数</span>
    <input type="text" class="span1" id="num" value="5"/>
    <span class="add-on">比值</span>
    <input type="text" class="span1" id="step" value="2"/>
    <span class="add-on">
    <input type="checkbox"/>
    补零</span>
    <span class="add-on">
    <input type="checkbox"/>
    倒序</span>
  </div>
</div>
<?php iACP::foot();?>
