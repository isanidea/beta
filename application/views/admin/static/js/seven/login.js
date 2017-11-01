$(function () {
    var url = '';
    $('#submit').on('click', function (e) {
        e.preventDefault();
        var username = $.trim($('#username').val());
        var password = $.trim($('#password').val());
        var imgcode = $.trim($('#imgCode').val());
        if (username == '') {
            $('#username').val('');
            return MODAL.tip('用户名不能为空！');
        }
        if (password == '') {
            $('#password').val('');
            return MODAL.tip('密码不能为空！');
        }
        if(imgcode == ''){
            $('#imgCode').val('');
            return MODAL.tip('验证码不能为空！');
        }
        var $btn = $(this).button('loading');
        var data = {username: username, password: $.md5(password),img_code:imgcode};

        $.ajax({
            url: url,
            data:data,
            cache:false,
            dataType: 'json',
            type: 'post',
            timeout:1000,
            success:function (result) {
                if(result.iRet == 0){
                    window.location.href = 'index.html';
                }else {
                    MODAL.tip(result.sMsg || '填写信息错误！');
                }
                return $btn.button('reset');
            },
            error: function () {
                MODAL.tip('系统错误！');
                return $btn.button('reset');
            }
        });
    })
});