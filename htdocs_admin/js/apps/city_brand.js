(function () {

	function main() {
		$('.edit_city_brand').click(onShowCityBrand);
        $('.save_city_brand').click(onSaveCityBrand);
	}

	function onShowCityBrand() {
		var id = $(this).attr('data-id');
        if(id > 0)
        {
            $('#edit_city_brand').find('.modal-title').html('编辑推荐品牌');
            $('#edit_city_brand').find('input[name=id]').val(id);
            $('#edit_city_brand').find('select[name=city_id]').val($(this).attr('data-city')).attr('readonly', true);
            $('#edit_city_brand').find('input[name=water_1]').val($(this).attr('data-1'));
            $('#edit_city_brand').find('input[name=electric_2]').val($(this).attr('data-2'));
            $('#edit_city_brand').find('input[name=wood_3]').val($(this).attr('data-3'));
            $('#edit_city_brand').find('input[name=tile_4]').val($(this).attr('data-4'));
            $('#edit_city_brand').find('input[name=oil_5]').val($(this).attr('data-5'));
            $('#edit_city_brand').find('input[name=tools_6]').val($(this).attr('data-6'));
        }else{
            $('#edit_city_brand').find('.modal-title').html('新增推荐品牌');
            $('#edit_city_brand').find('input[name=id]').val(0);
            $('#edit_city_brand').find('select[name=city_id]').attr('readonly', true);
            $('#edit_city_brand').find('input[name=water_1]').val('');
            $('#edit_city_brand').find('input[name=electric_2]').val('');
            $('#edit_city_brand').find('input[name=wood_3]').val('');
            $('#edit_city_brand').find('input[name=tile_4]').val('');
            $('#edit_city_brand').find('input[name=oil_5]').val('');
            $('#edit_city_brand').find('input[name=tools_6]').val('');
        }
        $('#edit_city_brand').modal();
	}

	function onSaveCityBrand() {
		var para = {
		    id: $('#edit_city_brand').find('input[name=id]').val(),
            city_id:$('#edit_city_brand').find('select[name=city_id]').val(),
            water_1:$('#edit_city_brand').find('input[name=water_1]').val(),
            electric_2:$('#edit_city_brand').find('input[name=electric_2]').val(),
            wood_3:$('#edit_city_brand').find('input[name=wood_3]').val(),
            tile_4:$('#edit_city_brand').find('input[name=tile_4]').val(),
            oil_5:$('#edit_city_brand').find('input[name=oil_5]').val(),
            tools_6:$('#edit_city_brand').find('input[name=tools_6]').val(),
        };

		if (K.isEmpty(para.city_id)) {
			alert('请选择城市！');
			return false;
		}
		if (K.isEmpty(para.water_1)) {
			alert('请填写水类推荐品牌！');
			return false;
		}
		if (K.isEmpty(para.electric_2)) {
			alert('请填写电类推荐品牌！');
			return false;
		}

        if (K.isEmpty(para.wood_3)) {
            alert('请填写木类推荐品牌！');
            return false;
        }

        if (K.isEmpty(para.tile_4)) {
            alert('请填写瓦类推荐品牌！');
            return false;
        }
        if (K.isEmpty(para.oil_5)) {
            alert('请填写油类推荐品牌！');
            return false;
        }
        if (K.isEmpty(para.tools_6)) {
            alert('请填写工具类推荐品牌！');
            return false;
        }
        $(this).attr('disabled', true);
		K.post('/activity/ajax/save_city_brand.php', para, onSaveSucc, onSaveFail);
	}

	function onSaveSucc(data) {
		alert('保存成功');

		window.location.href = '/activity/city_brand_list.php';
	}

	function onSaveFail(data) {
        alert(data.errmsg);
        $('#edit_city_brand').find('.save_city_brand').attr('disabled', false);
    }

	main();

})();