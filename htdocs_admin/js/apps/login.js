(function() {
    function main() {
        $(".get_picture_code_pwd").on('click', getPictureCodePwd);
        $(".get_picture_code_ding,#code_ding").on('click', getPictureCodeDing);

        $('._j_login').click(_onLogin);

        document.onkeydown=function(event) {
            var e = event || window.event || arguments.callee.caller.arguments[0];
            if(e && e.keyCode == 13) { // enter 键
                _onLogin();
            }
        };

        $('#ding_verify').on('click', sendemail);
    }

    function getPictureCodePwd()
    {
        $('#login_pwd img').attr("src", '/user/ajax/send_picture_code.php?' + Math.random());
        $('#code_pwd').unbind('click', getPictureCodePwd);
        $('#code_ding').bind('click', getPictureCodeDing);
    }
    function getPictureCodeDing()
    {
        $('#login_ding img').attr("src", '/user/ajax/send_picture_code.php?' + Math.random());
        $('#code_pwd').bind('click', getPictureCodePwd);
        $('#code_ding').unbind('click', getPictureCodeDing);
    }


    function _onLogin(ev) {
        var type = $(this).data('type');

        if (type == 'ding')
        {
            var para = {
                mobile:$('input[name=mobile_ding]').val(),
                picture: $('input[name=picture_ding]').val(),
                ding_code: $('input[name=ding_verify]').val(),
                type:type
            };
        }

        if (type == 'pwd')
        {
            var para = {
                mobile:$('input[name=mobile_pwd]').val(),
                password:$('input[name=password]').val(),
                picture: $('input[name=picture_pwd]').val(),
                return_url:$('input[name=return_url]').val(),
                type:type
            };
        }

		realType = para.type;
        K.post('/user/ajax/login.php', para, _onLoginSuccess, _onLoginFail);
    }

    function _onLoginSuccess(data){
        window.location.href = data.return_url;
    }

    function _onLoginFail(data) {
        alert(data.errmsg);
        if (realType == 'pwd')
        {
            $('#login_pwd img').attr("src", '/user/ajax/send_picture_code.php?' + Math.random());
        }
        if (realType == 'ding')
        {
            $('#login_ding img').attr("src", '/user/ajax/send_picture_code.php?' + Math.random());
        }
    }

    var countdown=60;
    function sendemail(){
        var obj = $("#ding_verify");
        var para = {
            mobile: $('input[name=mobile_ding]').val(),
            picture: $('input[name=picture_ding]').val(),
        };
        if (para.mobile.length == 0)
        {
            alert('请输入手机号！');
            return false;
        }
        if (para.picture.length == 0)
        {
            alert('请输入验证码！');
            return false;
        }

        settime(obj);
        K.post('/user/ajax/get_ding_verify.php', para, function () {

        })
    }
    function settime(obj) { //发送验证码倒计时
        if (countdown == 0) {
            obj.attr('disabled',false);
            obj.html("获取验证码");
            countdown = 60;
            return;
        } else {
            obj.attr('disabled',true);
            obj.html("重新发送(" + countdown + ")");
            countdown--;
        }
        setTimeout(function() {settime(obj) },1000)
    }

    main();

} )();