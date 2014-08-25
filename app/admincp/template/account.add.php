<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: account.add.php 179 2013-03-29 03:21:28Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');
iACP::head();
?>
<script type="text/javascript">
$(function(){
	iCMS.select('gid',"<?php echo $rs->gid ; ?>");
	iCMS.select('gender',"<?php echo $rs->gender ; ?>");
	iCMS.select('year',"<?php echo $rs->info['year'] ; ?>");
	iCMS.select('month',"<?php echo $rs->info['month'] ; ?>");
	iCMS.select('day',"<?php echo $rs->info['day'] ; ?>");
});
</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-user"></i> </span>
      <h5 class="brs"><?php echo empty($this->uid)?'添加':'修改' ; ?>用户</h5>
      <ul class="nav nav-tabs" id="account-tab">
        <li class="active"><a href="#account-info" data-toggle="tab"><b>基本信息</b></a></li>
        <li><a href="#account-power" data-toggle="tab"><b>后台权限</b></a></li>
        <li><a href="#account-cpower" data-toggle="tab"><b>栏目权限</b></a></li>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="iCMS-account" target="iPHP_FRAME">
        <input name="uid" type="hidden" value="<?php echo $this->uid; ?>" />
        <input name="type" type="hidden" value="<?php echo $this->type; ?>" />
        <div id="account-add" class="tab-content">
          <div id="account-info" class="tab-pane active">
            <?php if(iACP::is_superadmin()){ ?>
            <div class="input-prepend"> <span class="add-on">角色</span>
              <select name="gid" id="gid" class="chosen-select" data-placeholder="请选择用户组">
                <option value='0'>路人甲[GID:0] </option>
                <?php echo $this->groupApp->select(); ?>
              </select>
            </div>
            <?php }?>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on"> 账号</span>
              <input type="text" name="uname" class="span3" id="uname" value="<?php echo $rs->username ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on"> 密码</span>
              <input type="text" name="pwd" class="span3" id="pwd" value=""/>
              <a class="btn" data-toggle="createpass" data-target="#pwd">生成</a>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">昵称</span>
              <input type="text" name="nickname" class="span3" id="nickname" value="<?php echo $rs->nickname ; ?>"/>
            </div>
            <hr />
            <div class="input-prepend"> <span class="add-on">姓名</span>
              <input type="text" name="realname" class="span3" id="realname" value="<?php echo $rs->realname ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">性别</span>
              <select name="gender" id="gender" class="chosen-select">
                <option value="2">保密</option>
                <option value="1">男</option>
                <option value="0">女</option>
              </select>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">Q Q</span>
              <input type="text" name="icq" id="icq" class="span3" value="<?php echo $rs->info['icq'] ; ?>"  maxlength="12"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">博客</span>
              <input type="text" name="home" id="home" class="span3" value="<?php echo $rs->info['home'] ; ?>" />
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">生日</span>
              <select name="year" id="year" class="chosen-select"  style="width:90px;" data-placeholder="年">
                <?php
                $year = (int)date('Y');$syear =$year-35;$eyear =$year-14;
                for ($i=$syear; $i < $eyear; $i++) {?>
                <option value="<?php echo $i ?>"><?php echo $i ?></option>
                <?php } ?>
              </select>
              <select name="month" id="month" class="span1 chosen-select" data-placeholder="月">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">10</option>
                <option value="11">11</option>
                <option value="12">12</option>
              </select>
              <select name="day" id="day" class="span1 chosen-select" data-placeholder="日">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">10</option>
                <option value="11">11</option>
                <option value="12">12</option>
                <option value="13">13</option>
                <option value="14">14</option>
                <option value="15">15</option>
                <option value="16">16</option>
                <option value="17">17</option>
                <option value="18">18</option>
                <option value="19">19</option>
                <option value="20">20</option>
                <option value="21">21</option>
                <option value="22">22</option>
                <option value="23">23</option>
                <option value="24">24</option>
                <option value="25">25</option>
                <option value="26">26</option>
                <option value="27">27</option>
                <option value="28">28</option>
                <option value="29">29</option>
                <option value="30">30</option>
                <option value="31">31</option>
              </select>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">来自</span>
              <input type="text" name="from" id="from" class="span3" value="<?php echo $rs->info['from'] ; ?>" />
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">签名</span>
              <textarea name="signature" id="signature" cols="45" rows="5" class="span3"><?php echo $rs->info['signature'] ; ?></textarea>
            </div>
            <div class="clearfloat mb10"></div>
          </div>
          <?php include iACP::view("admincp.power"); ?>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php iACP::foot();?>
