$(function() {
  var login = $("#iCMS-login-box");
  var def_uname = "<!--{iCMS:lang key='user:login:def_uname'}-->";
  login.on("focus", '#iCMS-uname', function() {
    if (this.value == def_uname) {
      this.value = '';
    }
    this.style.color = '#000';
  }).on("blur", '#iCMS-uname', function() {
    if (this.value == "") {
      this.value = def_uname;
      this.style.color = '';
    }
  });
  $(document).on("click", '.iCMS-loginBtn', function() {
    var param = {
      'action': 'login',
      'openid': '<!--{$openid}-->'
    };
    param.uname = $("#iCMS-uname",login).val();
    if (login_check('uname', param.uname) || login_check('uname', param.uname, def_uname)) return false;

    param.pass = $("#iCMS-pass",login).val();
    if (login_check('pass', param.pass)) return false;

    <!--{if $iCMS.config.user.loginseccode }-->
    param.seccode = $("#iCMS-seccode",login).val();
    if (login_check('seccode', param.seccode)) return false;
    <!--{/if}-->

    param.remember = $("#iCMS-remember:checked",login).val();

    $.post("<!--{iCMS:router url='/api/user'}-->", param, function(ret) {
      if (!ret.code) {
        // if(ret.forward=='seccode'){
        //   $("#iCMS-seccode-img").click();
        // }
        msg = $('.err_' + ret.forward).css('visibility', 'visible');
        msg.html('<span><i class="fa fa-minus-circle"></i> ' + ret.msg + '</span>');
      } else {
        window.location.href = ret.forward;
      }
    }, 'json');
  })
})

function login_check(el, val, def) {
  def = def || '';
  if (val == def) {
    $("#iCMS-" + el).focus();
    $(".err_" + el).css('visibility', 'visible');
    return true;
  } else {
    $(".err_" + el).css('visibility', 'hidden');
    return false;
  }
}