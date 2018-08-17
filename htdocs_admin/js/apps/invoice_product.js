(function () {

    function main() {
        $('._j_add_product').click(_onAddProduct);
        $('.edit_product').click(_onShowProduct);
        $('._j_edit_product').click(_onEditProduct);
        $('.delete_product').click(_onDeleteProduct);
        $('.restore_product').click(_onRestoreProduct);
        switch ($('#navbar').attr('data-status')) {
            case 'all':
                $($('#navbar li')[0]).attr('class','active');
                break;
            case 'del':
                $($('#navbar li')[2]).attr('class','active');
                break;
            default:
                $($('#navbar li')[1]).attr('class','active');
                break;
        }
    }

    // 添加商品
    function _onAddProduct(ev) {

        var para = {
            title: $('#add_title').val(),
            spec: $('#add_spec').val(),
            unit: $('#add_unit').val(),
            city_id: $('#add_city').val(),
            cate1: $('#add_cate1').val()
        };

        if (para.title == ''){
            alert('商品名必填！');
            return false;
        }

        if (para.spec == '')
        {
            alert('规格必填！');
            return false;
        }

        if (para.unit == '')
        {
            alert('单位必填！');
            return false;
        }

        if (para.cate1 == 0)
        {
            alert('请选择分类！');
            return false;
        }

        if (para.city_id == 0)
        {
            alert('请选择城市！');
            return false;
        }

        K.post('/finance/ajax/save_product.php', para, _onSaveProductSuccess);
    }

    //显示编辑商品
    function _onShowProduct(ev) {
        $('#edit_pid').val($(this).attr('data-pid'));
        $('#edit_title').val($(this).attr('data-title'));
        $('#edit_spec').val($(this).attr('data-spec'));
        $('#edit_unit').val($(this).attr('data-unit'));
        $('#edit_city').val($(this).attr('data-city'));
        $('#edit_cate1').val($(this).attr('data-cate1'));
        $('#editProductModal').modal('show');
    }

    // 编辑商品
    function _onEditProduct(ev) {

        var para = {
            pid: $('#edit_pid').val(),
            title: $('#edit_title').val(),
            spec: $('#edit_spec').val(),
            unit: $('#edit_unit').val(),
            city_id: $('#edit_city').val(),
            cate1: $('#edit_cate1').val()
        };

        if (para.title == ''){
            alert('商品名必填！');
            return false;
        }

        if (para.spec == '')
        {
            alert('规格必填！');
            return false;
        }

        if (para.unit == '')
        {
            alert('单位必填！');
            return false;
        }

        if (para.cate1 == 0)
        {
            alert('请选择分类！');
            return false;
        }

        if (para.city_id == 0)
        {
            alert('请选择城市！');
            return false;
        }

        K.post('/finance/ajax/save_product.php', para, _onSaveProductSuccess);
    }

    //删除商品
    function _onDeleteProduct(ev) {
        var msg = "您真的确定要删除吗？\n\n请确认！";
        if (confirm(msg)==true){
            var para = {
                method: 'delete',
                pid: $(this).attr('data-pid')
            };
            K.post('/finance/ajax/change_product.php', para, _onSaveProductSuccess);
            return true;
        }else{
            return false;
        }
    }

    //还原商品
    function _onRestoreProduct(ev) {
        var msg = "您真的确定要还原吗？\n\n请确认！";
        if (confirm(msg)==true){
            var para = {
                method: 'restore',
                pid: $(this).attr('data-pid')
            };
            K.post('/finance/ajax/change_product.php', para, _onSaveProductSuccess);
            return true;
        }else{
            return false;
        }
    }

    function _onSaveProductSuccess(data) {
        alert('操作成功');
        window.location.reload();
    }

    main();

})();