"use strict";


/**
 * Created by qihua on 16/12/27.
 */
(function() {

    $('#save_point').bind("click", savePoint);


    function savePoint() {
        var point = parseInt($("#adjust_point").val());
        var note = $("#note").val();
        var uid = parseInt($("input[name=uid]").val());

        if (isNaN(uid) || uid <= 0) {
            alert("请选择要调整的用户！");
            return false;
        }
        if (isNaN(point) || point <= 0) {
            alert("请输入正确的增加分数！");
            return false;
        }
        if (K.isEmpty(note)) {
            alert("请输入调整分数的原因！");
            return false;
        }

        var para = {point: point, note: note, uid: uid};
        K.post("/crm2/ajax/change_point.php", para, _onChgSucc);
    }
    function _onChgSucc() {
        alert("调整成功！");
        window.location.reload();
    }

} )();