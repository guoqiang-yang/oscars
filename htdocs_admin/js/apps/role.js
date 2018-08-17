(function () {

	function main() {
		$('#add_driver').click(_onSaveDriver);
		$('#add_carrier').click(_onSaveCarrier);
		$('#add_car_model').click(_onSaveCarModel);
		$('#add_source').click(_onSaveSource);
		$('.delete_carrier').click(_onDeleteCarrier);
		$('.delete_driver').click(_onDeleteDriver);
		$('.delete_model').click(_onDeleteCarModel);
		$('.delete_source').click(_onDeleteSource);
		$('.reset_driver_passwd').click(_resetDriverPasswd);
        
        $('#chg_driver_city').on('change', changeDriverCity);
        //角色管理
        $('.select-search-type').on('click', selectSearchType);
        $('select[name=module]').on('change', changeModule);
        $('select[name=list_page]').on('change', changeListPage);
	}

	function _onSaveDriver() {
		var para = {
			did: $('#did').val(),
			name: $('input[name=name]').val(),
			mobile: $('input[name=mobile]').val(),
			real_name: $('input[name=real_name]').val(),
			card_num: $('input[name=card_num]').val(),
			bank_info: $('input[name=bank_info]').val(),
			car_model: $('select[name=car_model]').val(),
			source: $('select[name=source]').val(),
			wid: $('select[name=wid]').val(),
			status: $('select[name=status]').val(),
			car_code: $('select[name=car_code]').val(),
			can_carry: $('select[name=can_carry]').val(),
			score: $('input[name=score]').val(),
			note: $('textarea[name=note]').val(),
			referer: $('#referer').val(),
			can_trash: $('select[name=can_trash]').val(),
			can_escort: $('select[name=can_escort]').val(),
			car_province: $('select[name=car_province]').val(),
			car_number: $('input[name=car_number]').val(),
		};
        var transScope = [];
        $('input[name=trans_scope]:checked').each(function(){
            transScope.push($(this).val());
        });
        para.trans_scope = transScope.join(',');

		if ('' == K.trim(para.name)) {
			alert('名字不能为空');
			return false;
		}

		if (K.isEmpty(K.trim(para.mobile))) {
			alert('电话不能为空');
			return false;
		}

		if ('' == K.trim(para.car_model)) {
			alert('请选择车型');
			return false;
		}

		if (K.isEmpty(K.trim(para.source))) {
			alert('请选择来源');
			return false;
		}

		K.post('/logistics/ajax/save_driver.php', para, _onSaveDriverSucss);
	}

	function _onSaveCarrier() {
		var para = {
			cid: $('#cid').val(),
			name: $('input[name=name]').val(),
			mobile: $('input[name=mobile]').val(),
			real_name: $('input[name=real_name]').val(),
			card_num: $('input[name=card_num]').val(),
			bank_info: $('input[name=bank_info]').val(),
			wid: $('select[name=wid]').val(),
			status: $('select[name=status]').val(),
			referer: $('#referer').val()
		};

		if ('' == K.trim(para.name)) {
			alert('名字不能为空');
			return false;
		}

		if (K.isEmpty(K.trim(para.mobile))) {
			alert('电话不能为空');
			return false;
		}


		K.post('/logistics/ajax/save_carrier.php', para, _onSaveCarrierSucss);
	}

	function _onSaveCarModel() {
		var para = {
			mid: $('#mid').val(),
			name: $('input[name=name]').val()
		};

		if ('' == K.trim(para.name)) {
			alert('车型不能为空');
			return false;
		}

		K.post('/logistics/ajax/save_car_model.php', para, _onSaveCarModelSucss);
	}

	function _onSaveSource() {
		var para = {
			sid: $('#sid').val(),
			source: $('input[name=source]').val()
		};

		if ('' == K.trim(para.source)) {
			alert('来源不能为空');
			return false;
		}

		K.post('/logistics/ajax/save_source.php', para, _onSaveSourceSucss);
	}

	function _onDeleteDriver() {
		if (confirm('确定要删除该记录吗？')) {
			var para = {
				did: $(this).data('id')
			};

			K.post('/logistics/ajax/delete_driver.php', para, _onSaveDriverSucss);
		}
	}

	function _onDeleteCarrier() {
		if (confirm('确定要删除该记录吗？')) {
			var para = {
				cid: $(this).data('id')
			};

			K.post('/logistics/ajax/delete_carrier.php', para, _onSaveCarrierSucss);
		}
	}

	function _onDeleteCarModel() {
		if (confirm('确定要删除该记录吗？')) {
			var para = {
				mid: $(this).data('id')
			};

			K.post('/logistics/ajax/delete_car_model.php', para, _onSaveCarModelSucss);
		}
	}

	function _onDeleteSource() {
		if (confirm('确定要删除该记录吗？')) {
			var para = {
				sid: $(this).data('id')
			};

			K.post('/logistics/ajax/delete_source.php', para, _onSaveSourceSucss);
		}
	}

	function _resetDriverPasswd() {
		if (confirm('确定要重置司机的密码吗？')) {
			var para = {
				did: $(this).attr('data-id'),
				otype: 'reset_df_passwd'
			};

			K.post('/logistics/ajax/modify_driver.php', para, function () {
				alert('重置成功！重置为 【hc234987】 ！！');
				window.location.reload();
			});
		}
	}

	function _onSaveDriverSucss(data) {
		if (K.isEmpty(data.referer)) {
		    if (data.status){
                alert('该司机的队列状态为'+data.status);
            }
			window.location.href = "/logistics/driver.php";
		} else {
			window.location.href = data.referer;
		}
	}

	function _onSaveCarrierSucss(data) {
		if (K.isEmpty(data.referer)) {
			window.location.href = "/logistics/carrier.php";
		} else {
			window.location.href = data.referer;
		}
	}

	function _onSaveCarModelSucss(data) {
		window.location.href = "/logistics/car_model.php";
	}

	function _onSaveSourceSucss(data) {
		window.location.href = "/logistics/source.php";
	}

    // 修改司机对应的城市，同事变化配送范围
    function changeDriverCity(){
        var allTransScopes = eval('(' + $('#allTransScopes_json').html() + ')');
        var driverTransScopes = eval('('+$('#driverTransScopes_json').html() + ')');
        var cityId = $(this).val();
        
        var html = '';
        var checked;
        var driverTransScopesLen = driverTransScopes.length;
        if (typeof allTransScopes[cityId] != 'undefined'){
            
            for(var scope in allTransScopes[cityId]){
                
                checked = '';
                for (var i=0; i<driverTransScopesLen; i++){
                    if (driverTransScopes[i] == scope){
                        checked = 'checked';
                        break;
                    }
                }
                
                html += '<label style="margin-right:10px;font-size:16px;">'
                     +  '<input type="checkbox" name="trans_scope" value="'+ scope +'" '+ checked +'> '
                     +  allTransScopes[cityId][scope]
                     +  '</label>';
            }
        } else {
            html = '<span class="text-value" style="color:red;">该城市暂无配送范围！</span>';
        }
        
        $('#transScopeInCity').html(html);
    }

    function selectSearchType() {
        var search_type = $(this).attr('data-search-type');
        window.location.href = "/admin/role_list.php?search_type=" + search_type;
    }

    function changeModule() {
        var module = $(this).val();
        var modules = eval('(' + $('form').attr('data-modules') + ')');
        var obj = modules[module]['pages'];
        var html = '';
        for (var i in obj)
        {
            html += '<option value="'+ i +'">' + obj[i]['name'] + '</option>';
        }
        $('form').find('select[name=list_page]').html(html);

        var page = $('form').find('select[name=list_page]').val();
        var p_html = '<option value=0>全部</option>';
        for(var j in obj[page]['buttons'])
        {
            console.log(j);
            p_html += '<option value="' + j + '">' + obj[page]['buttons'][j] + '</option>';
        }
        $('form').find('select[name=permission]').html(p_html);
    }

    function changeListPage() {
        var page = $(this).val();
        var module = $('form').find('select[name=module]').val();
        var modules = eval('(' + $('form').attr('data-modules') + ')');
        var p_obj = modules[module]['pages'][page]['buttons'];
        var p_html = '<option value=0>全部</option>';
        for (var j in p_obj)
        {
            p_html += '<option value="' + j + '">' + p_obj[j] + '</option>';
        }
        $('form').find('select[name=permission]').html(p_html);
    }

	main();

})();