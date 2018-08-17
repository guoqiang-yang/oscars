"use strict";

(function(){
    
    $("#save").bind('click', addFee);
    $('.edit_warehouse_fee').bind('click', showEditFee);


    function addFee() {
        var wid = parseInt($('#wid').val());
        var month = $('#month').val();
        var fixedInput = $('#fixed_input').val();
        var staffSalary = $('#staff_salary').val();
        var otherInput = $('#other_input').val();
        var offlineLogisticsFee = $('#offline_logistics_fee').val();
        var id = $('#id').val();

        if (isNaN(wid) || wid <= 0) {
            alert("请选择仓库！");
            return false;
        }
        if (K.isEmpty(month)) {
            alert("请填写月份！");
            return false;
        }

        var para = {id: id, wid: wid, month: month, fixed_input: fixedInput, staff_salary: staffSalary, other_input: otherInput, offline_logistics_fee: offlineLogisticsFee};
        K.post('/statistics/ajax/save_warehouse_fee.php', para, _onSaveSucc);
    }

    function showEditFee() {
        var id = $(this).data("id");
        var wid = parseInt($('#wid_' + id).html());
        var month = $('#month_' + id).html();
        var fixedInput = $('#fixed_input_' + id).html();
        var staffSalary = $('#staff_salary_' + id).html();
        var otherInput = $('#other_input_' + id).html();
        var offlineLogisticsFee = $('#offline_logistics_fee_' + id).html();

        $('#edit_fee_dlg').modal();
        $('#id').val(id);
        $('#wid').val(wid);
        $('#month').val(month);
        $('#fixed_input').val(fixedInput);
        $('#staff_salary').val(staffSalary);
        $('#other_input').val(otherInput);
        $('#offline_logistics_fee').val(offlineLogisticsFee);
    }

    function _onSaveSucc() {
        alert("编辑库房费用成功！");
        window.location.reload();
    }

})();