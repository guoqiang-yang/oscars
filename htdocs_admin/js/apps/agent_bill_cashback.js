/**
 * Created by zouliangwei on 2017/1/16.
 */
(function () {

    function main() {
        $('#agent_bill_cashback_pay').on('click', onAgentBillCashbackPay);
    }

    function onAgentBillCashbackPay() {
        if(!confirm('确认要进行付款操作？'))
        {
            return false;
        }
        $(this).attr("disabled", true);
        var para = {
            id: $(this).data('id')
            };
        K.post('/finance/ajax/agent_bill_cashback_pay.php', para, function (ret) {
            alert('付款成功');
            window.location.reload();
        });
    }
    main();
})();