(function () {

	function main() {
        //多城市，多仓库
		$('#save').click(onSaveStaff);
		$('#check_all_cities').on('click', checkAllCities);
		$('.check_all_wid_of_city').on('click', checkAllWidOfCity);
		$('#save_city_warehouse').on('click', saveCityWids);
		$('.city_item').on('click', clickCityItem);
        $('#staff_role_select').on('change', changeLeaderSuid);
        $('#staff_city_id').on('change', changeLeaderSuid);

        changeLeaderSuid();
        //账号管理
        $('form').find('select[name=department]').on('change', changeDepartment);
	}

	function onSaveStaff() {
		var name = $('input[name=name]').val();
		var mobile = $('input[name=mobile]').val();
		var role = $('select[name=role]').val();

		if (K.isEmpty(name)) {
			alert('请填写姓名！');
			return false;
		}
		if (K.isEmpty(mobile)) {
			alert('请填写手机号！');
			return false;
		}
		if (K.isEmpty(role)) {
			alert('请选择角色！');
			return false;
		}
	}

	function checkAllCities() {
	    if ($(this).is(':checked')) {
            $('.city_item').each(function () {
                $(this).prop('checked', true);
            });
            $('#edit_warehouse_list').find('.city_wid_items').css('display', 'block');
        }
        else
        {
            $('.city_item').each(function () {
                $(this).removeProp('checked');
            });
            $('input[name=warehouse]').attr('checked', false);
            $('#edit_warehouse_list').find('.city_wid_items').css('display', 'none');
        }
    }
    
    function checkAllWidOfCity() {
	    var city = $(this).attr('data-city');
	    if ($(this).is(':checked')) {
            $('input[name=warehouse]').each(function () {
                if (city == $(this).attr('data-city'))
                {
                    $(this).prop('checked', true);
                }
            });
        }
        else{
            $('input[name=warehouse]').each(function () {
                if (city == $(this).attr('data-city'))
                {
                    $(this).removeProp('checked');
                }
            });
        }
    }

    function saveCityWids() {
	    var cities = [];
        $('#city_list').find('input[name=city]').each(function () {
            if ($(this).is(':checked'))
            {
                cities.push($(this).val());
            }
        });
        var wids = [];
        $('#edit_warehouse_list').find('input[name=warehouse]').each(function () {
            if ($(this).is(':checked'))
            {
                wids.push($(this).val());
            }
        });

	    var para = {
	        suid: $(this).attr('data-suid'),
            cities: JSON.stringify(cities),
            wids: JSON.stringify(wids)
        };

        K.post('/admin/ajax/save_city_warehouse.php', para, function () {
            alert('保存成功！');
            window.location.href = '/admin/staff_list.php';
        });
    }

    function clickCityItem() {
	    var city = $(this).val();
	    var check_box = $(this);
	    $('#edit_warehouse_list').find('.city_wid_items').each(function () {
	        if ($(this).attr('data-city') == city)
            {
                if (check_box.is(':checked')) {
                    $(this).css('display', 'block');
                }
                else {
                    $(this).css('display', 'none');
                    $(this).find('input[name=warehouse]').attr('checked', false);
                }
            }
        });
    }

    function changeDepartment() {
        var department = $(this).val();
        var role_mapping = eval('(' + $(this).attr('data-role-mapping') + ')');

        var obj = role_mapping[department];
        var html = '<option value=0 selected>全部</option>';
        for (var i in obj)
        {
            html += '<option value="' +i + '">' + obj[i] + '</option>';
        }
        $('form').find('select[name=role]').html(html);
    }

    function changeLeaderSuid() {
        var role_id = $('#staff_role_select').val();
        var city_id = $('#staff_city_id').val();
        var para = {
            suid: $('input[name=suid]').length==1? $('input[name=suid]').val():0,
            city_id: city_id
        };
        $('#staff_leader_suid option:not(:first)').remove();
        if(role_id == 3)
        {
            $('#staff_leader_suid').parent().parent().show();
            $.post('/admin/ajax/get_sale_leader.php', para, function (data) {
                var leader_list = eval('('+data+')');
                if(leader_list.errno == 0)
                {
                    for(var _role in leader_list.data){
                        $('#staff_leader_suid').append('<option value="'+leader_list.data[_role].suid+'">'+leader_list.data[_role].name+'</option>');
                    }
                    var leader_id = $('#staff_leader_suid').attr('data-suid');
                    if(leader_id != '')
                    {
                        $('#staff_leader_suid').val(leader_id);
                    }
                }else{
                    alert(leader_list.errmsg);
                }

            });
        }else{
            $('#staff_leader_suid').parent().parent().hide();
        }
    }

    main();

})();