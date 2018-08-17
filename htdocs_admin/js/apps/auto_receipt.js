(function () {

    function main() {
        inputOid();
        $('#order_list_area').on('click',orderListArea);
        $('#select_all_orders').on('click', selectAllOrders);
        $('#bluk_receipt').on('click', blukAutoReceipt);
        $('#payment_modal').on('click', saveMoneyIn);
    }

    function orderListArea(event) {
        if ($(event.target).hasClass('cancel_order_show'))
        {
            $(event.target).closest('tr').remove();
            var total = $('tr').size() - 1;
            $('#total').html(total);
            if ($('tr').size() == 1)
            {
                $('.bluk_auto_receipt').remove();
            }
        }

        if ($(event.target).hasClass('payment_order_show'))
        {
            var oid = $(event.target).data('oid');
            var type = $(event.target).data('type');
            var para = {oid:oid, type:type};
            K.post('/order/ajax/get_order_info.php', para, function (ret) {
                $('#payment_modal').append(ret.html);
                $('#editFinanceModal').modal();
            });
        }
    }
    
    function inputOid() {
        var total = $('tr').size() - 1;
        $('#total').html(total);
        $(document).keydown(function (event) {
            if (event.keyCode == 13)
            {
                var oid = $('input[name=oid]').val();
                var oids = [];
                $('#order_list_area').find('tr').each(function () {
                    oids.push($(this).data('oid'));
                });
                var show_oids = oids.join(',');
                if (oid)
                {
                    var para = {oid:oid,show_oids:show_oids,type:'order_show'};
                    K.post('/order/ajax/get_order_info.php', para, function (ret) {
                        if (ret.html)
                        {
                            $('#order_list_area').append(ret.html);
                            var total = $('tr').size() - 1;
                            var bluk_receipt_button = $('.bluk_auto_receipt').size();
                            $('#total').html(total);
                            if (total != 0 && bluk_receipt_button == 0)
                            {
                                $('#bluk_receipt').append('<a href="javascript:void(0);" class="btn btn-primary bluk_auto_receipt" style="float:right;margin-left: 20px;">批量回单</a>');
                            }
                        }
                        else
                        {
                            alert('订单号不存在，请点击确定后继续扫码！');
                        }
                        $('input[name=oid]').val('');
                    });
                    event.preventDefault();
                }
                else
                {
                    alert('订单号为空，请输入订单号！');
                }
            }
        });
    }
    
    function selectAllOrders() {
        if ($('#select_all_orders').is(':checked')) //全选
        {
            if($('#order_list_area').find('input[name=oid]').length){
                $('#order_list_area').find('input[name=oid]').each(function(){
                    this.checked=true;
                });
            } else if ($('#order_list_area').find('input[name=oid]').length) {
                $('#order_list_area').find('input[name=oid]').each(function(){
                    this.checked=true;
                });
            }
        }
        else //取消全选
        {
            if($('#order_list_area').find('input[name=oid]').length){
                $('#order_list_area').find('input[name=oid]').each(function(){
                    this.checked=false;
                });
            } else if ($('#order_list_area').find('input[name=oid]').length) {
                $('#order_list_area').find('input[name=oid]').each(function(){
                    this.checked=false;
                });
            }
        }
    }
    
    function blukAutoReceipt(event) {
        if ($(event.target).hasClass('bluk_auto_receipt'))
        {
            var oids = [];
            var oids_list = $('input[name=oid]');
            oids_list.each(function () {
                if ($(this).is(':checked'))
                {
                    oids.push($(this).data('oid'));
                }
            });

            if (oids.length == 0){
                alert('请勾选订单！'); return false;
            }
            $('.bluk_auto_receipt').attr('disabled', true);
            var para = {oids:oids};
            
            K.post('/order/ajax/auto_bluk_receipt.php', para, function (ret) {
                if (ret.oids)
                {
                    alert ('操作成功！');
                    window.location.reload();
                }
                else
                {
                    $('.bluk_auto_receipt').attr('disabled', false);
                }
            });
        }
    }

    function saveMoneyIn(ev) {
        if ($(event.target).hasClass('_j_save_money_in'))
        {
            ev.preventDefault();
            var tgt = $(ev.currentTarget),
                box = tgt.find('#editFinanceModal'),
                price = box.find('input[name=price]').val(),
                note = box.find('textarea[name=note]').val(),
                payType = box.find('select[name=payment_type]').val(),
                moling = box.find('input[name=moling]').val(),
                useBalance = box.find('input[name=use_balance]').is(':checked'),
                discount = box.find('input[name=discount]').val(),
                badDebt = box.find('input[name=bad_debt]').val(),

                para = {
                    cid: $(ev.target).data('cid'),
                    uid: $(ev.target).data('uid'),
                    type: $(ev.target).data('type'),
                    objid: $(ev.target).data('objid'),
                    wid: $(ev.target).data('wid'),
                    price: price,
                    note: note,
                    payment_type: payType,
                    //paid: paid,
                    moling: moling,
                    discount: discount,
                    use_balance: useBalance ? 1 : 0,
                    bad_debt: badDebt,
                    service_fee: box.find('input[name=service_fee]').val()
                };
                var  objid = $(ev.target).data('objid');
            $(this).attr('disabled', true);
            K.post('/finance/ajax/save_money_in.php', para, function () {
                alert('操作已成功');
                var rows =  $('#order_list_area').find('tr');
                rows.each(function () {
                    if($(this).data('oid') == objid)
                    {
                        $(this).remove();
                        var total = $('tr').size() - 1;
                        $('#total').html(total);
                        if ($('tr').size() == 1)
                        {
                            $('.bluk_auto_receipt').remove();
                        }
                    }
                });
                $('#editFinanceModal').modal('hide');
                $(this).attr('disabled', false);
            });
        }
    }

    main();
})();