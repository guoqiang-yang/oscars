(function(){
    var $tpPass = $('#tp_pass');
    var $tpRefuse = $('#tp_refuse');
    var $hcPass = $('#hc_pass');
    var $hcRefuse = $('#hc_refuse');
    var $hcCredit = $('#hc_credit');
    var id = $('#id').val();


    $tpPass.bind('click', tpPass);
    $tpRefuse.bind('click', tpRefuse);
    $hcPass.bind('click', hcPass);
    $hcRefuse.bind('click', hcRefuse);
    $hcCredit.bind('click', hcCredit);


    function tpPass() {
        var tpTotalAmount = parseFloat($('#tp_total_amount').val());
        var tpDueDate = parseInt($('#tp_due_date').val());

        if (isNaN(tpTotalAmount) || tpTotalAmount <= 0) {
            alert('请填写正确的第三方授信额度！');
            return false;
        }
        if (isNaN(tpDueDate) || tpDueDate <= 0) {
            alert('请填写正确的第三方授信期限！');
            return false;
        }

        var para = {id: id, tp_total_amount: tpTotalAmount, tp_due_date: tpDueDate, new_step: 11};
        K.post('/activity/ajax/update_finance_apply.php', para, upSucc);
    }

    function tpRefuse() {
        if (confirm('确定第三方已经拒绝了该申请？')) {
            var para = {id: id, new_step: -1};
            K.post('/activity/ajax/update_finance_apply.php', para, upSucc);
        }
    }

    function hcPass() {
        var hcTotalAmount = parseFloat($('#hc_total_amount').val());
        var hcDueDate = parseInt($('#hc_due_date').val());
        var tpTotalAmount = parseFloat($('#tp_total_amount_val').val());
        var tpDueDate = parseInt($('#tp_due_date_val').val());

        if (isNaN(hcTotalAmount) || hcTotalAmount <= 0) {
            alert('请填写正确的好材授信额度！');
            return false;
        }
        if (hcTotalAmount > tpTotalAmount) {
            alert('好材授信额度不能大于第三方授信额度！');
            return false;
        }
        if (isNaN(hcDueDate) || hcDueDate <= 0) {
            alert('请填写正确的好材授信期限！');
            return false;
        }
        if (hcDueDate > tpDueDate) {
            alert('好材授信期限不能大于第三方授信期限！');
            return false;
        }

        var para = {id: id, hc_total_amount: hcTotalAmount, hc_due_date: hcDueDate, new_step: 22};
        K.post('/activity/ajax/update_finance_apply.php', para, upSucc);
    }

    function hcRefuse() {
        if (confirm('确定好材已经对拒绝了该申请？')) {
            var para = {id: id, new_step: -11};
            K.post('/activity/ajax/update_finance_apply.php', para, upSucc);
        }
    }

    function hcCredit() {
        if (confirm("确定要对该用户授信吗？\n\n授信之后，用户即可使用授信账户消费。")) {
            var para = {id: id, new_step: 33};
            K.post('/activity/ajax/update_finance_apply.php', para, upSucc);
        }
    }

    function upSucc() {
        window.location.reload();
    }
})();