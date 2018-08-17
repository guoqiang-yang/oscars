(function () {

	function main() {
		$('#save_sku_mapping').on('click', saveSkuMapping);
		$('.delete_sku_mapping').on('click', deleteSkuMapping);
	}

	function saveSkuMapping() {
		var para = {
			id: $('#id').val(),
			mid: $('#mid').val(),
			msid: $('#msid').val(),
			sid: $('#sid').val(),
			mprice: $('#mprice').val(),
			price: $('#price').val()
		};

		if (K.isEmpty(para.mid)) {
			alert('请选择第三方');
			return false;
		}
		if (K.isEmpty(para.msid)) {
			alert('请填写第三方sku id');
			return false;
		}
		if (K.isEmpty(para.sid)) {
			alert('请填写好材sku id');
			return false;
		}
		if (K.isEmpty(para.mprice)) {
			alert('请填写第三方对外售价');
			return false;
		}
		if (K.isEmpty(para.price)) {
			alert('请填写好材对第三方售价');
			return false;
		}

		K.post('/shop/ajax/save_sku_mapping.php', para, function (ret) {
			alert('保存成功！');

			window.location.href = '/shop/edit_sku_mapping.php?id=' + ret.id;
		});
	}

	function deleteSkuMapping() {
		var id = $(this).data('id');
		var para = {id: id};

		if (confirm('确认删除该记录？')) {
			K.post('/shop/ajax/delete_sku_mapping.php', para, function(data) {
				window.location.reload();
			});
		}
	}

	main();
})();