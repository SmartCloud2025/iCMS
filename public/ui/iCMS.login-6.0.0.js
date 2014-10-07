(function($) {
  iCMS.user.login = function(boxid,success) {
    var login = $(boxid);
    login.on("click", '.iCMS_login_btn',function(){
      var param = {'action': 'login'};
      param.uname = $(".iCMS_login_uname", login).val();
      if (login_check('uname', param.uname)){
        return false
      };

      param.pass = $(".iCMS_login_pass", login).val();
      if (login_check('pass', param.pass)){
        return false
      };

      var seccode = $(".iCMS_login_seccode", login);
      if(seccode.length){
        param.seccode = seccode.val();
        if (login_check('seccode', param.seccode)){
          return false
        };
      }

      param.remember = $(".iCMS_login_remember:checked", login).val();

      $.post(iCMS.api('user'), param, function(ret) {
        if (ret.code) {
          window.location.href = ret.forward;
        } else {
          iCMS.alert(ret.msg);
        }
      }, 'json');
    })

    function login_check(el, val, def) {
      def = def || '';
      var info = {
        'uname':'请输入用户名',
        'pass':'请输入密码',
        'seccode':'请输入验证码',
      }
      var b = $(".iCMS_login_" + el,login);
      b.tooltip('destroy');
      if (val == def) {
        b.focus();
        iCMS.tip(b, '<i class="fa fa-times-circle"></i> '+info[el]);
        return true;
      } else {
        if (typeof(success) === "function") {
          success(b);
        }
        return false;
      }
    }
  }
})(jQuery);
