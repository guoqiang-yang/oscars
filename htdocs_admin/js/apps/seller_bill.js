(function () {

    function main() {
        $('._j_finance_seller_bill').click(_onSHowSellerBill);
        $('#_j_finance_seller_bill_submit').click(_onSaveSellerBill);
    }

    //显示结算信息
    function _onSHowSellerBill() {
        $('#_j_finance_seller_bill_submit').attr('disabled', false);
        var id = $(this).data('bid');
        if(id>0){
            var para = {
                bid: id
            };
            $("#sellerBillModal").modal('show');
            K.post('/finance/ajax/get_seller_bill_info.php', para, function (ret) {
                $('#sellerBillModal .modal-body').html(ret.html);
            });
        }
    }

    // 保存结算
    function _onSaveSellerBill(ev) {

        var para = {
            bid: $('#sell_bill_info').find('input[name=bid]').val(),
            real_amount: $('#sell_bill_info').find('input[name=real_amount]').val(),
            payment_type: $('#sell_bill_info').find('select[name=payment_type]').val(),
            note: $('#sell_bill_info').find('textarea[name=note]').val()
        };

        if(para.bid == '')
        {
            alert('商家结算单ID错误！');
            return false;
        }

        if (para.real_amount == ''){
            alert('实付金额必填！');
            return false;
        }

        var reg = /^[0-9]+.?[0-9]*$/;//用来验证数字，包括小数的正则
        if(!reg.test(para.real_amount))
        {
            alert("实付金额格式错误，请输入正确的数字格式！");
            return false;
        }

        if(para.real_amount > $('#sell_bill_amount').html())
        {
            alert("实付金额不能大于结算金额");
            return false;
        }

        para.real_amount = Math.round(para.real_amount*100);
        $(this).attr('disabled', true);
        K.post('/finance/ajax/seller_bill.php', para, _onDoSuccess);
    }


    function _onDoSuccess(data) {
        alert('结算成功');
        window.location.reload();
    }

    main();

})();
