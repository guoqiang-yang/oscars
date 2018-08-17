(function () {

    function main() {
        $('#_j_save_invoice').click(_onSaveInvoice);
        $('.delete_invoice').click(_onDeleteInvoice);
        $('.add_bill_ids').click(_onShowBillIds);
        $('._j_add_bill_ids').click(_onAddBillIds);
        $('#add_bill_by_hand').click(_onAddBillIdsByHand);
        $('#_j_confirm_invoice').click(_onConfirmInvoice);
        $('#_j_finished_invoice').click(_onFinishedInvoice);
        $('#add_product').click(onOrderSelectProduct);
        $('.delete_input_product').click(_onDeleteInputProduct);
        switch ($('#navbar').attr('data-step')) {
            case '1':
                $($('#navbar li')[1]).attr('class','active');
                break;
            case '2':
                $($('#navbar li')[2]).attr('class','active');
                break;
            case '3':
                $($('#navbar li')[3]).attr('class','active');
                break;
            default:
                $($('#navbar li')[0]).attr('class','active');
                break;
        }
    }

    //添加结算单、采购单ID
    function _onShowBillIds() {
        var id = $('input[name=supplier_id]').val();
        if(id>0){
            var para = {
                sid: id
            };
            $("#BillIdsModal").modal('show');
            K.post('/finance/ajax/get_input_bills_ids.php', para, function (ret) {
                $('#BillIdsModal .modal-body').html(ret.html);
                $('#checkAll').click(checkAll);
            });
        }else{
            alert('请选择供应商后再添加');
        }
    }

    function _onAddBillIds() {
        $('input[name=account_bill]').each(function () {
                if ($(this).is(':checked')){
                    var id = $(this).val();
                    var type = $(this).attr('data-type');
                    if($("#bill_"+type+'_'+id).length > 0){
                    }else{
                        if(type == 1){
                            var html = '<div id="bill_'+type+'_'+id+'"><input type="hidden" name="bill_ids" value="'+type+'_'+id+'">采购单ID：<a href="/warehouse/detail_in_order.php?oid='+id+'" target="_blank">'+id+'</a>&emsp;<span><a href="javascript:;" onclick="_onDeleteBillId(this);">X</a></span></div>';
                        }else{
                            var html = '<div id="bill_'+type+'_'+id+'"><input type="hidden" name="bill_ids" value="'+type+'_'+id+'">结算单ID：<a href="/finance/stockin_statement_detail.php?statement_id='+id+'" target="_blank">'+id+'</a>&emsp;<span><a href="javascript:;" onclick="_onDeleteBillId(this);">X</a></span></div>';
                        }
                        $('#bill_ids').append(html);
                    }
                }
            }
        );
        $('#BillIdsModal').modal('hide');
    }
    function _onAddBillIdsByHand() {
        var id = parseInt($('#add_bill_by_hand').prev().prev().val());
        if(!id)
        {
            alert('请先填写结算单/采购单ID');
            return;
        }
        var type = $('#add_bill_by_hand').prev().val();
        if($("#bill_"+type+'_'+id).length > 0){
        }else{
            if(type == 1){
                var html = '<div id="bill_'+type+'_'+id+'"><input type="hidden" name="bill_ids" value="'+type+'_'+id+'">采购单ID：<a href="/warehouse/detail_in_order.php?oid='+id+'" target="_blank">'+id+'</a>&emsp;<span><a href="javascript:;" onclick="_onDeleteBillId(this);">X</a></span></div>';
            }else{
                var html = '<div id="bill_'+type+'_'+id+'"><input type="hidden" name="bill_ids" value="'+type+'_'+id+'">结算单ID：<a href="/finance/stockin_statement_detail.php?statement_id='+id+'" target="_blank">'+id+'</a>&emsp;<span><a href="javascript:;" onclick="_onDeleteBillId(this);">X</a></span></div>';
            }
            $('#bill_ids').append(html);
        }
        $('#BillIdsModal').modal('hide');
    }

    // 添加/编辑发票
    function _onSaveInvoice(ev) {

        var para = {
            id: $('input[name=id]').val(),
            supplier_id: $('input[name=supplier_id]').val(),
            name: $('input[name=name]').val(),
            city_id: $('select[name=city_id]').val(),
            title: $('input[name=title]').val(),
            amount: $('input[name=amount]').val(),
            invoice_day: $('input[name=invoice_day]').val(),
            batch: $('input[name=batch]').val(),
            number: $('input[name=number]').val(),
            invoice_num: $('select[name=invoice_num]').val(),
            invoice_type: $('select[name=invoice_type]').val(),
        };

        if(para.supplier_id == '')
        {
            alert('供应商ID错误！');
            return false;
        }

        if (para.name == ''){
            alert('开票供应商必填！');
            return false;
        }

        if (para.city_id == 0){
            alert('城市必选！');
            return false;
        }

        if (para.invoice_type == 0)
        {
            alert('开票类型必选！');
            return false;
        }


        if (para.title == '')
        {
            alert('开票名称必填！');
            return false;
        }

        if (para.amount == '' || para.amount == 0)
        {
            alert('开票金额必填！');
            return false;
        }

        var reg = /^[0-9]+.?[0-9]*$/;//用来验证数字，包括小数的正则
        if(!reg.test(para.amount))
        {
            alert("开票金额格式错误，请输入正确的数字格式！");
            return false;
        }
        para.amount = Math.round(para.amount*100);
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

        var bill_num = 0;
        var bill_arr = new Array();
        $("input[name=bill_ids]").each(function(){
            bill_arr.push($(this).val());
            bill_num += 1;
        });
        if($('input[name=step]').val() > 1 && para.id != '' && bill_num == 0)
        {
            alert('请添加开票单据！');
            return false;
        }
        var invoice_num = $('#bill_ids').attr('data-num');
        if(invoice_num == 1 && bill_num > 1)
        {
            alert('该发票原来对应的开票单据有多张发票，只能添加一张开票单据');
            return false;
        }

        if(para.invoice_num > 1 && bill_num>1)
        {
            alert('要生成多张发票只能对应一张开票单据');
            return false;
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
        K.post('/finance/ajax/save_input_invoice.php', para, _onSaveInvoiceSuccess);
    }

    //确认发票
    function _onConfirmInvoice(ev)
    {
        var bill_num = 0;
        $("input[name=bill_ids]").each(function(){
            bill_num += 1;
        });
        if(bill_num == 0)
        {
            alert('请添加开票单据！');
            return false;
        }
        var msg = "您真的要确认该发票吗？\n\n请确认！\n\n(修改发票信息请先保存)";
        if(confirm(msg) == true)
        {
            $(this).attr('disabled', true);
            var para = {
                method: 'confirm',
                id: $(this).attr('data-id'),
            };
            K.post('/finance/ajax/change_input_invoice.php', para, _onDoSuccess);
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
            K.post('/finance/ajax/change_input_invoice.php', para, _onDoSuccess);
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

            K.post('/finance/ajax/change_input_invoice.php', para, _onDoSuccess);
            return true;
        } else {
            return false;
        }
    }

    function _onDoSuccess(data) {
        alert('操作成功');
        window.location.reload();
    }

    function _onSaveInvoiceSuccess(data) {
        var id = $('input[name=id]').val();
        if(id)
        {
            alert('保存成功');
            window.location.reload();
        }else{
            alert('保存成功');
            window.location.href = '/finance/input_invoice_list.php?supplier_id='+data.supplier_id;
        }
    }

    // 删除发票商品
    function _onDeleteInputProduct(ev) {
        var para = {
            id: $(this).attr('data-id'),
            pid: $(this).attr('data-pid'),
            method: 'delete'
        };
        if (confirm('确认删除该商品(pid:'+para.pid+')？')) {
            K.post('/finance/ajax/save_input_invoice_products.php', para, _onDoSuccess);
        }
    }



    // 保存商品
    function onSaveProducts(ev) {
        var curProducts = new Array();
        var tmp_key = 0;
        var flag = true;
        $('._j_product_item').each(function() {
            var cb = $(this),
                pid = cb.data('pid'),
                num = Math.round(cb.find('input[name=num]').val()),
                amount = Math.round(100*parseFloat(cb.find('input[name=amount]').val())),
                tax_rate = cb.find('input[name=tax_rate]').val(),
                tax_amount = Math.round(100*parseFloat(cb.find('input[name=tax_amount]').val()));
                if (num > 0) {
                    if(amount == 0)
                    {
                        alert('商品（pid:'+pid+'）金额不能为0');
                        flag = false;
                        return false;
                    }
                    if(tax_rate == 0)
                    {
                        alert('商品（pid:'+pid+'）税率不能为0');
                        flag = false;
                        return false;
                    }
                    if(tax_amount == 0)
                    {
                        alert('商品（pid:'+pid+'）税额不能为0');
                        flag = false;
                        return false;
                    }
                    var product = {
                        pid: pid, num: num, amount: amount, tax_rate: tax_rate, tax_amount: tax_amount
                    };
                    curProducts[tmp_key] = product;
                    tmp_key++;
                }
        });
        if(flag)
        {
            if(curProducts.length>0)
            {
                var para = {
                    id: $('input[name=id]').val(),
                    products: curProducts,
                    method: 'add'
                };
                K.post('/finance/ajax/save_input_invoice_products.php', para, _onDoSuccess);
            }
            $('#dlgAddProduct').modal('hide');
        }
    }

    // 选择商品
    function onOrderSelectProduct(ev) {
        ev.preventDefault();

        var tgt = $(ev.currentTarget),
            para = {id: $('input[name=id]').val(), href : tgt.attr('href')};

        K.post('/finance/ajax/dlg_get_products.php', para, _onGetProductsSuccess);
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

        K.post('/finance/ajax/dlg_get_products.php', para, _onOrderSearchProductSuccess);
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

    main();

})();

// 删除单据
function _onDeleteBillId(obj) {
    $(obj).parent().parent().remove();
}

//全选和反选逻辑
function checkAll(){
    $("#bill_in_list input[type='checkbox']").prop('checked', $(this).prop('checked'));
}