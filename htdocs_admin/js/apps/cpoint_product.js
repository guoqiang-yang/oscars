(function () {

    function main() {
        $('#btn_save_product').click(_onSaveCpointProduct);
        $('#change_stock_history').click(_onShowStockHistoryDailog);
        $('._j_change_product_stock').click(_onSaveStockHistory);
        if ($("#stock_action_list").length > 0)
        {
            _onGetProductStockLog();
        }
        var ue = UE.getEditor('editor', {
            toolbars: [
                ['fullscreen', 'source', '|', 'undo', 'redo', '|',
                    'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
                    'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
                    'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
                    'directionalityltr', 'directionalityrtl', 'indent', '|',
                    'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|','link', 'unlink', 'anchor', '|','background', 'inserttable', 'deletetable',, 'emotion','map']
            ],
            autoHeightEnabled: false,
            autoFloatEnabled: true,
            initialFrameHeight: 300,
            elementPathEnabled:false,
            maximumWords:500,
            /*      retainOnlyLabelPasted:true,
             pasteplain:true,
             filterTxtRules:true,*/
        });
    }

    function _onShowStockHistoryDailog() {
        $('#chg_stock_num').val('');
        $('#chg_reason').val('');
        $('#addProductStockHistoryModal').modal('show');
    }
    function _onSaveStockHistory() {
        var para = {
            pid: $('input[name=pid]').val(),
            stock_num: $('#chg_stock_num').val(),
            note: $('#chg_reason').val()
        };
        if(para.stock_num == '')
        {
            alert('修改库存必填！');
            return false;
        }
        if(para.note == '')
        {
            alert('修改原因必填！');
            return false;
        }
        $(this).attr('disabled',true);
        K.post('/activity/ajax/save_cpoint_product_stock_history.php', para, _onSaveStockHistorySuccess, _onSaveStockHistoryFail);
    }
    function _onSaveStockHistorySuccess(data) {
        alert('修改库存成功');
        window.location.reload();
    }
    function _onSaveStockHistoryFail(data) {
        alert(data.errmsg);
        $('._j_change_product_stock').attr('disabled', false);
    }
    // 添加／编辑商品
    function _onSaveCpointProduct(ev) {
        var member_level = '';
        $.each($('input[name=member_level]:checked'), function(i, v){
            member_level += v.value+',';
        });
        var para = {
            pid: $('input[name=pid]').val(),
            title: $('input[name=title]').val(),
            abstract: $('textarea[name=abstract]').val(),
            cate1: $('select[name=cate1]').val(),
            price: $('input[name=price]').val(),
            cost: $('input[name=cost]').val(),
            point: $('input[name=point]').val(),
            stime: $('input[name=stime]').val(),
            etime: $('input[name=etime]').val(),
            status: $('select[name=status]').val(),
            detail: UE.getEditor('editor').getContent(),
            pics: $('input[name=pic_ids]').val()
        };

        if(para.pid == '')
        {
            para.stock_num = $('input[name=stock_num]').val();
        }

        if(para.title == '')
        {
            alert('商品名必填！');
            return false;
        }
        if(para.cate1 == 0)
        {
            alert('商品分类必选！');
            return false;
        }

        if(para.price == '')
        {
            alert('市场价必填！');
            return false;
        }


        if(para.cost == '')
        {
            alert('成本价必填！');
            return false;
        }

        if(para.point == '')
        {
            alert('兑换积分必填！');
            return false;
        }

        if(para.pid == '' && para.stock_num == '')
        {
            alert('库存必填！');
            return false;
        }

        if (para.stime == ''){
            alert('兑换开始时间必填！');
            return false;
        }

        if (para.etime == ''){
            alert('兑换结束时间必填！');
            return false;
        }

        if (member_level == ''){
            alert('兑换等级必选！');
            return false;
        }else{
            para.member_level = member_level.substr(0,member_level.length-1);
        }

        if (para.detail == ''){
            alert('商品描述必填！');
            return false;
        }

        if(para.pics == '')
        {
            alert('商品图片必填！');
            return false;
        }

        var picIdsArr = para.pics.split(',');
        if(picIdsArr.length > 8)
        {
            alert('最多只能上传8张照片！');
            return false;
        }

        $(this).attr('disabled', true);
        K.post('/activity/ajax/save_customer_point_product.php', para, _onSaveCustomerPointProductSuccess);
    }

    function _onSaveCustomerPointProductSuccess(data) {
        alert('保存成功');
        window.location.href = '/activity/customer_point_product.php';
    }

    function _onGetProductStockLog() {
        var pid = $('#stock_action_list').data('pid');
        if($(this).data('start') > 0)
        {
            var start = $(this).data('start');
        }else{
            var start = 0;
        }
        var para = {
            pid: pid,
            start: start
        };

        K.post('/activity/ajax/get_cpoint_product_stock_log.php', para, _onGetProductStockLogSucc);
    }

    function _onGetProductStockLogSucc(data) {
        $('#stock_action_list').html(data.html);
        $('.cpoint_product_stock_log_pagetruning').on('click',_onGetProductStockLog);
    }

    main();

})();