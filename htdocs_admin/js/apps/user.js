(function() {

	function main() {
		$('#_j_chgpwd').click(_onChangePassword);
	}

	function _onChangePassword(ev) {
		var old_password = $('input[name=old_password]').val(),
			new_password = $('input[name=new_password]').val();

		var para = {old_password:old_password, new_password:new_password};
		K.post('/user/ajax/chgpwd.php', para, _onChgpwdSuccess);
	}
	function _onChgpwdSuccess(data) {
		alert('修改密码成功！');
        window.location.href = '/';
	}

	main();

} )();