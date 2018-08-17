(function(){
    
    function main(){
        $('.check_statement_detail').on('click',checkStatementDetail);
        $('#check_statement').on('click', checkStatement)
        $('.paid_coopworker').on('click', paidCoopworker);
        $('.bluk_pay_coopworker').on('click', blukPayCoopworker);
        $('._j_chg_order_step').on('click', orderBack);

        $('.print_coopworker_order').on('click', printCoopworkerOrder);
        $('.print_statement').on('click', print_statement);
        $('#statement_output').on('click', outputWarnning);

        $('#coopworker_pay_select_all').on('click', coopworkerPaySelectAll);
        $('#statement_select_all').on('click', statementSelectAll);
        $('.gen_coopworker_statement').on('click', checkWidIsEmpty);
        $('.cancel_statement').on('click', cancelStatement);
        $('.sure_statement').on('click', sureStatement);
        $('.pay_statement').on('click', payStatement);

        $('#generate_batch').on('click', generateBatch);
        $('#cancel_generate_batch').on('click', cancelGenerateBatch);
        $('#print_statement_finance').on('click', printStatementFinance);

        //工人结算--重庆分仓
        $('._j_settlement').on('click', coopworkerSettlement);
        $('._j_confirm_pay_statement').on('click', confirmPayStatement);
        $('._j_save_coopworkers_statement').on('click', generateCoopworkerStatement);
    }
    
    
    function paidCoopworker(){
        var coopworkerOrder = $(this).closest('.coopworker_order');
        var para = {
            oid: coopworkerOrder.attr('data-oid'),
            cuid: coopworkerOrder.attr('data-cuid'),
            type: coopworkerOrder.attr('data-type'),
            user_type:coopworkerOrder.attr('data-usertype'),
            exec_type: 'paid'
        };
        
        if (para.oid.length==''||para.cuid.length==''||para.type.length==''||para.user_type.length==''){
            alert('参数错误');
            return false;
        }
        
        if (!confirm('确认支付？')){
            return false;
        }
        
        K.post('/logistics/ajax/exec_order_coopworker.php', para, function(ret){
            if (ret.st == 1){
                //alert('操作成功！');
                window.location.reload();
            }
        });
    }
    
    function blukPayCoopworker(){
        var blukpayObj = [];
        var paymentType = $('#_j_bulk_payment_type').val();
        var paymentName = $('#_j_bulk_payment_type').find("option:selected").text();
        
        // 需要支付类型
        if (typeof paymentType=='undefined'||paymentType.length==0||paymentType=='0'){
            alert('请选择支付方式！！'); return false;
        }
        
        var doids='', coids='', dprices='', cprices='', dp=0, cp=0;
        $('input[name=bluk_pay]').each(function(){
            var _obj, _para;
            if ($(this).is(':checked')){
                _obj = $(this).closest('.coopworker_order');
                _para = {
                    id: _obj.attr('data-id'),
                    oid: _obj.attr('data-oid'),
                    cuid: _obj.attr('data-cuid'),
                    type: _obj.attr('data-type'),
                    user_type:_obj.attr('data-usertype')
                };
                blukpayObj.push(_para);
                
                var price = _obj.attr('data-price');
                if (_para.type==1){
                    doids += _para.oid+', ';
                    dprices += price+'元, ';
                    dp += price*1;
                } else {
                    coids += _para.oid+', ';
                    cprices += price+'元, ';
                    cp += price*1;
                }
            }
        });
        
        if (blukpayObj.length == 0){
            alert('请勾选【批量付】！'); return false;
        }
        
        var msg = '';
        if (dp > 0){
            msg = '[运费]订单：'+doids+ '\n[运费]总金额：'+dp/10/10+'元\n\n';
        }
        if (cp > 0){
            msg += '[搬运费]订单：'+coids+'\n[搬运费]总金额：'+cp/10/10+'元\n\n';
        }
        msg += '支付方式：【'+ paymentName + '】\n';
        msg += '共计：'+(dp+cp)/10/10+'元，确认支付？';
        
        if (!confirm(msg)){
            return false;
        }
        
        var para = {
            exec_type: 'bluk_paid',
            payment_type: paymentType,
            bluk_datas: JSON.stringify(blukpayObj)
        };
        
        $(this).attr('disabled', true);
        K.post('/logistics/ajax/exec_order_coopworker.php', para, function(ret){
            if (ret.st == 1){
                //alert('操作成功！');
                window.location.reload();
            }
        });
    }
    
    function orderBack(){
        var _obj = $(this).closest('.coopworker_order');
        var para = {
                step: 7, 
                type: 'next_step', 
                oid: _obj.attr('data-oid')
            };
            
        $(this).attr('disabled', true);
		K.post('/order/ajax/set_order.php', para, function(){
            window.location.reload();
        });
    }
    
    // 打印工人订单
    function printCoopworkerOrder(){
        var payList = $('input[name=bluk_pay]');
        var printList = $('input[name=bluk_pay_print]');
        var ids = [];

        if (payList.length > 0){ // 优先判断 来自批量支付的打印
            payList.each(function(){
                if ($(this).is(':checked')){
                    ids.push($(this).closest('.coopworker_order').attr('data-id'));
                }
            });
        } else if (printList.length > 0){ // 来自打印列表的打印
            printList.each(function(){
                if ($(this).is(':checked')){
                    ids.push($(this).closest('.coopworker_order').attr('data-id'));
                }
            });
        }
        
        if (ids.length == 0){
            alert('没有可打印内容！'); return false;
        }
        
        window.open('/order/coopworker_order_print.php?ids='+ids.join(','));
    }
    
    // 司机结款，全选
    function coopworkerPaySelectAll(){
        if ($('#coopworker_pay_select_all').is(':checked')) //全选
        {
            if($('#coopworker_order_area').find('input[name=bluk_pay]').length){
                $('#coopworker_order_area').find('input[name=bluk_pay]').each(function(){
                    this.checked=true;
                });
            } else if ($('#coopworker_order_area').find('input[name=bluk_pay_print]').length) {
                $('#coopworker_order_area').find('input[name=bluk_pay_print]').each(function(){
                    this.checked=true;
                });
            }
        }
        else //取消全选
        {
            if($('#coopworker_order_area').find('input[name=bluk_pay]').length){
                $('#coopworker_order_area').find('input[name=bluk_pay]').each(function(){
                    this.checked=false;
                });
            } else if ($('#coopworker_order_area').find('input[name=bluk_pay_print]').length) {
                $('#coopworker_order_area').find('input[name=bluk_pay_print]').each(function(){
                    this.checked=false;
                });
            }
        }
    }

    function checkWidIsEmpty() {
        var wid = $(this).data('wid');

        if (wid == 0)
        {
            var ids = [];
            var id_list = $('input[name=bluk_pay]');
            id_list.each(function () {
                if ($(this).is(':checked')) {
                    ids.push($(this).val());
                }
            });
            if (ids.length == 0)
            {
                alert('请勾选订单！');
                return false;
            }
            $('#selectWid').modal();
        } else {
            generateCoopworkerStatement();
        }

    }

    //生成结算单
    function generateCoopworkerStatement() {
        var wid = $('input[name=user_wid]:checked').val();
        var ids = [];
        var id_list = $('input[name=bluk_pay]');
        id_list.each(function () {
            if ($(this).is(':checked')) {
                ids.push($(this).val());
            }
        });

        var para = {ids: ids, wid:wid};
        $(this).attr('disabled', true);
        K.post('/order/ajax/generate_statement.php', para, function(ret){
            if (ret.ids){
                alert('操作成功！');
                window.location.reload();
            }
        }, function (err) {
            $('._j_save_coopworkers_statement').attr('disabled', false);
            alert(err.errmsg);
            return false;
        });
    }

    function cancelStatement() {
        var id = $(this).attr('data-id');
        var para = {id:id};
        $(this).attr('disabled', true);
        K.post('/order/ajax/cancel_statement.php', para, function(ret){
            if (ret.id){
                alert('操作成功！');
                window.location.reload();
            }
        });
    }

    function sureStatement() {
        var id = $(this).attr('data-id');
        var para = {id:id};
        $(this).attr('disabled', true);
        K.post('/order/ajax/sure_statement.php', para, function(ret){
            if (ret.id){
                alert('操作成功！');
                window.location.reload();
            }
        });
    }

    function print_statement() {
        var statement_id = $(this).attr('data-id');
        if (statement_id > 0)
        {
            window.open('/order/coopworker_order_print.php?statement_id='+statement_id);
        }
    }

    function statementSelectAll() {
        if ($('#statement_select_all').is(':checked')) //全选
        {
            if($('#statement_area').find('input[name=bluk_pay]').length){
                $('#statement_area').find('input[name=bluk_pay]').each(function(){
                    this.checked=true;
                });
            }
        }
        else //取消全选
        {
            if($('#statement_area').find('input[name=bluk_pay]').length){
                $('#statement_area').find('input[name=bluk_pay]').each(function(){
                    this.checked=false;
                });
            }
        }
    }
    
    function payStatement() {
        var paymentType = $('#_j_bulk_payment_type').val();

        // 需要支付类型
        if (typeof paymentType=='undefined'||paymentType.length==0||paymentType=='0'){
            alert('请选择支付方式！！'); return false;
        }

        var ids = [];
        var price = 0;
        var id_list = $('input[name=bluk_pay]');
        id_list.each(function () {
            if ($(this).is(':checked')) {
                ids.push($(this).val());
                price = price + parseFloat($(this).attr('data-price'));
            }
        });

        if (ids.length == 0){
            alert('请勾选结算单！'); return false;
        }

        if (!confirm('支付总金额为：' + price + '元，确认要支付吗？'))
        {
            return false;
        }

        var para = {
            exec_type: 'statement_bluk_paid',
            payment_type: paymentType,
            statement_ids: ids,
        };

        $(this).attr('disabled', true);
        K.post('/logistics/ajax/exec_order_coopworker.php', para, function(ret){
            if (ret.st == 1){
                alert('操作成功！');
                window.location.reload();
            }
        });
    }

    function outputWarnning() {
        $('#contentDetail').modal();
    }

    function statementOutput() {
        var ids = [];
        var id_list = $('input[name=bluk_pay]');
        id_list.each(function () {
            if ($(this).is(':checked')) {
                ids.push($(this).val());
            }
        });

        if (ids.length == 0){
            alert('请勾选结算单！'); return false;
        }
        window.open('/order/download_statement.php?statement_ids=' + ids.join(','));
    }

    function checkStatementDetail() {
        var id = $(this).attr('data-id');
        var para = {
            id:id,
        };
        K.post('/order/ajax/get_statement_fees.php', para, function (ret) {
            $('.modal-body').html('').append(ret.html);
            $('#check_statement').attr('data-id', id);
        });
        $('#contentDetail').modal();
    };
    function checkStatement() {
        var id = $(this).data('id');
        var total_price = $('#total_price').data('total-price');
        var para = {
            id: id,
            total_price:total_price,
        };
        K.post('/order/ajax/check_statement.php', para, function (ret) {
            $('#contentDetail').modal('hide');
            window.location.reload();
        });
    }

    function generateBatch() {
        var ids = [];
        var id_list = $('input[name=bluk_pay]');
        id_list.each(function () {
            if ($(this).is(':checked')) {
                ids.push($(this).val());
            }
        });
        var para = {ids:ids};
        K.post('/order/ajax/generate_batch.php', para,function(){
            statementOutput();
            window.location.reload();
        });
    }
    
    function cancelGenerateBatch() {
        statementOutput();
    }
    
    function printStatementFinance() {
        var ids = [];
        var id_list = $('input[name=bluk_pay]');
        id_list.each(function () {
            if ($(this).is(':checked')) {
                ids.push($(this).val());
            }
        });

        if (ids.length == 0){
            alert('请勾选结算单！'); return false;
        }

        window.open('/order/print_statement_finance.php?ids=' + ids.join(','));
    }

    function coopworkerSettlement() {
        $('#settlement .settlement_id').html($(this).data('id'));
        $('#settlement .settlement_id').attr('data-id', $(this).data('id'));
        $('#settlement .settlement_price').html('￥'+$(this).data('price'));
        $('#settlement .settlement_coopworker').html($(this).data('name')+' '+$(this).data('mobile'));
    }

    function confirmPayStatement() {
        var box = $('#settlement');
        var ids = [];
        ids.push(box.find('.settlement_id').data('id'));
        var para = {
            exec_type: 'statement_paid',
            payment_type: box.find('.payment_type').data('payment-type'),
            statement_ids: ids,
        };
        $(this).attr('disabled', true);
        K.post('/logistics/ajax/exec_order_coopworker.php', para, function(ret){
            if (ret.st == 1){
                alert('操作成功！');
                window.location.reload();
            }
        }, function (err) {
            alert(err.errmsg);
            $(this).attr('disabled', false);
        });
    }
    main();
    
})();