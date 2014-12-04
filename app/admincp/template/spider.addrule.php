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
.rule_data_name { width:80px; }
.rule_data_rule { width:600px; }
.delprop { width:45px; }
</style>
<script type="text/javascript">
$(function(){
	<?php if($_GET['tab']){?>
		var $itab	= $("#<?php echo $_GET['app']; ?>-tab");
		$("li",$itab).removeClass("active");
		$(".tab-pane").removeClass("active").addClass("hide");
		$("a[href='#<?php echo $_GET['app']; ?>-<?php echo $_GET['tab']; ?>']",$itab).parent().addClass("active");
		$("#<?php echo $_GET['app']; ?>-<?php echo $_GET['tab']; ?>").addClass("active").removeClass("hide");
	<?php }?>
	$('#spider-data').on("click",".delprop",function(){
   		$(this).parent().parent().remove();
	});

	$(".addprop").click(function(){
		var length=$("#spider-data tbody tr").length;
		var href = $(this).attr("href");
		var tb	= $(href),tbody=$("tbody",tb);
		var ntr=$(".aclone",tb).clone(true).removeClass("hide aclone");
		$('input,textarea',ntr).removeAttr("disabled");
		$('input,textarea',ntr).each(function(i){
      this.id = this.id.replace('__NO__',length);
      this.name = this.name.replace('[__NO__]','['+length+']');
		});
    $('a[data-target]',ntr).each(function(i){
      var target= $(this).attr('data-target')
      target = target.replace('__NO__',length);
      $(this).attr('data-target',target);
    });
		$('.tip',ntr).tooltip();
    $(':checkbox,:radio',ntr).uniform()
    .on("click",function(){
        checkedStatus = $(this).prop("checked");
        this.checked = checkedStatus;
        if (checkedStatus == this.checked) {
          $(this).closest('.checker > span').removeClass('checked');
        }
        if (this.checked) {
         $(this).closest('.checker > span').addClass('checked');
        }
    });
		ntr.appendTo(tbody);
		return false;
	});
	$(".rule_data_page").on("click",function(){
		checkedStatus = $(this).prop("checked");
        this.checked = checkedStatus;
        if (checkedStatus == this.checked) {
          $(this).closest('.checker > span').removeClass('checked');
        }
        if (this.checked) {
			   $(this).closest('.checker > span').addClass('checked');
			   alert("此数据项您选择有分页,\n\n请记得设置[分页设置]选项卡的内容!");
        }
	});
});

</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-plus-square"></i> </span>
      <h5 class="brs"><?php echo ($this->rid ?'修改':'添加') ; ?><?php echo "[{$rs['name']}]"; ?>规则</h5>
      <ul class="nav nav-tabs" id="spider-tab">
        <li class="active"><a href="#spider-base" data-toggle="tab"><i class="fa fa-info-circle"></i> 基本设置</a></li>
        <li><a href="#spider-data" data-toggle="tab"><i class="fa fa-truck"></i> 数据项</a></li>
        <li><a href="#spider-page" data-toggle="tab"><i class="fa fa-columns"></i> 分页设置</a></li>
        <li><a href="#spider-pic" data-toggle="tab"><i class="fa fa-columns"></i> 图片下载设置</a></li>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=saverule" method="post" class="form-inline" id="iCMS-spider" target="iPHP_FRAME">
        <input name="id" type="hidden" value="<?php echo $this->rid ; ?>" />
        <div id="spider" class="tab-content">
          <div id="spider-base" class="tab-pane active">
            <div class="input-prepend"><span class="add-on">规则名称</span>
              <input type="text" name="name" class="span6" id="name" value="<?php echo $rs['name']; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"><span class="add-on">User_Agent</span>
              <input type="text" name="rule[user_agent]" class="span6" id="user_agent" value="<?php echo $rule['user_agent'] ; ?>"/>
              <div class="btn-group">
                <a class="btn" href="Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)" data-toggle="insertContent" data-target="#user_agent" data-mode="replace">百度蜘蛛</a>
                <a class="btn" href="Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727)" data-toggle="insertContent" data-target="#user_agent" data-mode="replace">普通浏览器</a>
                <a class="btn" href="Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4" data-toggle="insertContent" data-target="#user_agent" data-mode="replace">iPhone 6</a>
                <a class="btn" href="Mozilla/5.0 (Linux; Android 4.2.1; en-us; Nexus 5 Build/JOP40D) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.166 Mobile Safari/535.19" data-toggle="insertContent" data-target="#user_agent" data-mode="replace">Nexus 5</a>
              </div>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"><span class="add-on">Cookie</span>
              <input type="text" name="rule[curl][cookie]" class="span6" id="CURLOPT_COOKIE" value="<?php echo $rule['curl']['cookie'] ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"><span class="add-on">客户端解码</span>
              <input type="text" name="rule[curl][encoding]" class="span6" id="CURLOPT_ENCODING" value="<?php echo $rule['curl']['encoding'] ; ?>"/>
            </div>
            <span class="help-inline"><span class="label label-important">CURL设置 为客户端解码 默认为空,如果采集乱码可以填上gzip,deflate</span></span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"><span class="add-on">来路页</span>
              <input type="text" name="rule[curl][referer]" class="span6" id="CURLOPT_REFERER" value="<?php echo $rule['curl']['referer'] ; ?>"/>
            </div>
            <span class="help-inline"><span class="label label-important">CURL伪造来路页 默认为空,如果网站限制来路可填上相关来路</span></span>
            <div class="clearfloat mb10"></div>

            <div class="input-prepend input-append"> <span class="add-on">网页编码</span><span class="add-on">
              <label class="radio">
                <input type="radio" name="rule[charset]" id="charset1" value="utf-8"<?php if($rule['charset']=="utf-8"){ echo ' checked="true"';};?>>
                UTF-8 </label>
              </span><span class="add-on">
              <label class="radio">
                <input type="radio" name="rule[charset]" id="charset2" value="gbk"<?php if($rule['charset']=="gbk"){ echo ' checked="true"';};?>>
                GBK </label>
              </span><span class="add-on">
              <label class="radio">
                <input type="radio" name="rule[charset]" id="charset3" value="auto"<?php if($rule['charset']=="auto"){ echo ' checked="true"';};?>>
                自动识别 </label>
              </span> </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">采集顺序</span><span class="add-on">
              <label class="radio">
                <input type="radio" name="rule[sort]" id="charset1" value="1"<?php if($rule['sort']=="1"){ echo ' checked="true"';};?>>
                自下向上 </label>
              </span><span class="add-on">
              <label class="radio">
                <input type="radio" name="rule[sort]" id="charset2" value="2"<?php if($rule['sort']=="2"){ echo ' checked="true"';};?>>
                自上向下 </label>
              </span><span class="add-on">
              <label class="radio">
                <input type="radio" name="rule[sort]" id="charset3" value="3"<?php if($rule['sort']=="3"){ echo ' checked="true"';};?>>
                随机乱序 </label>
              </span></div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">列表采集模式</span><span class="add-on">
              <label class="radio">
                <input type="radio" name="rule[mode]" id="mode1" value="1"<?php if($rule['mode']=="1"){ echo ' checked="true"';};?>>
                正则 </label>
              </span><span class="add-on">
              <label class="radio">
                <input type="radio" name="rule[mode]" id="mode2" value="2"<?php if($rule['mode']=="2"){ echo ' checked="true"';};?>>
                phpQuery </label>
              </span></div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-sp"><span class="add-on">列表网址</span>
              <textarea name="rule[list_urls]" id="list_urls" class="span6"><?php echo $rule['list_urls'] ; ?></textarea>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-sp"><span class="add-on">列表区域规则</span>
              <textarea name="rule[list_area_rule]" id="list_area_rule" class="span6"><?php echo $rule['list_area_rule'] ; ?></textarea>
              <div class="btn-group btn-group-vertical"> <a class="btn" href="<%content%>" data-toggle="insertContent" data-target="#list_area_rule">内容标识</a> <a class="btn" href="<%var%>" data-toggle="insertContent" data-target="#list_area_rule">变量标识</a> </div>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-sp"><span class="add-on">列表区域整理</span>
              <textarea name="rule[list_area_format]" id="list_area_format" class="span6"><?php echo $rule['list_area_format'] ; ?></textarea>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-sp"><span class="add-on">列表链接规则</span>
              <textarea name="rule[list_url_rule]" id="list_url_rule" class="span6"><?php echo $rule['list_url_rule'] ; ?></textarea>
              <div class="btn-group btn-group-vertical"> <a class="btn" href="<%title%>" data-toggle="insertContent" data-target="#list_url_rule">标题</a> <a class="btn" href="<%url%>" data-toggle="insertContent" data-target="#list_url_rule">网址</a> <a class="btn" href="<%var%>" data-toggle="insertContent" data-target="#list_url_rule">变量标识</a> </div>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"><span class="add-on">网址合成</span>
              <input type="text" name="rule[list_url]" class="span6" id="list_url" value="<?php echo $rule['list_url'] ; ?>"/>
              <a class="btn" href="<%url%>" data-toggle="insertContent" data-target="#list_url">网址</a>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"><span class="add-on">网址整理</span>
              <textarea name="rule[list_url_clean]" id="list_url_clean" class="span6 tip" title="合成后整理"><?php echo $rule['list_url_clean'] ; ?></textarea>
              <a class="btn" href="<%url%>" data-toggle="insertContent" data-target="#list_url_clean">变量标识</a>
            </div>
            <div class="clearfloat mb10"></div>
          </div>
          <div id="spider-data" class="tab-pane">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>数据项名称</th>
                  <th>规则</th>
                  <th>选项</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php if($rule['data'])foreach((array)$rule['data'] AS $dkey=>$data){
              	$RDid	= 'rule_data_'.$dkey.'_rule';
              ?>
                <tr>
                  <td><div class="btn-group btn-group-vertical">
                      <input name="rule[data][<?php echo $dkey;?>][name]" type="text" class="rule_data_name" value="<?php echo $data['name'];?>"/>
                      <a class="btn" href="<%content%>" data-toggle="insertContent" data-target="#<?php echo $RDid;?>">内容标识</a> <a class="btn" href="<%var%>" data-toggle="insertContent" data-target="#<?php echo $RDid;?>">变量标识</a> </div></td>
                  <td class="rule_data_rule"><textarea name="rule[data][<?php echo $dkey;?>][rule]" class="span6" id="<?php echo $RDid;?>"><?php echo $data['rule'];?></textarea>
                    <div class="clearfloat mb10"></div>
                    <div class="input-prepend input-sp"> <span class="add-on s4">数据整理</span>
                      <textarea name="rule[data][<?php echo $dkey;?>][cleanbefor]" class="span3 tip"title="采集后整理"><?php echo $data['cleanbefor'];?></textarea>
                    </div>
                    <div class="input-append input-sp">
                      <textarea name="rule[data][<?php echo $dkey;?>][cleanafter]" class="span3 tip"title="发布前整理"><?php echo $data['cleanafter'];?></textarea>
                      <span class="add-on s4">数据整理</span></div></td>
                  <td><label class="checkbox">
                      <input type="checkbox" class="rule_data_page" name="rule[data][<?php echo $dkey;?>][page]" value="1"<?php if($data['page']){ echo ' checked="true"';};?>>
                      有分页</label>
                    <label class="checkbox">
                      <input type="checkbox" name="rule[data][<?php echo $dkey;?>][multi]" value="1"<?php if($data['multi']){ echo ' checked="true"';};?>>
                      匹配多条</label>
                    <div class="clearfloat mb10"></div>
                    <label class="checkbox">
                      <input type="checkbox" name="rule[data][<?php echo $dkey;?>][format]" value="1"<?php if($data['format']){ echo ' checked="true"';};?>>
                      HTML格式化</label>
                    <label class="checkbox">
                      <input type="checkbox" name="rule[data][<?php echo $dkey;?>][cleanhtml]" value="1"<?php if($data['cleanhtml']){ echo ' checked="true"';};?>>
                      移除HTML标识</label>
                    <div class="clearfloat mb10"></div>
                    <label class="checkbox">
                      <input type="checkbox" name="rule[data][<?php echo $dkey;?>][trim]" value="1"<?php if($data['trim']){ echo ' checked="true"';};?>>
                      去首尾空白</label>
                    <label class="checkbox">
                      <input type="checkbox" name="rule[data][<?php echo $dkey;?>][mergepage]" value="1"<?php if($data['mergepage']){ echo ' checked="true"';};?>>
                      合并分页</label>
                    <div class="clearfloat mb10"></div>
                    <label class="checkbox">
                      <input type="checkbox" name="rule[data][<?php echo $dkey;?>][empty]" value="1"<?php if($data['empty']){ echo ' checked="true"';};?>>
                      不允许为空</label>
                    <label class="checkbox">
                      <input type="checkbox" name="rule[data][<?php echo $dkey;?>][array]" value="1"<?php if($data['array']){ echo ' checked="true"';};?>>
                      返回数组</label>
                    <div class="clearfloat mb10"></div>
                    <label class="checkbox">
                      <input type="checkbox" name="rule[data][<?php echo $dkey;?>][json_decode]" value="1"<?php if($data['json_decode']){ echo ' checked="true"';};?>>
                      json解码</label>
                    <label class="checkbox">
                      <input type="checkbox" name="rule[data][<?php echo $dkey;?>][img_absolute]" value="1"<?php if($data['img_absolute']){ echo ' checked="true"';};?>>
                      图片地址补全</label>
                      <div class="clearfloat mb10"></div>
                    <label class="checkbox">
                      <input type="checkbox" name="rule[data][<?php echo $dkey;?>][dom]" value="1"<?php if($data['dom']){ echo ' checked="true"';};?>>
                      使用phpQuery匹配</label>
                    <div class="clearfloat mb10"></div></td>
                  <td><a class="btn btn-small delprop"><i class="fa fa-trash-o"></i> 删除</a></td>
                </tr>
                <?php } ?>
              </tbody>
              <tfoot>
                <tr class="hide aclone">
                  <td><div class="btn-group btn-group-vertical">
                      <input name="rule[data][__NO__][name]" type="text" disabled="disabled" class="rule_data_name" value=""/>
                      <a class="btn" href="<%content%>" data-toggle="insertContent" data-target="#rule_data___NO___rule">内容标识</a>
                      <a class="btn" href="<%var%>" data-toggle="insertContent" data-target="#rule_data___NO___rule">变量标识</a> </div></td>
                  <td class="rule_data_rule"><textarea name="rule[data][__NO__][rule]" disabled="disabled" class="span6" id="rule_data___NO___rule"></textarea>
                    <div class="clearfloat mb10"></div>
                    <div class="input-prepend input-sp"> <span class="add-on s4">数据整理</span>
                      <textarea name="rule[data][__NO__][cleanbefor]" disabled="disabled" class="span3 tip"title="采集后整理"></textarea>
                    </div>
                    <div class="input-append input-sp">
                      <textarea name="rule[data][__NO__][cleanafter]" disabled="disabled" class="span3 tip"title="发布前整理"></textarea>
                      <span class="add-on s4">数据整理</span> </div></td>
                  <td><label class="checkbox">
                      <input type="checkbox" class="rule_data_page" name="rule[data][__NO__][page]" value="1">
                      有分页</label>
                    <label class="checkbox">
                      <input type="checkbox" name="rule[data][__NO__][multi]" value="1">
                      匹配多条</label>
                    <div class="clearfloat mb10"></div>
                    <label class="checkbox">
                      <input type="checkbox" name="rule[data][__NO__][format]" value="1">
                      HTML格式化</label>
                    <label class="checkbox">
                      <input type="checkbox" name="rule[data][__NO__][cleanhtml]" value="1">
                      移除HTML标识</label>
                    <div class="clearfloat mb10"></div>
                    <label class="checkbox">
                      <input type="checkbox" name="rule[data][__NO__][trim]" value="1">
                      去首尾空白</label>
                    <label class="checkbox">
                      <input type="checkbox" name="rule[data][__NO__][mergepage]" value="1">
                      合并分页</label>
                    <div class="clearfloat mb10"></div>
                    <label class="checkbox">
                      <input type="checkbox" name="rule[data][__NO__][empty]" value="1">
                      不允许为空</label>
                    <label class="checkbox">
                      <input type="checkbox" name="rule[data][__NO__][array]" value="1">
                      返回数组</label>
                    <div class="clearfloat mb10"></div>
                    <label class="checkbox">
                      <input type="checkbox" name="rule[data][__NO__][json_decode]" value="1">
                      json解码</label>
                    <label class="checkbox">
                      <input type="checkbox" name="rule[data][__NO__][img_absolute]" value="1">
                      图片地址补全</label>
                    <div class="clearfloat mb10"></div>
                    <label class="checkbox">
                      <input type="checkbox" name="rule[data][__NO__][dom]" value="1">
                      使用phpQuery匹配</label>
                    <div class="clearfloat mb10"></div></td>
                  <td><a class="btn btn-small delprop"><i class="fa fa-trash-o"></i> 删除</a></td>
                </tr>
                <tr>
                  <td colspan="10">
                    <p class="mb10"> <span class="label label-info">摘要:description</span> <span class="label label-info">标签:tags</span> <span class="label label-info">出处:source</span> <span class="label label-info">作者:author</span> <span class="label label-info">关键字:keywords</span></p>
                    <a href="#spider-data" class="btn btn-primary addprop"/>增加加附加属性</a></td>
                </tr>
              </tfoot>
            </table>
          </div>
          <div id="spider-page" class="tab-pane">
            <ul class="nav nav-tabs" id="spider-tab">
              <li class="active"><a href="#spider-page-area-rule" data-toggle="tab"><i class="fa fa-wrench"></i> 采集方式</a></li>
              <li><a href="#spider-page-url-parse" data-toggle="tab"><i class="fa fa-random"></i> 逻辑方式</a></li>
            </ul>
            <div class="tab-content">
              <div id="spider-page-area-rule" class="tab-pane active">
                <div class="input-prepend input-sp"><span class="add-on">分页区域规则</span>
                  <textarea name="rule[page_area_rule]" id="page_area_rule" class="span6"><?php echo $rule['page_area_rule'] ; ?></textarea>
                  <div class="btn-group btn-group-vertical"> <a class="btn" href="<%content%>" data-toggle="insertContent" data-target="#page_area_rule">内容标识</a> <a class="btn" href="<%var%>" data-toggle="insertContent" data-target="#page_area_rule">变量标识</a> </div>
                </div>
                <div class="clearfloat mb10"></div>
                <div class="input-prepend input-sp"><span class="add-on">分页链接规则</span>
                  <textarea name="rule[page_url_rule]" id="page_url_rule" class="span6"><?php echo $rule['page_url_rule'] ; ?></textarea>
                  <div class="btn-group btn-group-vertical"> <a class="btn" href="<%url%>" data-toggle="insertContent" data-target="#page_url_rule">网址</a> <a class="btn" href="<%var%>" data-toggle="insertContent" data-target="#page_url_rule">变量标识</a> </div>
                </div>
              </div>
              <div id="spider-page-url-parse" class="tab-pane">
                <div class="input-prepend input-append"><span class="add-on">当前网址分解</span>
                  <input type="text" name="rule[page_url_parse]" class="span6" id="page_url_parse" value="<?php echo $rule['page_url_parse'] ; ?>"/>
                  <a class="btn" href="<%url%>" data-toggle="insertContent" data-target="#page_url_parse">分页网址</a> </div>
                <div class="clearfloat mb10"></div>
                <div class="input-prepend input-append"><span class="add-on">分页增量</span> <span class="add-on">起始编号</span>
                  <input type="text" name="rule[page_no_start]" class="span1" id="page_no_start" value="<?php echo $rule['page_no_start'] ; ?>"/>
                  <span class="add-on"><i class="fa fa-arrows-h"></i></span> <span class="add-on">结束编号</span>
                  <input type="text" name="rule[page_no_end]" class="span1" id="page_no_end" value="<?php echo $rule['page_no_end'] ; ?>"/>
                  <span class="add-on">步长</span>
                  <input type="text" name="rule[page_no_step]" class="span1" id="page_no_step" value="<?php echo $rule['page_no_step'] ; ?>"/>
                </div>
              </div>
            </div>
            <div class="clearfloat mb10"></div>
            <hr />
            <div class="input-prepend input-sp"><span class="add-on">分页有效特征码</span>
              <textarea name="rule[page_url_right]" id="page_url_right" class="span6" ><?php echo $rule['page_url_right'] ; ?></textarea>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-sp"><span class="add-on">分页无效特征码</span>
              <textarea name="rule[page_url_error]" id="page_url_error" class="span6"><?php echo $rule['page_url_error'] ; ?></textarea>
            </div>
            <div class="clearfloat mb10"></div>
            <hr />
            <div class="input-prepend input-append"><span class="add-on">网址合成</span>
              <input type="text" name="rule[page_url]" class="span6" id="page_url" value="<?php echo $rule['page_url'] ; ?>"/>
              <a class="btn" href="<%url%>" data-toggle="insertContent" data-target="#page_url">分页网址</a> <a class="btn" href="<%step%>" data-toggle="insertContent" data-target="#page_url">分页增量</a> </div>
            <div class="clearfloat mb10"></div>
          </div>
          <div id="spider-pic" class="tab-pane">
            <div class="input-prepend"><span class="add-on">CURLOPT_ENCODING</span>
              <input type="text" name="rule[fs][encoding]" class="span6" id="FS_ENCODING" value="<?php echo $rule['fs']['encoding'] ; ?>"/>
            </div>
            <span class="help-inline"><span class="label label-important">默认为空,如果采集乱码可以填上gzip,deflate</span></span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"><span class="add-on">CURLOPT_REFERER</span>
              <input type="text" name="rule[fs][referer]" class="span6" id="FS_REFERER" value="<?php echo $rule['fs']['referer'] ; ?>"/>
            </div>
            <span class="help-inline"><span class="label label-important">默认为空,如果网站限制来路可填上相关来路</span></span>
            <div class="clearfloat mb10"></div>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
          <a id="test" href="<?php echo APP_URI; ?>&do=testrule&rid=<?php echo $this->rid ; ?>" class="btn" data-toggle="modal" title="测试规则"><i class="fa fa-keyboard-o"></i> 测试</a> </div>
      </form>
    </div>
  </div>
</div>
<?php iACP::foot();?>
