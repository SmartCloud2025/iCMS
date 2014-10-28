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
<style>
  hr{margin: 10px;}
</style>
<script>
$(function(){
  var box = document.getElementById("mkurls");
  $('#makeurls').click(function(){
      iCMS.dialog({title:'添加采集地址',
        content:box,
        okValue: '确定',ok: function () {
          var urls_text = $("#urls").val();
          if(urls_text){
            urls_text+="\n";
          }
          var urls_rs = $("#urls_rs").val();
          $("#urls").val(urls_text+urls_rs);
        },
        cancelValue: "取消",cancel: function(){}
      });
  });
  $(":text",box).keyup(function(event) {
    if(this.id=='url_pattern'){
      return;
    }
    var pp = $(this).closest("div.input-prepend");
    var a  = $("[name='format']",pp);
    preview(a[0]);
  });
  $(":radio,:checkbox",box).click(function(event) {
    preview(this);
  });
  function preview(a){
    var pp  = $(a).closest("div.input-prepend"),
    format  = parseInt($("[name='format']",pp).val());
    begin   = $("[name='begin']",pp).val()||0,
    num     = $("[name='num']",pp).val()||0,
    step    = $("[name='step']",pp).val()||1,
    zeroize = $("[name='zeroize']",pp).prop("checked"),
    reverse = $("[name='reverse']",pp).prop("checked"),
    url     = $('#url_pattern').val(),
    start   = 0,
    end     = 0,
    pattern = '<'+format+','+begin+','+num+','+step+','+zeroize+','+reverse+'>';
    if(format=="2"){
      pattern = '<'+format+','+begin+','+num+','+reverse+'>';
    }
    $("[name='format']").prop("checked", false).parent().removeClass('checked');
    $("[name='format']",pp).prop("checked", true).parent().addClass('checked');

    if(url==""){
      iCMS.alert("请先输入网址");
      return;
    }
    if(url.indexOf("(*)")==-1){
      iCMS.alert("请使用(*)格式通配符匹配网址");
      return;
    }
    if(format==0){
      start = parseInt(begin);
      end   = start+parseInt(num);
    }else if(format==1){
      if(parseInt(step)==1){
        iCMS.alert("等比不能为1");
        return;
      }
      if(num>32){
        iCMS.alert("等比数列数值太大,请重新设置初始值,项数,比值");
        return;
      }
      start = parseInt(begin);
      end   = start*Math.pow(parseInt(step), num-1);//parseInt(num)*parseInt(step);
    }else if(format==2){
      start = begin.charCodeAt(0);
      end   = num.charCodeAt(0);
    }
    urls = urlist(format,start,end,url,reverse,parseInt(step),zeroize,num);
    $("#urls_rs").val(url.replace('(*)',pattern));
    $("#preview").html(urls);
  }
  function urlist(format,start,end,url,reverse,step,zeroize,num){
    var urls = '',n = 0;
    if(format==2){
      num = end-start;
    }
    if(reverse){
      for(var i=end;i>=start;){
        if(n<5){
          urls+= _url(format,i,zeroize,end);
        }else{
          break;
        }
        n++;
        if(format==1){
          i=i/step;
        }else{
          i=i-step;
        }
      }

      if(num>5){
        urls+= '..................<br />';
        urls+= _url(format,start,zeroize,end);
      }
    }else{
      for(var i=start;i<=end;){
        if(n<5){
          urls+= _url(format,i,zeroize,end)
        }else{
          break;
        }
        n++;
        if(format==1){
          i=i*step;
        }else{
          i=i+step;
        }
      }
      if(num>5){
        urls+= '..................<br />';
        urls+= _url(format,end,zeroize,end);
      }
    }
    return urls;
  };
  function _url(format,i,zeroize,end){
      var ii = i
      if(format==2){
        ii = String.fromCharCode(i);
      }else{
        if(zeroize){
          var len = end.toString().length;
          if(len==1){
            len=2;
          }
          ii = pad(i,len);
        }
      }
      return url.replace('(*)',ii)+'<br />';
  }
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
            <a class="btn" id="makeurls">添加采集地址</a>
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
    <input type="text" class="span6" id="url_pattern" value=""/>
    <input type="hidden" id="urls_rs" value=""/>
    <a class="btn" href="(*)" data-toggle="insertContent" data-target="#url_pattern">(*)</a>
  </div>
  <hr>
  <div class="input-prepend input-append">
    <span class="add-on"><input type="radio" name="format" value="0"/> 等差数列</span>
    <span class="add-on">初始值</span>
    <input type="text" class="span1" name="begin" value="1"/>
    <span class="add-on">项数</span>
    <input type="text" class="span1" name="num" value="5"/>
    <span class="add-on">步长</span>
    <input type="text" class="span1" name="step" value="1"/>
    <span class="add-on">
    <input type="checkbox" name="zeroize"/>
    补零</span>
    <span class="add-on">
    <input type="checkbox" name="reverse"/>
    倒序</span>
  </div>
  <hr>
  <div class="input-prepend input-append">
    <span class="add-on"><input type="radio" name="format" value="1"/> 等比数列</span>
    <span class="add-on">初始值</span>
    <input type="text" class="span1" name="begin" value="1"/>
    <span class="add-on">项数</span>
    <input type="text" class="span1" name="num" value="5"/>
    <span class="add-on">比值</span>
    <input type="text" class="span1" name="step" value="2"/>
    <span class="add-on">
    <input type="checkbox" name="zeroize"/>
    补零</span>
    <span class="add-on">
    <input type="checkbox" name="reverse"/>
    倒序</span>
  </div>
  <hr>
  <div class="input-prepend input-append">
    <span class="add-on"><input type="radio" name="format" value="2"/> 字母变化</span>
    <span class="add-on">从</span>
    <input type="text" class="span1" name="begin" value="a" maxlength="1"/>
    <span class="add-on">到</span>
    <input type="text" class="span1" name="num" value="z" maxlength="1"/>
    (区分大小写)
    <span class="add-on">
    <input type="checkbox" name="reverse"/>
    倒序</span>
  </div>
  <div class="well" id="preview">
  </div>
</div>
<?php iACP::foot();?>
