$(function() {
  var register = $("#iCMS-register-box");
  $(".iCMS-registerBtn",register).click(function() {
    var _checkform = false,
      param = {
        'action': 'register',
        'openid': '<!--{$openid}-->',
        'sex': $('input[name="sex"]:checked').val(),
      };
    $("input[id^='iCMS-']", register).each(function() {
      iCMS_check_form(this);
      console.log(this.id, this.name, !$(this).data('check'))
      if (!$(this).data('check')) {
        _checkform = true;
        return false;
      }
      console.log(this.id, 'ok')
      if (this.name) {
        param[this.name] = this.value;
      }
    });
    if (_checkform) return false;

    $.post("<!--{iCMS:router url='/api/user'}-->", param, function(ret) {
      if (ret.code) {
        window.location.href = ret.forward;
      } else {
        var a = document.getElementById('iCMS-' + ret.forward);
        msg(a, ret.msg);
      }
    }, 'json');

  })
  $("input[id^='iCMS-']", register).click(function() {
    msg(this);
  }).blur(function() {
    iCMS_check_form(this);
  });

  $("[name=agreement]").click(function() {
    $(this).prop("checked", true);
  });
})

function iCMS_check_form(a) {
  if (a.value == "") {
    return msg(a, 'empty');
  }
  switch (a.name) {
    case 'username':
      var pattern = /^([a-zA-Z0-9._-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9._-])+/;
      if (!pattern.test(a.value)) {
        return msg(a, "err");
      }
      ajax_check(a);
      break;
    case 'nickname':
      var length = a.value.replace(/[^\x00-\xff]/g, 'xx').length;
      if (length < 4) {
        return msg(a, "err");
      }
      if (length > 20) {
        return msg(a, "err");
      }
      ajax_check(a);
      break;
    case 'password':
      if (a.value.length < 6) {
        return msg(a, "err");
      }
      return msg(a, "ok");
      break;
    case 'rstpassword':
      var pwd = $("#iCMS-password").val();
      if (pwd.length < 6) {
        return msg(a, "perr");
      }
      if (pwd != a.value) {
        return msg(a, "err");
      }
      return msg(a, "ok");
      break;
    case 'seccode':
      ajax_check(a);
      break;
  }
}

function msg(e, c) {
  var info = {
    'err_username': '电子邮箱格式不正确！',
    'err_nickname': '昵称只能4~20位，每个中文字算2位字符。',
    'err_password': '密码太短啦，至少要6位哦',
    'err_rstpassword': '密码与确认密码不一致！',
    'perr_rstpassword': '请重复输入一次密码！',
    'err_ajax_username': '邮件地址已经注册过了,请直接登陆或者换个邮件再试试。',
    'err_ajax_nickname': '昵称已经被注册了,请换个再试试。',
    'err_ajax_seccode': '',

    'empty_username': '请填写电子邮箱！',
    'empty_nickname': '请填写昵称！',
    'empty_password': '请填写密码！',
    'empty_rstpassword': '请重复输入一次密码！',
    'empty_seccode': '请输入验证码！',
  }

  var n = e.name,
    err = $(".err_" + n).css('visibility', 'visible');

  if (c == "ok") {
    err.html('<span><i class="fa fa-check-circle"></i></span>');
    $(e).data('check', true);
  }
  if (c && c != "ok") {
    var info = info[c + '_' + n] || c;
    err.html('<span><i class="fa fa-minus-circle"></i> ' + info + '</span>');
    $(e).data('check', false);
  }
}

function ajax_check(a) {
  var val = $(a).data('value');
  if (typeof(val) === undefined || val == "" || val != a.value) {
    $(a).data('value', a.value);
    $.get("<!--{iCMS:router url='/api/user/check'}-->", {
        name: a.name,
        value: a.value
      },
      function(c) {
        //a.name = c.forward
        if (c.code) {
          msg(a, "ok");
          $(a).data("check", true);
        } else {
          msg(a, c.msg);
          $(a).data("check", false);
        }
      }, 'json');
  }
}