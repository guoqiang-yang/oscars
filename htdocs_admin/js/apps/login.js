(function() {
    function main() {
        $(".get_picture_code_pwd").on('click', getPictureCodePwd);

        $('._j_login').click(_onLogin);

        document.onkeydown=function(event) {
            var e = event || window.event || arguments.callee.caller.arguments[0];
            if(e && e.keyCode == 13) { // enter 键
                _onLogin();
            }
        };
    }

    function getPictureCodePwd()
    {
        $('#login_pwd img').attr("src", '/user/ajax/send_picture_code.php?' + Math.random());
    }
    

    function _onLogin() {
        var para = {
            mobile:$('input[name=mobile_pwd]').val(),
            password:$('input[name=password]').val(),
            picture: $('input[name=picture_pwd]').val(),
            return_url:$('input[name=return_url]').val(),
        };
        
        K.post('/user/ajax/login.php', para, 
            function(data){
                window.location.href = data.return_url;
            }, 
            function(data){
                alert(data.errmsg);
                $('#login_pwd img').attr("src", '/user/ajax/send_picture_code.php?' + Math.random());
            }
        );
    }
    
    main();

} )();