(function () {

    function main() {
        $('._j_add_agent_amount').click(_onAddAgentAmount);
        $('#add_agent_amount').click(_onShowAddAgentModal);
        $('#withdraw_agent_amount').click(_onShowWithdrawAgentModal);
        $('._j_withdraw_agent_amount').click(_onWithdrawAgentAmount);
    }

    // 预存
    function _onAddAgentAmount(ev) {

        var para = {
            aid: $('#addAgentAmountModal').find('input[name=aid]').val(),
            price: $('#addAgentAmountModal').find('input[name=price]').val(),
            payment: $('#addAgentAmountModal').find('select[name=type]').val(),
            type: 'add'
        };

        if (para.price == ''){
            alert('预存金额必填！');
            return false;
        }
        para.price = Math.round(para.price*100);

        K.post('/finance/ajax/save_agent_amount.php', para, _onSaveSuccess);
    }

    //显示预存
    function _onShowAddAgentModal(ev) {
        $('#addAgentAmountModal').find('input[name=aid]').val($(this).attr('data-aid'));
        $('#addAgentAmountModal').find('input[name=price]').val('');
        $('#addAgentAmountModal').modal('show');
    }

    //显示提现
    function _onShowWithdrawAgentModal(ev) {
        $('#withdrawAgentAmountModal').find('input[name=aid]').val($(this).attr('data-aid'));
        $('#withdrawAgentAmountModal').find('input[name=price]').val('');
        $('#withdrawAgentAmountModal').modal('show');
    }

    // 编辑商品
    function _onWithdrawAgentAmount(ev) {

        var para = {
            aid: $('#withdrawAgentAmountModal').find('input[name=aid]').val(),
            price: $('#withdrawAgentAmountModal').find('input[name=price]').val(),
            payment: $('#withdrawAgentAmountModal').find('select[name=type]').val(),
            type: 'withdraw'
        };

        if (para.price == ''){
            alert('提现金额必填！');
            return false;
        }
        para.price = Math.round(para.price*100);

        K.post('/finance/ajax/save_agent_amount.php', para, _onSaveSuccess);
    }

    function _onSaveSuccess(data) {
        alert('操作成功');
        window.location.reload();
    }

    main();

})();