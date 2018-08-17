(function () {

    var step = 1;

    $('#btn_save').bind('click',_onSave);
    $('#btn_assign_to_saler').bind('click',_onAssignToSaler);
    $('#btn_complate').bind('click',_onComplate);

    function _onAssignToSaler() {
        step = 33;
        _onSave();
    }

    function _onComplate() {
        step = 99;
        _onSave();
    }

    function _onSave(ev) {
        var para = {
            id: $('#id').val(),
            city: $('#city').val(),
            district: $('#district').val(),
            area: $('#area').val(),
            name: $('#name').val(),
            mobile: $('#mobile').val(),
            house_style: $('#house_style').val(),
            house_type: $('#house_type').val(),
            house_area: $('#house_area').val(),
            budget: $('#budget').val(),
            fit_time: $('#fit_time').val(),
            note: $('#note').val(),
            saler_suid: $("#saler_suid").val(),
            hc_note: $('#hc_note').val(),
            step: step,
            case_id: $('#case_id').val(),
            fid: $('#fid').val()
        };
        if (K.isEmpty(para.city) || K.isEmpty(para.district) || K.isEmpty(para.area)) {
            alert('所在区域不能为空');
            return false;
        }
        if (K.isEmpty(para.name)) {
            alert('姓名不能为空');
            return false;
        }
        if (K.isEmpty(para.mobile)) {
            alert('手机号不能为空');
            return false;
        }
        if (parseInt(para.house_type) == 0) {
            alert('装修户型不能为空');
            return false;
        }
        if (parseInt(para.house_area) == 0) {
            alert('装修面积不能为空');
            return false;
        }
        if (step == 33 && parseInt(para.saler_suid) == 0) {
            alert('请选择销售！');
            return false;
        }
        if (step != 1 && K.isEmpty(para.hc_note)) {
            alert('请填写备注！');
            return false;
        }

        $(this).attr('disabled', true);
        K.post('/activity/ajax/save_appointment.php', para, _onSaveSucc);
    }

    function _onSaveSucc(data) {
        alert('保存成功');
        window.location.href = '/aftersale/appointment_list.php';
    }
})();