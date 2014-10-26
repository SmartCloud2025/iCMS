<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: user.add.php 179 2013-03-29 03:21:28Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');
iACP::head();
?>
<script type="text/javascript">
$(function(){
	iCMS.select('gid',"<?php echo $rs->gid ; ?>");
  iCMS.select('pid',"<?php echo $rs->pid?$rs->pid:0; ?>");
	iCMS.select('gender',"<?php echo $userdata->gender ; ?>");
	iCMS.select('year',"<?php echo $userdata->year ; ?>");
	iCMS.select('month',"<?php echo $userdata->month ; ?>");
  iCMS.select('day',"<?php echo $userdata->day ; ?>");
  iCMS.select('status',"<?php echo $this->uid?$rs->status:'1' ; ?>");
  iCMS.select('isSeeFigure',"<?php echo $userdata->isSeeFigure ; ?>");
});
</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-user"></i> </span>
      <h5 class="brs"><?php echo empty($this->uid)?'添加':'修改' ; ?>用户</h5>
      <ul class="nav nav-tabs" id="user-tab">
        <li class="active"><a href="#user-info" data-toggle="tab"><b>基本信息</b></a></li>
        <?php if($this->uid){;?>
        <li><a href="#user-data" data-toggle="tab"><b>用户资料</b></a></li>
        <?php };?>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="iCMS-user" target="iPHP_FRAME">
        <input name="uid" type="hidden" value="<?php echo $this->uid; ?>" />
        <input  name="user[type]" type="hidden"value="<?php echo $rs->type ; ?>"/>
        <input name="_pid" type="hidden" value="<?php echo $rs->pid; ?>" />

        <div id="user-add" class="tab-content">
          <div id="user-info" class="tab-pane active">
            <?php if(iACP::is_superadmin()){ ?>
            <div class="input-prepend"> <span class="add-on">角色</span>
              <select name="user[gid]" id="gid" class="chosen-select" data-placeholder="请选择用户组">
                <option value='0'>路人甲[GID:0] </option>
                <?php echo $this->groupApp->select(); ?>
              </select>
            </div>
            <?php }?>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">属 性</span>
              <select name="pid[]" id="pid" class="chosen-select span6" multiple="multiple">
                <option value="0">普通用户[pid='0']</option>
                <?php echo iACP::getProp("pid") ; ?>
              </select>
            </div>
            <?php if($this->uid){;?>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend">
              <span class="add-on"> 头像</span>
              <img src="<?php echo iCMS_FS_URL.get_user_pic($this->uid);?>" class="img-polaroid">
            </div>
            <?php };?>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on"> 账号</span>
              <input type="text" name="user[username]" class="span3" id="username" value="<?php echo $rs->username ; ?>"/>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on"> 密码</span>
              <input type="text" name="user[password]" class="span3" id="password" value=""/>
              <a class="btn" data-toggle="createpass" data-target="#password">生成</a>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">昵称</span>
              <input type="text" name="user[nickname]" class="span3" id="nickname" value="<?php echo $rs->nickname ; ?>"/>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">性别</span>
              <select name="user[gender]" id="gender" class="chosen-select">
                <option value="1">男</option>
                <option value="0">女</option>
              </select>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend input-append">
              <span class="add-on">粉丝数</span>
              <input type="text" name="user[fans]" class="span1" id="fans" value="<?php echo $rs->fans ; ?>"/>
              <span class="add-on">关注数</span>
              <input type="text" name="user[follow]" class="span1" id="follow" value="<?php echo $rs->follow ; ?>"/>
              <span class="add-on">积分</span>
              <input type="text" name="user[credit]" class="span1" id="credit" value="<?php echo $rs->credit ; ?>"/>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend input-append">
              <span class="add-on">评论数</span>
              <input type="text" name="user[comments]" class="span1" id="comments" value="<?php echo $rs->comments ; ?>"/>
              <span class="add-on">文章数</span>
              <input type="text" name="user[article]" class="span1" id="article" value="<?php echo $rs->article ; ?>"/>
              <span class="add-on">分享数</span>
              <input type="text" name="user[share]" class="span1" id="share" value="<?php echo $rs->share ; ?>"/>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">注册IP</span>
              <input type="text" name="user[regip]" class="span3" id="regip" value="<?php echo $rs->regip ; ?>"/>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend">
              <span class="add-on">注册时间</span>
              <input type="text" name="user[regdate]" class="span3" id="regdate" value="<?php echo get_date($rs->regdate) ; ?>"/>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">最后登陆IP</span>
              <input type="text" name="user[lastloginip]" class="span3" id="lastloginip" value="<?php echo $rs->lastloginip ; ?>"/>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"><span class="add-on">最后登陆时间</span>
              <input type="text" name="user[lastlogintime]" class="span3" id="lastlogintime" value="<?php echo get_date($rs->lastlogintime) ; ?>"/>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend input-append">
              <span class="add-on">总点击数</span>
              <input type="text" name="user[hits]" class="span2" id="hits" value="<?php echo $rs->hits ; ?>"/>
              <span class="add-on">当天点击数</span>
              <input type="text" name="user[hits_today]" class="span2" id="hits_today" value="<?php echo $rs->hits_today ; ?>"/>
              <span class="add-on">昨天点击数</span>
              <input type="text" name="user[hits_yday]" class="span2" id="hits_yday" value="<?php echo $rs->hits_yday ; ?>"/>
              <span class="add-on">周点击</span>
              <input type="text" name="user[hits_week]" class="span2" id="hits_week" value="<?php echo $rs->hits_week ; ?>"/>
              <span class="add-on">月点击</span>
              <input type="text" name="user[hits_month]" class="span2" id="hits_month" value="<?php echo $rs->hits_month ; ?>"/>
            </div>

            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">账号状态</span>
              <select name="user[status]" id="status" class="chosen-select">
                <option value="0">禁用</option>
                <option value="1">正常</option>
                <option value="2">黑名单</option>
              </select>
            </div>
          </div>
          <div id="user-data" class="tab-pane hide">
            <div class="input-prepend"> <span class="add-on">姓名</span>
              <input type="text" name="userdata[realname]" class="span3" id="realname" value="<?php echo $userdata->realname ; ?>"/>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">电话</span>
              <input type="text" name="userdata[mobile]" class="span3" id="mobile" value="<?php echo $userdata->mobile ; ?>"/>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">现有的地址</span>
              <input type="text" name="userdata[enterprise]" class="span3" id="enterprise" value="<?php echo $userdata->enterprise ; ?>"/>
              <span class="add-on">街道地址</span>
              <input type="text" name="userdata[address]" class="span3" id="address" value="<?php echo $userdata->address ; ?>"/>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">微博</span>
              <input type="text" name="userdata[weibo]" id="weibo" class="span3" value="<?php echo $userdata->weibo ; ?>" />
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">省份</span>
              <input type="text" name="userdata[province]" class="span3" id="province" value="<?php echo $userdata->province ; ?>"/>
              <span class="add-on">城市</span>
              <input type="text" name="userdata[city]" class="span3" id="city" value="<?php echo $userdata->city ; ?>"/>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">生日</span>
              <select name="userdata[year]" id="year" class="chosen-select"  style="width:90px;" data-placeholder="年">
                <?php
                $year = (int)date('Y');$syear =$year-35;$eyear =$year-14;
                for ($i=$syear; $i < $eyear; $i++) {?>
                <option value="<?php echo $i ?>"><?php echo $i ?></option>
                <?php } ?>
              </select><span class="add-on">年</span>
              <select name="userdata[month]" id="month" class="span1 chosen-select" data-placeholder="月">
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
              <span class="add-on">月</span>
              <select name="userdata[day]" id="day" class="span1 chosen-select" data-placeholder="日">
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
              </select><span class="add-on">日</span>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">星座</span>
              <input type="text" name="userdata[constellation]" class="span3" id="constellation" value="<?php echo $userdata->constellation ; ?>"/>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">职业</span>
              <input type="text" name="userdata[profession]" class="span3" id="profession" value="<?php echo $userdata->profession ; ?>"/>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">身材信息是否可见</span>
              <select name="userdata[isSeeFigure]" id="isSeeFigure" class="chosen-select">
                <option value="0">自己</option>
                <option value="1">所有人</option>
              </select>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">身高</span>
              <input type="text" name="userdata[height]" id="height" class="span3" value="<?php echo $userdata->height ; ?>"  maxlength="12"/>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">体重</span>
              <input type="text" name="userdata[weight]" id="weight" class="span3" value="<?php echo $userdata->weight ; ?>" />
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">胸围</span>
              <input type="text" name="userdata[bwhB]" id="bwhB" class="span3" value="<?php echo $userdata->bwhB ; ?>" />
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">腰围</span>
              <input type="text" name="userdata[bwhW]" id="bwhW" class="span3" value="<?php echo $userdata->bwhW ; ?>" />
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">臀围</span>
              <input type="text" name="userdata[bwhH]" id="bwhH" class="span3" value="<?php echo $userdata->bwhH ; ?>" />
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">肤质</span>
              <input type="text" name="userdata[pskin]" id="pskin" class="span3" value="<?php echo $userdata->pskin ; ?>" />
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">发质</span>
              <input type="text" name="userdata[phair]" id="phair" class="span3" value="<?php echo $userdata->phair ; ?>" />
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">鞋码尺寸</span>
              <input type="text" name="userdata[shoesize]" id="shoesize" class="span3" value="<?php echo $userdata->shoesize ; ?>" />
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">个人标签</span>
              <input type="text" name="userdata[personstyle]" id="personstyle" class="span3" value="<?php echo $userdata->personstyle ; ?>" />
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">自我介绍</span>
              <textarea name="userdata[slogan]" id="slogan" rows="5" class="span6"><?php echo $userdata->slogan ; ?></textarea>
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">昵称修改次数</span>
              <input type="text" name="userdata[unickEdit]" id="unickEdit" class="span3" value="<?php echo $userdata->unickEdit ; ?>" />
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">自定义封面</span>
              <input type="text" name="userdata[coverpic]" id="coverpic" class="span3" value="<?php echo $userdata->coverpic ; ?>" />
            </div>
            <div class="clearfix mb10"></div>

            <div class="input-prepend"> <span class="add-on">淘宝用户名</span>
              <input type="text" name="userdata[tb_nick]" id="tb_nick" class="span3" value="<?php echo $userdata->tb_nick ; ?>" />
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">买家信用</span>
              <input type="text" name="userdata[tb_buyer_credit]" id="tb_buyer_credit" class="span3" value="<?php echo $userdata->tb_buyer_credit ; ?>" />
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">卖家信用</span>
              <input type="text" name="userdata[tb_seller_credit]" id="tb_seller_credit" class="span3" value="<?php echo $userdata->tb_seller_credit ; ?>" />
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">淘宝用户类型</span>
              <input type="text" name="userdata[tb_type]" id="tb_type" class="span3" value="<?php echo $userdata->tb_type ; ?>" />
            </div>
            <div class="clearfix mb10"></div>
            <div class="input-prepend"> <span class="add-on">是否金牌卖家</span>
              <input type="text" name="userdata[is_golden_seller]" id="is_golden_seller" class="span3" value="<?php echo $userdata->is_golden_seller ; ?>" />
            </div>
            <div class="clearfix mb10"></div>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php iACP::foot();?>
