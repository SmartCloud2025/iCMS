$(function(){
  var def_uname="<!--{iCMS:lang key='user:login:def_uname'}-->";
  $(document).on("focus",'#iCMS_uname',function() {
    if(this.value==def_uname){
      this.value = '';
    }
    this.style.color='#000';
  }).on("blur",'#iCMS_uname',function() {
    if(this.value==""){
      this.value       = def_uname;
      this.style.color = '';
    }
  });
  $(document).on("click",'.iCMS_loginBtn',function() {
    var param   = {'action':'login','openid':'<!--{$openid}-->'};
    param.uname = $("#iCMS_uname").val();
    if(login_check('uname',param.uname)||login_check('uname',param.uname,def_uname)) return false;

    param.pass = $("#iCMS_pass").val();
    if(login_check('pass',param.pass)) return false;

    <!--{if $iCMS.config.user.loginseccode }-->
    param.seccode = $("#iCMS_seccode").val();
    if(login_check('seccode',param.seccode)) return false;
    <!--{/if}-->

    param.remember = $("#iCMS_remember:checked").val();
    
    $.post("<!--{$iURL.api.user}-->",param,function(ret){
      if(!ret.code){
        // if(ret.forward=='seccode'){
        //   $("#iCMS_seccode_img").click();
        // }
        msg = $('.err_'+ret.forward).css('visibility','visible');
        msg.html('<span><i class="fa fa-minus-circle"></i> '+ret.msg+'</span>');
      }else{
        window.location.href = ret.forward;
      }
    },'json');
  })
})
function login_check(el,val,def){
    def = def||'';
    if(val==def){
      $("#iCMS_"+el).focus();
      $(".err_"+el).css('visibility','visible');
      return true;
    }else{
      $(".err_"+el).css('visibility','hidden');
      return false;
    }
}