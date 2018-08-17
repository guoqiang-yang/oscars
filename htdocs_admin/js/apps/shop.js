(function () {

    function main() {
        //保存商品
        $('#btn_save_product').click(_onSaveProduct);
        $('._j_del_product').click(_onDeleteProduct);
        $('._j_offline_product').click(_onOfflineProduct);
        $('._j_online_product').click(_onOnlineProduct);

        //型号管理
        $('#btn_save_model').click(_onSaveModel);
        $('._j_del_model').click(_onDelModel);
        $('._j_modify_model').click(_onModifyModel);
        $('._j_save_modify_model').click(_onSaveModifyModel);

        //品牌管理
        $('#btn_new_brand').click(_onNewBrand);
        $('#btn_save_brand').click(_onSaveBrand);
        $('._j_delete_brand').click(_onDeleteBrand);

        //订单选择商品
        $('._j_order_select_product').click(_onOrderSelectProduct);

        //品牌、型号下拉菜单 @添加商品页
        _initBrandModel();
        $('select[name=bid]').change(_onChgBrand);
        $('select[name=mid]').change(_onChgModel);
        $('select[name=cate2]').change(_onChgCate2);
        $('select[name=cate3]').change(_onChgCate3);
        //修改采购类型
        $('.save_purchase_type').click(_savePurchaseType);

        //编辑包装含量
        $('._j_save_picking_note').on('click',savePickNote);

    }

    function _onOrderSelectProduct(ev) {
        ev.preventDefault();
        var tgt = $(ev.currentTarget);

        alert(1);
    }

    function _initBrandModel() {
        var cate2 = $('select[name=cate2]').val(),
            cate3 = $('select[name=cate3]').val();
        _chgBrandAndModel(cate2, cate3);
    }

    function _onChgCate2(ev) {
        var tgt = $(ev.currentTarget),
            cate2 = tgt.val();
        if (cate2) {
            _chgBrandAndModel(cate2);
        }
    }

    function _onChgCate3(ev) {
        var tgt = $(ev.currentTarget),
            cate3 = tgt.val();
        _chgBrandAndModel(0, cate3);
    }

    function _chgBrandAndModel(cate2, cate3) {
        var bid = $('select[name=bid]').data('init'),
            mid = $('#model-container').data('init');
        if (cate2 || cate3) {
            _chgBrand(cate2, cate3, mid, bid);
            _chgModel(cate2, cate3, bid, mid);
        } else {
            $('select[name=mid]').html('<option>--选择型号--</option>');
            $('select[name=bid]').html('<option>--选择品牌--</option>');
        }
        $('select[name=bid]').data('init', 0).data('cate2', cate2).data('cate3', cate3);
        $('select[name=mid]').data('init', 0).data('cate2', cate2).data('cate3', cate3);
    }

    function _chgBrand(cate2, cate3, mid, preBid) {
        var para = {cate2: cate2, cate3: cate3};
        if (mid) para.mid = mid;
        if (preBid) para.bid = preBid;
        K.post('/shop/ajax/get_brand_list.php', para, _onGetBrandListSucss);
    }

    function _onGetBrandListSucss(data) {
        var dom = $('select[name=bid]');
        dom.html('');
        dom.append("<option value=''>--选择品牌--</option>");
        for (var i = 0; i < data.list.length; i++) {
            if (data.list[i].selected) {
                dom.append(jQuery("<option selected='selected'></option>").val(data.list[i].id).html(data.list[i].name));
            } else {
                dom.append(jQuery("<option></option>").val(data.list[i].id).html(data.list[i].name));
            }
        }
        ;
    }

    function _chgModel(cate2, cate3, bid, preMid) {
        var para = {cate2: cate2, cate3: cate3};
        if (bid) para.bid = bid;
        if (preMid) para.mid = preMid;
        K.post('/shop/ajax/get_model_list.php', para, _onGetModelListSucss);
    }

    function _onGetModelListSucss(data) {
        var dom = $('#model-container');
        dom.html('');
        if (data.list.length > 0) {
            for (var i = 0; i < data.list.length; i++) {
                if (data.list[i].selected) {
                    dom.append(jQuery('<label class="checkbox-inline"> <input disabled="disabled" checked="checked" type="checkbox" name="mids" value="' + data.list[i].id + '" />' + data.list[i].name + '</label>'));
                } else {
                    dom.append(jQuery('<label class="checkbox-inline"> <input disabled="disabled" type="checkbox" name="mids" value="' + data.list[i].id + '" />' + data.list[i].name + '</label>'));
                }
            }
        } else {
            dom.append(jQuery('<label style="text-align:left;" class="col-sm-4 control-label">暂无可用型号</label>'));
        }
    }

    function _onChgBrand(ev) {
        var tgt = $(ev.currentTarget),
            cate2 = tgt.data('cate2'),
            cate3 = tgt.data('cate3'),
            bid = tgt.val(),
            mid = $('select[name=mid]').val();
        _chgModel(cate2, cate3, bid, mid);
    }

    function _onChgModel(ev) {
        var tgt = $(ev.currentTarget),
            cate2 = tgt.data('cate2'),
            cate3 = tgt.data('cate3'),
            mid = tgt.val(),
            bid = $('select[name=bid]').val();
        _chgBrand(cate2, cate3, mid, bid);
    }

    function _onSaveProduct(ev) {
        var tgt = $(ev.currentTarget),
            frm = tgt.closest('form');

        var para = {
            pid: frm.find('input[name=pid]').val(),
            sid: frm.find('input[name=sid]').val(),
            price: frm.find('input[name=price]').val(),
            work_price: frm.find('input[name=work_price]').val(),
            ori_price: frm.find('input[name=ori_price]').val(),
            cost: frm.find('input[name=cost]').val(),
            online: frm.find('input[name=online]:checked').val(),
            sales_type: frm.find('input[name=sales_type]:checked').val(),
            carrier_fee: frm.find('input[name=carrier_fee]').val(),
            carrier_fee_ele: frm.find('input[name=carrier_fee_ele]').val(),
            worker_ca_fee: frm.find('input[name=worker_ca_fee]').val(),
            worker_ca_fee_ele: frm.find('input[name=worker_ca_fee_ele]').val(),
            city_id: frm.find('select[name=city_id]').val(),
            buy_type: frm.find('input[name=buy_type]:checked').val(),
            managing_mode: frm.find('input[name=managing_mode]:checked').val(),
            recommend_pids: frm.find('input[name=recommend_pids]').val(),
            picking_note: frm.find('input[name=picking_note]').val(),
            alias: frm.find('input[name=alias]').val()
        };

        K.post('/shop/ajax/save_product.php', para, _onSaveProductSucss);
    }

    function _onSaveProductSucss(data) {
        alert('保存成功！');
        window.location.href = "/shop/edit_product.php?pid=" + data.pid;
    }

    function _onDeleteProduct(ev) {
        var tgt = $(ev.currentTarget),
            pid = $(this).data('pid');

        if (!confirm("确定要删除该商品？")) {
            return;
        }

        K.post('/shop/ajax/delete_product.php', {pid: pid}, _onDeleteProductSucss);
    }

    function _onDeleteProductSucss(data) {
        window.location.reload();
    }

    function _onSaveModel(ev) {
        var tgt = $(ev.currentTarget),
            frm = tgt.closest('form');
        var para = {
            name: frm.find('input[name=name]').val(),
            cate1: frm.data('cate1'),
            cate2: frm.data('cate2'),
            cate3: frm.data('cate3')
        };
        K.post('/shop/ajax/save_model.php', para, _onSaveModelSucss);
    }

    function _onSaveModelSucss(data) {
        window.location.reload();
    }

    function _onDelModel(ev) {
        var tgt = $(ev.currentTarget),
            frm = tgt.closest('tr'),
            para = {mid: frm.data('mid')};

        if (confirm('确认删除该型号? 请先确认没有商品属于该型号')) {
            K.post('/shop/ajax/delete_model.php', para, _onDelModelSucss);
        }
    }

    function _onDelModelSucss(data) {
        window.location.reload();
    }

    function _onModifyModel(ev) {
        var tgt = $(ev.currentTarget),
            mid = tgt.closest('tr').data('mid'),
            name = tgt.closest('tr').data('name'),
            frm = $('#dlgModalForm').find('form');

        frm.data('mid', mid);
        frm.find('input[name=name]').val(name);

        $('#dlgModalForm').modal('show');
    }

    function _onSaveModifyModel(ev) {
        var tgt = $(ev.currentTarget),
            frm = $('#dlgModalForm').find('form');

        var para = {
            mid: frm.data('mid'),
            name: frm.find('input[name=name]').val()
        };
        K.post('/shop/ajax/save_model.php', para, _onSaveModelSucss);
    }

    function _onSaveModelSucss(data) {
        window.location.reload();
    }

    function _onSaveBrand(ev) {
        var tgt = $(ev.currentTarget),
            frm = tgt.closest('form');

        var para = {
            bid: frm.data('bid'),
            cate1: frm.data('cate1'),
            cate2: frm.data('cate2'),
            cate3: frm.data('cate3'),
            name: frm.find('input[name=name]').val()
        };
        K.post('/shop/ajax/save_brand.php', para, _onSaveBrandSucss);
    }

    function _onSaveBrandSucss(data) {
        window.location.href = "/shop/brand_list.php?cate1=" + data.cate1 + "&cate2=" + data.cate2 + "&cate3=" + data.cate3;
    }

    function _onNewBrand(ev) {
        var tgt = $(ev.currentTarget),
            frm = tgt.closest('form'),
            name = K.trim(frm.find('input[name=name]').val());

        if (name.length == 0) {
            alert('品牌名称不能为空');
        }

        var para = {
            name: name,
            cate1: frm.data('cate1'),
            cate2: frm.data('cate2'),
            cate3: frm.data('cate3')
        };
        K.post('/shop/ajax/save_brand.php', para, _onNewBrandSucss);
    }

    function _onNewBrandSucss(data) {
        window.location.href = "/shop/brand_list.php?cate1=" + data.cate1 + "&cate2=" + data.cate2 + "&cate3=" + data.cate3;
    }

    function _onDeleteBrand(ev) {
        var tgt = $(ev.currentTarget),
            frm = tgt.closest('tr'),
            para = {bid: frm.data('bid'), cate2: frm.data('cate2'), cate3: frm.data('cate3')};

        if (confirm('确认删除该品牌? 请先确认有没有商品属于该品牌')) {
            K.post('/shop/ajax/delete_brand.php', para, _onDeleteBrandSucss);
        }
    }

    function _onDeleteBrandSucss(data) {
        window.location.reload();
    }

    function _onOfflineProduct(ev) {
        var tgt = $(ev.currentTarget),
            pdom = tgt.closest('._j_product'),
            pid = pdom.data('pid');

        if (!confirm("确定要下架该商品吗？")) {
            return false;
        }

        K.post('/shop/ajax/offline_product.php', {pid: pid}, _onOfflineProductSucss);
    }

    function _onOfflineProductSucss(data) {
        window.location.reload();
    }

    function _onOnlineProduct(ev) {
        var tgt = $(ev.currentTarget),
            pdom = tgt.closest('._j_product'),
            pid = pdom.data('pid');

        if (!confirm("确定要上架该商品吗？")) {
            return false;
        }

        K.post('/shop/ajax/offline_product.php?type=online', {pid: pid}, _onOnlineProductSucss);
    }

    function _onOnlineProductSucss(data) {
        window.location.reload();
    }

    function _savePurchaseType(){
        var buy_type = $('#modifyPurchaseType').find('input[name=buy_type]:checked').val();
        var pid = $('input[name="pid"]').val();
        var para = {buy_type: buy_type, pid: pid};
        
        K.post('/shop/ajax/purchase_type.php',para, function(){
            alert('修改成功');
            window.location.reload();
        });
    }

    function savePickNote() {
        var box = $('#editPickNote');
        var pid = $(this).data('pid');
        var para = {
            pick_note: box.find('input[name=pick_note]').val(),
            pid: pid
        };

        K.post('/shop/ajax/save_pick_note.php', para, function (suc) {
            alert(suc.msg);
            window.location.reload();
        })
    }

    main();

})();