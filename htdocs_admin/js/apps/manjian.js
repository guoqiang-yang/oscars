(function () {

	function main() {
		$('#save_manjian').click(onSaveManjian);
	}

	function onSaveManjian() {
		var id = $('#id').val();
		var stime = $('#stime').val();
		var etime = $('#etime').val();
		var isSand = $('input[name=is_sand]:checked').val();
		var isVip = $('input[name=is_vip]:checked').val();
		var status = $('input[name=status]:checked').val();
		var conf =$('#conf').val();

		if (K.isEmpty(stime)) {
			alert('请填写开始时间！');
			return false;
		}
		if (K.isEmpty(etime)) {
			alert('请填写结束时间！');
			return false;
		}
		if (K.isEmpty(conf)) {
			alert('请填写满减配置！');
			return false;
		}

		var para = {id: id, stime: stime, etime: etime, is_sand: isSand, isVip: isVip, status: status, conf: conf};
		K.post('/activity/ajax/save_manjian.php', para, onSaveSucc);
	}

	function onSaveSucc(data) {
		alert('保存成功');

		window.location.href = '/activity/manjian_list.php';
	}

	main();

})();