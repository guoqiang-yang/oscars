(function () {

	function main() {
		$('#save_keyword').on('click', saveKeyword);
	}

	function saveKeyword() {
		var keyword = $('#keyword').val();
		var sortby = $('#sortby').val();
		var cityIdArr = [];
		$('.city_item:checked').each(function() {
			cityIdArr.push($(this).val());
		});

		var cityId = cityIdArr.join(",");
		var para = {keyword: keyword, sortby: sortby, city_id: cityId};

		if (K.isEmpty(para.keyword)) {
			alert('没写热搜词，能不能认真点！');
			return false;
		}
		if (K.isEmpty(para.city_id)) {
			alert('没选城市，能不能认真点！');
			return false;
		}

		K.post('/activity/ajax/save_keyword.php', para, function (ret) {
			alert('保存成功');
			window.location.reload();
		});
	}

	main();

})();