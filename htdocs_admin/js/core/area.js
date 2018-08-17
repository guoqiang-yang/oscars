(function () {
	var city = eval('(' + $('#city-json').html() + ')');
	var distinct = eval('(' + $('#distinct-json').html() + ')');
	var area = eval('(' + $('#area-json').html() + ')');

	function main() {
		_initArea();

		$('#select-city').change(_onChgCity);
		$('#select-district').change(_onChgDistrict);
		$('#service').change(_onChgService);
		$('#select-area').change(_onChangeArea);
	}

	function _initArea() {
        
        if ($('#select-city').length==0 && $('#select-district').length==0){
            return;
        }
        
		$('#select-city').html('');
		$('#select-city').append('<option value="0">选择城市</option>');

		var addressCity = $('#select-city').data('id');
		if (addressCity == '') {
			addressCity = $('#cur_city').val();
		}
		for (var i in city) {
			var option = '<option value="' + i + '">' + city[i] + '</option>';
			if (addressCity != 0 && addressCity == i) {
				option = '<option selected="selected" value="' + i + '">' + city[i] + '</option>';
			}

			$('#select-city').append(option);
		}

		$('#select-district').html('');
		$('#select-district').append('<option value="0">选择城区</option>');
		var addressDistrict = $('#select-district').data('id');
		var districtList = distinct[addressCity];
		for (var j in districtList) {
			var option = '<option value="' + j + '">' + districtList[j] + '</option>';
			if (addressDistrict == j) {
				option = '<option selected="selected" value="' + j + '">' + districtList[j] + '</option>';
			}
			$('#select-district').append(option);
		}

		var addressArea = $('#select-area').data('id');
		if (!$.isEmptyObject(area[addressDistrict])) {
			$('#area').css('display', '');
			if (Object.getOwnPropertyNames(area[addressDistrict]).length > 1 || $('#area').data('mustall')=='1') {
				$('#select-area').append('<option value="0">选择范围</option>');
			}
			for (var k in area[addressDistrict]) {
				var option = '<option value="' + k + '">' + area[addressDistrict][k] + '</option>';
				if (addressArea == k) {
					option = '<option selected="selected" value="' + k + '">' + area[addressDistrict][k] + '</option>';
				}
				$('#select-area').append(option);
			}
		}

		_onChangeArea();
	}

	function _onChgCity() {
		var curCity = $('#select-city').val();

		if (distinct.hasOwnProperty(curCity)) {
			$('#select-district').html('');
			$('#select-district').append('<option value="0">选择区域</option>');

			for (var i in distinct[curCity]) {
				$('#select-district').append('<option value="' + i + '">' + distinct[curCity][i] + '</option>');
			}

			$('#district').css('display', '');
		} else {
			$('#select-district').html('');
			$('#district').css('display', 'none');
		}

		$('#select-area').html('');
		$('#area').css('display', 'none');
	}

	function _onChgDistrict() {
		var curDistrict = $('#select-district').val();

		if (area.hasOwnProperty(curDistrict)) {
			$('#select-area').html('');

			if (Object.getOwnPropertyNames(area[curDistrict]).length > 1 || $('#area').data('mustall')=='1') {
				$('#select-area').append('<option value="0">选择范围</option>');
			}

			for (var i in area[curDistrict]) {
				$('#select-area').append('<option value="' + i + '">' + area[curDistrict][i] + '</option>');
			}

			$('#area').css('display', '');
		} else {
			$('#select-area').html('');
			$('#area').css('display', 'none');
		}
	}

	function _onChangeArea() {
		var area = $('#select-area').val();

		if (parseInt(area) == 6) {
			$('#_j_cal_freight').css('display', 'none');
			$('#freight_desc').css('display', '');
			$('#freight_alert').css('display', '');
		} else {
			$('#_j_cal_freight').css('display', '');
			$('#freight_desc').css('display', 'none');
			$('#freight_alert').css('display', 'none');
		}
	}

	function _onChgService() {
		if ($(this).val() == 2) {
			$('#floor-num').css('display', 'inline-block');
		} else {
			$('#floor-num').css('display', 'none');
		}

		if ($(this).val() == 0) {
			$('#carry_fee').val(0);
		}
	}

	main();

})();