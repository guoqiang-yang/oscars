(function () {

    function main() {
        $('#_j_save_invoice').click(_onSaveInvoice);
        $('.delete_invoice').click(_onDeleteInvoice);
        $('.add_bill_ids').click(_onShowBillIds);
        $('._j_add_bill_ids').click(_onAddBillIds);
        $('#_j_sale_audit_invoice').click(_onSaleAuditInvoice);
        $('#_j_rebut_invoice').click(_onRebutInvoice);
        $('#_j_finance_confirm_invoice').click(_onFinanceConfirmInvoice);
        $('#_j_finished_invoice').click(_onFinishedInvoice);
        $('#add_product').click(onOrderSelectProduct);
        $('.delete_input_product').click(_onDeleteInputProduct);
        $('._j_invoice_order_amount').change(_onShowServiceAmount);
        $('._j_invoice_type_change').change(_onChangeInvoiceType);
        $("#BillIdsModal").on('click','._j_search_bill_oid', onGetBillIds);
        $("#BillIdsModal").on('click','._j_search_invoice_output',onGetBillIds);
        switch ($('#navbar').attr('data-step')) {
            case '1':
                $($('#navbar li')[1]).attr('class','active');
                break;
            case '3':
                $($('#navbar li')[2]).attr('class','active');
                break;
            case '5':
                $($('#navbar li')[3]).attr('class','active');
                break;
            case '2':
                $($('#navbar li')[4]).attr('class','active');
                break;
            case '99':
                $($('#navbar li')[5]).attr('class','active');
                break;
            default:
                $($('#navbar li')[0]).attr('class','active');
                break;
        }
        switch ($('#navbar2').attr('data-step')) {
            case '3':
                $($('#navbar2 li')[1]).attr('class','active');
                break;
            case '4':
                $($('#navbar2 li')[2]).attr('class','active');
                break;
            case '5':
                $($('#navbar2 li')[3]).attr('class','active');
                break;
            default:
                $($('#navbar2 li')[0]).attr('class','active');
                break;
        }
    }

    //显示服务费提示
    function _onShowServiceAmount() {
        var amount = $('input[name=invoice_amount]').val();
        if(amount > 0)
        {
            var amount1 = amount * 8 / 92;
            var amount2 = amount * 4 / 96;
            var html = '*开票服务费(MAX)：¥ '+amount1.toFixed(2)+'，不开票服务票：¥ '+amount2.toFixed(2);
            $('#service_tip').html(html);
        }else{
            $('#service_tip').html('');
        }
    }

    //修改发票类型删除开票订单id
    function _onChangeInvoiceType() {
        $('#bill_ids').html('');
        $('#show_order_amount').html('0.00');
    }

    //显示订单ID
    function _onShowBillIds() {
        var id = $('input[name=cid]').val();
        var invoice_type = $('select[name=invoice_type]').val();
        if(id>0){
            if(invoice_type == 0)
            {
                alert('请选择开票类型');
                return false;
            }
            $("#BillIdsModal").modal('show');
            onGetBillIds();
        }else{
            alert('请选择客户后再添加');
            return false;
        }
    }
    //获取订单ID
    function onGetBillIds(){
        var start_num = $(this).attr('data-start');

        if ("undefined" == typeof start_num){
            start_num = 0;
        }
        var para = {
            cid: $('input[name=cid]').val(),
            invoice_type: $('select[name=invoice_type]').val(),
            oid: $('input[name=search_oid]').val(),
            start: start_num
        };
        K.post('/finance/ajax/get_output_bills_ids.php', para, function (ret) {
            $('#BillIdsModal .modal-body').html(ret.html);
            bindEvent2Order();
        });
    }
    //添加订单ID
    function _onAddBillIds() {
        var price_amount = parseFloat($('#show_price_amount').html());
        var customerCarriage_amount = parseFloat($('#show_customerCarriage_amount').html());
        var order_amount = parseFloat($('#show_order_amount').html());
        $('input[name=account_bill]').each(function () {
                if ($(this).is(':checked')){
                    var id = $(this).val();
                    if($("#bill_"+id).length > 0){
                    }else{
                        var html = '<tr id="bill_'+id+'"><td><input type="hidden" name="bill_ids" value="'+id+'">订单ID：<a href="/order/order_detail.php?oid='+id+'" target="_blank">'+id+'</a></td><td>'+$(this).attr('data-payment-type')+'</td><td>'+$(this).attr('data-time')+'</td><td>'+$(this).attr('data-price')+'</td><td>'+$(this).attr('data-customer_carriage')+'</td><td>'+$(this).attr('data-amount')+'</td><td><a href="javascript:;" onclick="_onDeleteBillId(this);">移除</a></td></tr>';
                        $('#bill_ids').append(html);
                        price_amount += parseFloat($(this).attr('data-price'));
                        customerCarriage_amount+= parseFloat($(this).attr('data-customer_carriage'));
                        order_amount += parseFloat($(this).attr('data-amount'));

                    }
                }
            }
        );
        $('#show_price_amount').html(price_amount.toFixed(2));
        $('#show_customerCarriage_amount').html(customerCarriage_amount.toFixed(2));
        $('#show_order_amount').html(order_amount.toFixed(2));

        $('#BillIdsModal').modal('hide');
    }

    // 添加/编辑发票
    function _onSaveInvoice(ev) {

        var para = {
            id: $('input[name=id]').val(),
            cid: $('input[name=cid]').val(),
            contract_number: $('input[name=contract_number]').val(),
            city_id: $('select[name=city_id]').val(),
            invoice_type: $('select[name=invoice_type]').val(),
            title: $('input[name=title]').val(),
            pay_company: $('input[name=pay_company]').val(),
            invoice_amount: $('input[name=invoice_amount]').val(),
            service_type: $('select[name=service_type]').val(),
            service_amount: $('input[name=service_amount]').val(),
            invoice_day: $('input[name=invoice_day]').val(),
            batch: $('input[name=batch]').val(),
            number: $('input[name=number]').val(),
        };

        if(para.cid == '')
        {
            alert('客户ID错误！');
            return false;
        }

        if (para.contract_number == ''){
            alert('合同编号必填！');
            return false;
        }

        if (para.city_id == 0){
            alert('城市必选！');
            return false;
        }

        if (para.invoice_type == 0)
        {
            alert('发票类型必选！');
            return false;
        }


        if (para.title == '')
        {
            alert('开票名称必填！');
            return false;
        }

        if (para.pay_company == '')
        {
            alert('付款单位必填！');
            return false;
        }

        if (para.invoice_amount == '' || para.invoice_amount == 0)
        {
            alert('开票订单金额必填！');
            return false;
        }

        var reg = /^[0-9]+.?[0-9]*$/;//用来验证数字，包括小数的正则
        if(!reg.test(para.invoice_amount))
        {
            alert("开票订单金额格式错误，请输入正确的数字格式！");
            return false;
        }

        if (para.service_type == 0)
        {
            alert('服务费类型必选！');
            return false;
        }

        if (para.service_amount == '' || para.service_amount == 0)
        {
            alert('服务费金额必填！');
            return false;
        }

        if(!reg.test(para.service_amount))
        {
            alert("服务费金额格式错误，请输入正确的数字格式！");
            return false;
        }

        para.invoice_amount = Math.round(para.invoice_amount*100);
        para.service_amount = Math.round(para.service_amount*100);
        var bill_num = 0;
        var bill_arr = new Array();
        $("input[name=bill_ids]").each(function(){
            bill_arr.push($(this).val());
            bill_num += 1;
        });
        if(bill_num == 0)
        {
            alert('请添加开票订单！');
            return false;
        }

        if(para.invoice_amount > Math.round($('#show_order_amount').html()*100))
        {
            alert('开票订单金额不能大于订单实付金额！');
            return false;
        }

        if($(this).data('step') > 3){
            if (para.invoice_day == '')
            {
                alert('开票日期必填！');
                return false;
            }

            //日期格式yyyy-mm-dd
            DATE_FORMAT= /^(\d{4})-(0\d{1}|1[0-2])-(0\d{1}|[12]\d{1}|3[01])$/;
            if(!DATE_FORMAT.test(para.invoice_day))
            {
                alert('开票日期格式错误');
                return false;
            }

            if (para.batch == '')
            {
                alert('批次必填！');
                return false;
            }

            if(para.number == '')
            {
                alert('票号必填！');
                return false;
            }
        }


        var old_city = $('select[name=city_id]').attr('data-city');
        if(old_city>0 && para.city_id != old_city)
        {
            var msg = "您真的要修改开票城市吗？\n\n请确认！\n\n(修改后会清空相关发票商品信息)";
            if(confirm(msg) == false)
            {
                return false;
            }
        }

        para.bill_ids = bill_arr;
        $(this).attr('disabled', true);
        K.post('/finance/ajax/save_output_invoice.php', para, _onSaveInvoiceSuccess, _onSaveInvoiceiFail);
    }

    //销售审核发票
    function _onSaleAuditInvoice(ev)
    {
        var msg = "您真的要审核通过该发票吗？\n\n请确认！\n\n(修改发票信息请先保存)";
        if(confirm(msg) == true)
        {
            $(this).attr('disabled', true);
            var para = {
                method: 'sale_audit',
                id: $(this).attr('data-id'),
            };
            K.post('/finance/ajax/change_output_invoice.php', para, _onDoSuccess);
        }
    }

    //驳回发票
    function _onRebutInvoice(ev)
    {
        var msg = "您真的要驳回该发票吗？\n\n请确认！\n\n(修改发票信息请先保存)";
        if(confirm(msg) == true)
        {
            $(this).attr('disabled', true);
            var para = {
                method: 'rebut',
                id: $(this).attr('data-id'),
            };
            if($(this).data('step') == 1) {
                K.post('/finance/ajax/change_output_invoice.php', para, _onDoSuccess);
            }else{
                K.post('/finance/ajax/change_output_invoice.php', para, _onDoSuccess2);
            }
        }
    }

    //财务确认发票
    function _onFinanceConfirmInvoice(ev)
    {
        var msg = "您真的要确认该发票吗？\n\n请确认！\n\n(修改发票信息请先保存)";
        if(confirm(msg) == true)
        {
            $(this).attr('disabled', true);
            var para = {
                method: 'finance_confirm',
                id: $(this).attr('data-id'),
            };
            K.post('/finance/ajax/change_output_invoice.php', para, _onSaveInvoiceSuccess);
        }
    }

    //完成发票
    function _onFinishedInvoice(ev)
    {
        var msg = "您真的要完成该发票吗？\n\n请确认！\n\n(修改发票信息请先保存)";
        if(confirm(msg) == true)
        {
            $(this).attr('disabled', true);
            var para = {
                method: 'finished',
                id: $(this).attr('data-id'),
            };
            K.post('/finance/ajax/change_output_invoice.php', para, _onDoSuccess2);
        }
    }

    //删除发票
    function _onDeleteInvoice(ev) {
        var msg = "您真的确定要删除吗？\n\n请确认！";
        if (confirm(msg) == true) {
            var para = {
                method: 'delete',
                id: $(this).attr('data-id')
            };

            K.post('/finance/ajax/change_output_invoice.php', para, _onDoSuccess);
            return true;
        } else {
            return false;
        }
    }

    function _onDoSuccess(data) {
        alert('操作成功');
        window.location.href = '/crm2/invoice_list.php';
    }

    function _onDoSuccess2(data) {
        alert('操作成功');
        window.location.href = '/finance/output_invoice_list.php';
    }

    function _onSaveInvoiceSuccess(data) {
        var id = $('input[name=id]').val();
        if(id)
        {
            alert('保存成功');
            window.location.reload();
        }else{
            alert('保存成功');
            window.location.href = '/crm2/edit_invoice.php?id='+data.id;
        }
    }

    function _onSaveInvoiceiFail(data) {
        alert(data.errmsg);
        $('#_j_save_invoice').attr('disabled', false);
    }

    // 删除发票商品
    function _onDeleteInputProduct(ev) {
        var para = {
            id: $(this).attr('data-id'),
            pid: $(this).attr('data-pid'),
            method: 'delete'
        };
        if (confirm('确认删除该商品(pid:'+para.pid+')？')) {
            K.post('/finance/ajax/save_output_invoice_products.php', para, _onSaveInvoiceSuccess);
        }
    }



    // 保存商品
    function onSaveProducts(ev) {
        var curProducts = new Array();
        var tmp_key = 0;
        var flag = false;
        $('._j_product_item').each(function() {
            var cb = $(this),
                pid = cb.data('pid'),
                num = Math.round(cb.find('input[name=num]').val()),
                cost = cb.data('cost'),
                price = Math.round(100*parseFloat(cb.find('input[name=price]').val()));
            if (K.isNumber(num)) {
                if (num > 0) {
                    if(price == 0)
                    {
                        alert('商品（pid:'+pid+'）价格不能为0');
                        flag = true;
                        return false;
                    }
                    if(num > (cb.data('num')+cb.data('num2')))
                    {
                        alert('商品（pid:'+pid+'）开票数量不能超过剩余数量');
                        flag = true;
                        return false;
                    }
                    var product = {
                        pid: pid, num: num,price: price,cost: cost
                    };
                    curProducts[tmp_key] = product;
                    tmp_key++;
                }
            }
        });
        if(flag){
            return false;
        }
        if(curProducts.length>0)
        {
            var para = {
                id: $('input[name=id]').val(),
                products: curProducts,
                method: 'add'
            };
            K.post('/finance/ajax/save_output_invoice_products.php', para, _onSaveInvoiceSuccess);
        }
        $('#dlgAddProduct').modal('hide');
    }

    // 选择商品
    function onOrderSelectProduct(ev) {
        ev.preventDefault();

        var tgt = $(ev.currentTarget),
            para = {id: $('input[name=id]').val(), href : tgt.attr('href')};

        K.post('/finance/ajax/dlg_get_output_products.php', para, _onGetProductsSuccess);
    }

    //显示结果，重新绑定事件
    function _onGetProductsSuccess(data) {
        $('#product_list_container').html('').append($(data.html));

        bindEvent();
    }

    //回车搜索
    function onOrderSearchProductKeydown(ev) {
        if(event.keyCode==13) {
            onOrderSearchProduct(ev);
        }
    }

    //搜索
    function onOrderSearchProduct(ev) {
        ev.preventDefault();

        var tgt = $(ev.currentTarget),
            id = $('#dlgAddProduct').data('id'),
            keyword = tgt.closest('._j_form').find('input[name=keyword]').val(),
            para = {keyword : keyword, id : id}

        K.post('/finance/ajax/dlg_get_output_products.php', para, _onOrderSearchProductSuccess);
    }

    //显示结果，重新绑定事件
    function _onOrderSearchProductSuccess(data) {
        $('#product_list_container').html('').append($(data.html));

        bindEvent();
    }
    function bindEvent() {
        $('a._j_invoice_select_product').unbind('click').bind('click', onOrderSelectProduct);
        $('._j_invoice_search_product').unbind('click').bind('click', onOrderSearchProduct);
        $('._j_invoice_search_product').closest('._j_form').find('input[name=keyword]').unbind('keydown').bind('keydown', onOrderSearchProductKeydown);
        $('#_j_btn_save_products').unbind('click').bind('click', onSaveProducts);
        $('#_j_btn_save_products2').unbind('click').bind('click', onSaveProducts);
    }
    function bindEvent2Order() {
        $('#checkAll').unbind('click').bind('click', checkAll);
    }

    main();

})();

// 删除单据
function _onDeleteBillId(obj) {
    var del_amount = parseFloat($(obj).parent().prev().html());
    var del_customerCarriage_amount = parseFloat($(obj).parent().prev().prev().html());
    var del_price_amount = parseFloat($(obj).parent().prev().prev().prev().html());

    var price_amount = parseFloat($('#show_price_amount').html());
    var customerCarriage_amount = parseFloat($('#show_customerCarriage_amount').html());
    var order_amount = parseFloat($('#show_order_amount').html());

    order_amount = order_amount-del_amount;
    price_amount = price_amount-del_price_amount;
    customerCarriage_amount = customerCarriage_amount-del_customerCarriage_amount;

    $('#show_order_amount').html(order_amount.toFixed(2));
    $('#show_customerCarriage_amount').html(customerCarriage_amount.toFixed(2));
    $('#show_price_amount').html(price_amount.toFixed(2));

    $(obj).parent().parent().remove();
}

//全选和反选逻辑
function checkAll(){
    $("#bill_in_list input[type='checkbox']").prop('checked', $(this).prop('checked'));
}

function checkAllOrder(){
    var orderNum = $("input[name='account_bill']").length;
    var checkedOrderIdNum = $("input[name='account_bill']:checked").length;

    if (orderNum == checkedOrderIdNum){
        $("#checkAll").prop("checked",true);
    }else{
        $("#checkAll").prop("checked",false);
    }
}