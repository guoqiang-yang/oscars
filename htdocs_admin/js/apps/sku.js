(function () {

    function main() {
        //保存sku
        $('#btn_save_sku').click(_onSaveSku);
        $('._j_offline_sku').click(_onOfflineSku);
        $('._j_online_sku').click(_onOnlineSku);

        //品牌、型号下拉菜单 @添加商品页
        _initBrandModel();
        $('select[name=bid]').change(_onChgBrand);
        $('select[name=mid]').change(_onChgModel);
        $('select[name=cate2]').change(_onChgCate2);
        $('select[name=cate3]').change(_onChgCate3);

        if ($('.gridly').length > 0) {
            $('.gridly').gridly('layout');
        }

        $('#chgSelectSkuType').on('change', chgSelectSkuType);
        $('#addSku4Relation').on('click', addSku4Relation);
        $('.relation_skus_area').on('click', modifyRelationSkus);

        $('.wait_select_loc_2_processing').on('click', calMaxNum4Processing);
        $('#confirmCombinProduct').on('click', confirmCombinProduct);
        $('#confirmSplitProduct').on('click', confirmSplitProduct);
    }

    function _onSaveSku(ev) {
        var tgt = $(ev.currentTarget),
            frm = tgt.closest('form');

        var imageArr = [];
        var picIds = '';
        var imageItems = $('.gridly').find('.brick');
        if (imageItems.length > 0) {
            for (var i = 0; i < imageItems.length; i++) {
                var item = $(imageItems[i]);
                var top = item.css('top');
                var left = item.css('left');
                imageArr.push({top: top, left: left, pictag: item.data('pic')});
                //console.log(parseInt(top) + "; " + parseInt(left) + "; " + item.data('pic'));
            }

            imageArr.sort(function (a, b) {
                if (a.top == b.top) {
                    if (a.left == b.left) {
                        return 0;
                    }

                    return a.left > b.left ? 1 : -1;
                }
                ;

                return a.top > b.top ? 1 : -1;
            });

            for (var j = 0; j < imageArr.length; j++) {
                picIds += imageArr[j].pictag + ",";
            }

            picIds = picIds.substring(0, picIds.length - 1);
        }

        console.log(picIds);

        var cate1Arr = [];
        $('input[name=mids]:checked').each(function () {
            cate1Arr.push($(this).val());
        });

        var para = {
            sid: frm.find('input[name=sid]').val(),
            title: frm.find('input[name=title]').val(),
            alias: frm.find('input[name=alias]').val(),
            cate1: frm.find('select[name=cate1]').val(),
            cate2: frm.find('select[name=cate2]').val(),
            cate3: frm.find('select[name=cate3]').val(),
            bid: frm.find('select[name=bid]').val(),
            mid: cate1Arr,
            unit: frm.find('input[name=unit]').val(),
            package: frm.find('input[name=package]').val(),
            picking_note: frm.find('input[name=picking_note]').val(),
            detail: frm.find('textarea[name=detail]').val(),
            online: frm.find('input[name=online]:checked').val(),
            pic_ids: picIds,
            qrcode_type: frm.find('input[name=qrcode_type]:checked').val(),
            length: frm.find('input[name=length]').val(),
            width: frm.find('input[name=width]').val(),
            height: frm.find('input[name=height]').val(),
            weight: frm.find('input[name=weight]').val(),
            type: frm.find('select[name=type]').val()
        };
        if (para.type == '2'||para.type == '3') {
            para.rel_sku = $('#currRelationSku').val();
        } else {
            para.rel_sku = '';
        }

        _getPicInfo(para);

        K.post('/shop/ajax/save_sku.php', para, _onSaveSkuSucss);
    }

    function _getPicInfo(para) {

        if (K.headAreaSelector) {
            var selection = K.headAreaSelector.getSelection();
            para.x1 = selection.x1;
            para.y1 = selection.y1;
            para.width = selection.width;
            para.height = selection.height;
            para.imgwidth = $('#_j_upload_view_img').width();
            para.imgheight = $('#_j_upload_view_img').height();
        }

    }

    function _onSaveSkuSucss(data) {
        window.location.href = "/shop/edit_sku.php?sid=" + data.sid;
    }

    function _onOfflineSku(ev) {
        var tgt = $(ev.currentTarget),
            pdom = tgt.closest('._j_sku'),
            sid = pdom.data('sid');

        if (!confirm("确定要下架该sku？\n\n下架后，其属下所有商品也都将被下架。")) {
            return false;
        }

        K.post('/shop/ajax/offline_sku.php', {sid: sid}, _onOfflineSkuSucss);
    }

    function _onOfflineSkuSucss(data) {
        window.location.reload();
    }

    function _onOnlineSku(ev) {
        var tgt = $(ev.currentTarget),
            pdom = tgt.closest('._j_sku'),
            sid = pdom.data('sid');

        if (!confirm("确定要上架该sku？\n\n上架sku只会上架该sku，而不会上架其属下的商品。")) {
            return false;
        }

        K.post('/shop/ajax/offline_sku.php?type=online', {sid: sid}, _onOnlineSkuSucss);
    }

    function _onOnlineSkuSucss(data) {
        window.location.reload();
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
            cate2 = $('select[name=cate2]').val(),
            cate3 = tgt.val();

        _chgBrandAndModel(cate2, cate3);
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
                    dom.append(jQuery('<label class="checkbox-inline"> <input checked="checked" type="checkbox" name="mids" value="' + data.list[i].id + '" />' + data.list[i].name + '</label>'));
                } else {
                    dom.append(jQuery('<label class="checkbox-inline"> <input type="checkbox" name="mids" value="' + data.list[i].id + '" />' + data.list[i].name + '</label>'));
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

    // 修改商品的类型
    function chgSelectSkuType() {
        if ($(this).val()=='2'||$(this).val()=='3') {
            $('.showAddRelationSkus').show();
            $('.relation_skus_area').show();
        } else {
            $('.showAddRelationSkus').hide();
            $('.relation_skus_area').hide();
        }
    }

    function addSku4Relation(evt) {
        if ($(evt.target).hasClass('searchSkuList')) {
            var pageStart = $(evt.target).attr('data-start');
            searchSkuList4Relation(pageStart);
        } else if ($(evt.target).hasClass('add_sku_for_relation')) {
            bindSkuForRelation(evt.target);
        }
    }

    function searchSkuList4Relation(pageStart) {
        var dlg = $('#addSku4Relation');
        var params = {
            start: (typeof pageStart == 'undefined') ? 0 : parseInt(pageStart),
            parent_sid: dlg.find('.modal-df-datas').attr('data-sid'),
            had_rel_sku: $('#currRelationSku').val(),
            keyword: dlg.find('input[name=keyword]').val(),
            optype: 'show'
        };

        if (params.keyword.length == 0) {
            alert('请输入检索 关键字！');
            return false;
        }

        K.post('/shop/ajax/add_sku_4_relation.php', params, function (ret) {
            $('#showSkuListForRelation').html(ret.html);
        });
    }

    function bindSkuForRelation(tgt) {
        var dlg = $('#addSku4Relation');
        var params = {
            parent_sid: dlg.find('.modal-df-datas').attr('data-sid'),
            rel_sid: $(tgt).closest('._j_product').attr('data-sid'),
            bind_num: $(tgt).closest('._j_product').find('input[name=num]').val(),
            rel_title: $(tgt).closest('._j_product').find('.title').html(),
            had_rel_sku: $('#currRelationSku').val(),
            optype: 'bind'
        };

        var tgtTr = $(tgt).closest('._j_product');
        K.post('/shop/ajax/add_sku_4_relation.php', params, function (ret) {
            $(tgt).closest('td').html('<span style="color:red;">已关联</span>');
            tgtTr.find('input[name=num]').attr('disabled', true);
            ;
            $('#currRelationSku').val(ret.rel_sku);
            $('.relation_skus_area').show();
            $('.relation_skus_area').html(ret.html);
        });
    }

    function modifyRelationSkus(evt) {
        if ($(evt.target).hasClass('delSku4SkuRelation')) {
            unbindSkuForRelation(evt.target);
        }
    }

    function unbindSkuForRelation(tgt) {
        var parentSid = 0;
        if ($(tgt).closest('form').find('input[name=sid]').length) {
            parentSid = $('form').find('input[name=sid]').val();
        }
        var params = {
            parent_sid: parentSid,
            rel_sid: $(tgt).attr('data-sid'),
            optype: 'unbind'
        };

        if (!confirm('确定要解绑该sku！')) return false;

        if (parentSid == 0)
        {
            // 页面删除
            var trNum = $(tgt).closest('tbody').find('tr').length;
            if (trNum == 1) {
                $('.relation_skus_area').html('<div><span class="text-value" style="color:red;font-size:15px;">暂无关联SKU！！</span></div>');
            } else {
                $(tgt).closest('tr').remove();
            }
            
            
            
            return;
        }

        //编辑sku
        K.post('/shop/ajax/add_sku_4_relation.php', params, 
            function (ret) {
                $('#currRelationSku').val(ret.rel_sku);
                
                // 页面删除
                var trNum = $(tgt).closest('tbody').find('tr').length;
                if (trNum == 1) {
                    $('.relation_skus_area').html('<div><span class="text-value" style="color:red;font-size:15px;">暂无关联SKU！！</span></div>');
                } else {
                    $(tgt).closest('tr').remove();
                }
            },
            function (msg){
                alert(msg.errmsg);
            }
        );
    }

    function calMaxNum4Processing() {
        var locs = {};
        var rate = {};
        $('#partProductsArea_Combin').find('.wait_select_loc_2_processing').each(function () {
            var dom = $(this).closest('p');
            var _sid = dom.attr('data-sid'),
                _num = dom.attr('data-lnum');

            if ($(this).is(':checked')) {
                if (locs[_sid]) {
                    locs[_sid] += parseInt(_num);
                }
                else {
                    locs[_sid] = parseInt(_num);
                }
            }
            rate[_sid] = parseInt(dom.attr('data-rate'));
        });

        // 获取部件商品的个数
        var partsNum = $('#partProductsArea_Combin').find('._j_product').length;
        var selectedNum = Object.keys(locs).length;

        var maxNum = 9999999;
        if (selectedNum == partsNum && Object.keys(rate).length == selectedNum) {
            for (i in locs) {
                maxNum = Math.min(maxNum, Math.floor(locs[i] / rate[i]));
            }
        }
        else {
            maxNum = 0;
        }

        $('#maxNum4ProcessedOrder').html(maxNum);
    }

    function confirmCombinProduct() {
        var para = {
            combin_sid: $('#combinProductsArea_Combin').attr('data-sid'),
            wid: $('form').find('select[name=wid]').val(),
            type: 1,
            location: $('#createCombinArea_Combin').find('input[name=location]').val(),
            num: $('#createCombinArea_Combin').find('input[name=num]').val()
        };
        if (para.location.length == 0) {
            alert('请输入组合商品的上架货位！');
            return;
        }
        if (para.num.length == 0 || para.num == '0') {
            alert('请输入组合商品的生产数量');
            return;
        }
        if (parseInt(para.num) > parseInt($('#maxNum4ProcessedOrder').html())) {
            alert('组合商品的生产数量不能大于最大生产数量');
            return;
        }

        var skuinfos = [];
        $('#partProductsArea_Combin').find('.wait_select_loc_2_processing').each(function () {
            var part = {};
            if ($(this).is(':checked')) {
                var obj = $(this).closest('p');
                part.sid = obj.attr('data-sid');
                part.location = obj.attr('data-loc');

                skuinfos.push(part);
            }
        });
        para.parts = JSON.stringify(skuinfos);

        if (!confirm('确定组合商品？')) {
            return false;
        }

        $(this).attr('disabled', true);
        K.post('/shop/ajax/create_processed_order.php', para,
            function (ret) {
                alert('操作成功！');
                window.location.href = "/shop/processed_order_detail.php?id=" + ret.data.id;
            },
            function (err) {
                alert(err.errmsg);
                $('#confirmCombinProduct').attr('disabled', false);
            }
        );
    }

    function confirmSplitProduct() {
        var para = {
            type: 2,
            wid: $('form').find('select[name=wid]').val()
        };

        // 被拆分商品
        var combinSkus = [];
        var combinSt = true;
        $('#combinProductsArea_Split').find('input[name=num]').each(function () {
            var _num = $(this).val();
            if (_num.length > 0 && parseInt(_num) > 0) {
                if (_num > parseInt($(this).attr('data-lnum'))) {
                    combinSt = false;
                    return;
                }
                var s = {};
                s.sid = $('#combinProductsArea_Split').attr('data-sid');
                s.location = $(this).attr('data-loc');
                s.num = _num;
                combinSkus.push(s);
            }
        });

        if (!combinSt) {
            alert('组合商品，货位数量不足，请检测');
            return;
        }
        if (combinSkus.length == 0) {
            alert('请输入商品的转换数量');
            return;
        }
        para.split_combinskus = JSON.stringify(combinSkus);

        // 拆分商品
        var splitSkus = [];
        var splitSt = true;
        $('#partProductsArea_Split').find('input[name=location]').each(function () {
            if ($(this).val().length == 0) {
                splitSt = false;
                return;
            }
            var ps = {};
            ps.sid = $(this).attr('data-sid');
            ps.location = $(this).val();
            splitSkus.push(ps);
        });
        if (!splitSt) {
            alert('请输入拆分商品的货位信息');
            return;
        }
        if (splitSkus.length == 0) {
            alert('请检测拆分商品');
            return;
        }
        para.split_partskus = JSON.stringify(splitSkus);

        if (!confirm('确认转换？')) return;

        $(this).attr('disabled', true);
        K.post('/shop/ajax/create_processed_order.php', para,
            function (ret) {
                alert('操作成功！');
                window.location.href = "/shop/processed_order_detail.php?id=" + ret.data.id;
            },
            function (err) {
                alert(err.errmsg);
                $('#confirmSplitProduct').attr('disabled', false);
            }
        );
    }

    main();

})();

function delete_pic(picTag) {
    $(".img_item").each(function (i) {
        var pic = $(this).data('pic');
        if (pic == picTag) {
            $(this).remove();
        }
    });

    var pic_ids = $('input[name=pic_ids]').val();
    var picIdsArr = pic_ids.split(',');
    for (var i = 0; i < picIdsArr.length; i++) {
        if (picIdsArr[i] == picTag) {
            picIdsArr.splice(i, 1);
            break;
        }
    }

    var newPicIds = '';
    if (picIdsArr.length > 0) {
        newPicIds = picIdsArr.join(',');
    }

    $('input[name=pic_ids]').val(newPicIds);
}