(function () {

    function main() {
        $('.add_express_info').click(_onShowExpress);
        $('.edit_express_info').click(_onShowExpress);
        $('._j_save_tracking').click(_onSaveExpress);
    }

    // 显示快递弹窗
    function _onShowExpress(ev) {
        $('#edit_oid').val($(this).attr('data-oid'));
        $('#edit_express').val($(this).attr('data-express'));
        $('#edit_tracking_num').val($(this).attr('data-tracking_num'));
        $('#edit_freight').val($(this).attr('data-freight'));
        $('#editTrackingModal').modal('show');
    }

    // 编辑快递信息
    function _onSaveExpress(ev) {

        var para = {
            oid: $('#edit_oid').val(),
            express: $('#edit_express').val(),
            tracking_num: $('#edit_tracking_num').val(),
            freight: $('#edit_freight').val(),
        };

        if (para.express == ''){
            alert('快递公司必选！');
            return false;
        }

        if (para.tracking_num == '')
        {
            alert('快递单号必填！');
            return false;
        }

        if (para.freight == '')
        {
            alert('运费必填！');
            return false;
        }

        K.post('/activity/ajax/save_cpoint_product_express.php', para, _onSaveExpresssSuccess);
    }

    function _onSaveExpresssSuccess(data) {
        alert('操作成功');
        window.location.reload();
    }

    main();

})();