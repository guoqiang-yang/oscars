(function () {

	function main() {
		$('#btn_save').click(_onSaveSupplier);
		$('#check_supplier').click(changeSupplierStep);
		$('#back_supplier').click(changeSupplierStep);
		$('#back_supplier_modal').click(showBackReasonModal);
		$('.offline_supplier').click(changeSupplierStep);
        
        $('.show_modify_supplier_purchase_price').on('click', showModifySupplierPurchasePrice);
        $('#modify_supplier_purchase_price').on('click', saveModifySupplierPurchasePrice);
        $('.delete_supplier_sku').on('click', deleteSupplierSku);
        $('#showSkuListForSupplier').on('click', clickDlg4SupplierSku);
        $('.bluk_selected_inorder').on('click', stat4Purchase);
        $('#supplier_skus_area').find('input[name=real_num]').on('blur', stat4Purchase);
        $('#confirmCreateInorder4Supplier').on('click', createInorder4Supplier);
        $('#createRefund4Supplier').on('click', createRefund4Supplier);
        $('#createRefund4SupplierModal').on('hidden.bs.modal', resetSupplierRefundModal);

        $('.look_bank_info').on('click', lookBankInfo);
        $('.add_more').on('click', addMore);

        /*供应商预付*/
        $('#supplier_prepay').on('click', addSupplierPrepay);
        $('._j_add_supplier_prepay').on('click', sureAddSupplierAmount);

        //批量维护采购价
        $('#_j_confirm_save_price').on('click', savePurchasePrice);
	}

	function _onSaveSupplier(ev) {
		var tgt = $(ev.currentTarget),
			para = {
				sid: $('input[name=sid]').val(),
				name: $('input[name=name]').val(),
				alias_name: $('input[name=alias_name]').val(),
				contact_name: $('input[name=contact_name]').val(),
				phone: $('input[name=phone]').val(),
				address: $('input[name=address]').val(),
				wid: $('select[name=wid]').val(),
				type: $('select[name=type]').val(),
				products: $('textarea[name=products]').val(),
				note: $('textarea[name=note]').val(),
                book_note: $('textarea[name=book_note]').val(),
                bank_info: $('input[name=bank_info]').val(),
                public_bank: $('input[name=public_bank]').val()+','+$('.bank_flag[num=0]').val()+','+$('.bank_flag[num=1]').val()+','+$('.bank_flag[num=2]').val(),
                payment_days: $('input[name=payment_days]').val(),
                duty: $('input[name=duty]').val(),
                special_duty: $('input[name=special_duty]').val(),
                invoice: $('select[name=invoice]').val(),
                delivery_hours: $('input[name=delivery_hours]').val(),
                managing_mode: $('input[name=managing_mode]:checked').val()
			};

		if (para.type.length==0 || para.type == 0) {
			alert('请先填写供应商类型');
			return;
		}

		if (para.type.length == 0 ) {
            alert('请选择经营模式！');
            return;
        }

		if (para.delivery_hours == 0)
		{
			alert('请填写送货周期！');
			return;
		}

		var cate1Arr = [];
		$('input[name=cate1]:checked').each(function(){
			cate1Arr.push($(this).val());
		});
        var cityArr = [];
        $('input[name=city]:checked').each(function(){
            cityArr.push($(this).val());
        });

		para.city = cityArr.join(',');
		para.cate1 = cate1Arr.join(',');
		para.refer = tgt.data('refer');

		K.post('/warehouse/ajax/save_supplier.php', para, _onSaveSupplierSucss);
	}

	function _onSaveSupplierSucss(data) {
		if (data.errno == -1){
			alert('添加失败，手机号对应的供应商已存在！');
			return false;
		}
        alert('保存成功！');
		if (data.url) {
			window.location.href = data.url;
		}else{
			window.location.reload();
		}
	}

	function changeSupplierStep() {
		var status = $(this).attr('data-status');
		var sid = $(this).attr('data-sid');
		var para = {
			sid: sid,
			status: status,
		};

		if (status == 6)
		{
			var	reason = $('#no_audit_reason_area').val();
			if (reason.length == 0)
			{
				alert('请填写驳回原因！');
				return;
			}
			para.reason = reason;
		}

		K.post('/warehouse/ajax/change_supplier_status.php', para, function () {
			alert('操作成功！');
            window.location.href = '/warehouse/supplier_list.php';
        });
    }

    function showBackReasonModal() {
		$('#un_audit_reason').modal();
    }
    
    // 修改供应商的sku采购价
    function showModifySupplierPurchasePrice(){
        var dialog = $(this).closest('.dialog');
        var supplier_id = dialog.attr('data-supplierid'),
            sku_id = dialog.attr('data-skuid'),
            purchase_price = dialog.find('.purchase_price').html();
    
        $('#modify_supplier_purchase_price').attr('data-supplierid', supplier_id);
        $('#modify_supplier_purchase_price').attr('data-skuid', sku_id);
        $('#modifySupplierPurchasePrice').find('input[name=purchase_price]').val(purchase_price);
        
        $('#modifySupplierPurchasePrice').modal();
    }
    
    function saveModifySupplierPurchasePrice(){
        var params = {
            supplier_id: $(this).attr('data-supplierid'),
            sku_id: $(this).attr('data-skuid'),
            purchase_price: $('#modifySupplierPurchasePrice').find('input[name=purchase_price]').val()
        };
        
        K.post('/warehouse/ajax/save_supplier_purchase_price.php', params, function(){
            alert('修改成功！');
            window.location.reload();
        });
    }
    
    function deleteSupplierSku(){
        var params = {
            supplier_id: $(this).closest('tr').attr('data-supplierid'),
            sku_id: $(this).closest('tr').attr('data-skuid')
        };
        
        if(confirm('确定要删除该sku？')){
            K.post('/warehouse/ajax/del_supplier_sku.php', params, function(){
                alert('修改成功！');
                window.location.reload();
            });
        }
    }
    
    function clickDlg4SupplierSku(evt){
        if ($(evt.target).hasClass('searchSkuListForSupplier')) {
            var pageStart = $(evt.target).attr('data-start');
            searchSkuListForSupplier(pageStart);
        } else if ($(evt.target).hasClass('add_sku_for_supplier')) {
            addSkuForSupplier(evt.target);
        }
    }
    
    function addSkuForSupplier(target){
        var dlg = $('#showSkuListForSupplier');
        var params = {
            supplier_id : dlg.find('.modal-df-datas').attr('data-supplier_id'),
            purchase_price: $(target).closest('._j_product').find('input[name=purchase_price]').val()*10*10,
            sku_id: $(target).closest('._j_product').attr('data-sid'),
            optype: 'add'
        };
        
        K.post('/warehouse/ajax/add_sku_4_supplier.php', params, function(){
            $(target).closest('._j_product').find('input[name=purchase_price]').parent().html('<span>'+params.purchase_price/100+' 元</span>')
            $(target).parent().html('<span style="color:red;">已添加</span>');
            target = null;
            alert('添加成功！');
        });
    }
    
    function searchSkuListForSupplier(pageStart){
        var dlg = $('#showSkuListForSupplier');
        var params = {
            start: (typeof pageStart=='undefined')? 0: parseInt(pageStart),
            supplier_id : dlg.find('.modal-df-datas').attr('data-supplier_id'),
            keyword: dlg.find('input[name=keyword]').val(),
            optype: 'show'
        };
        
        if (params.keyword.length == 0){
            alert('请输入检索 关键字！');
            return false;
        }
        
        K.post('/warehouse/ajax/add_sku_4_supplier.php', params, function(ret){
            $('#showSkuListForSupplierArea').html(ret.html);
        });
    }
    
    function stat4Purchase(){
        var weight = 0,
            price = 0;
        
        $('#supplier_skus_area').find('.dialog').each(function(){
            if ($(this).find('.bluk_selected_inorder').is(':checked'))
            {
                var _weight = $(this).attr('data-weight');
                var _price = $(this).find('.purchase_price').html();
                var _num = $(this).find('input[name=real_num]').val(); 
                weight += _num * _weight;
                price += _num * _price;
            }
        });
        
        $('#stat_4_purchase').find('.weight').html(weight+'KG');
        $('#stat_4_purchase').find('.price').html(price+'元');
    }
    
    function createInorder4Supplier(){
        var dlg = $('#createInorder4Supplier');
        var dataObj = $('#supplier_skus_area');
        
        var wid = dlg.find('select[name=wid]').val();
        var supplierId = $('#supplier_skus_area').attr('data-supplierid');
        
        if (wid == '0'){
            alert('请选择仓库！'); return false;
        }
        
        var products = [];
        var checkDesc = '';
        dataObj.find('.dialog').each(function(){
            var _pinfo = {};
            if($(this).find('.bluk_selected_inorder').is(':checked')){
                _pinfo.sid = $(this).find('.sku_id').html();
                _pinfo.price = $(this).find('.purchase_price').html()*100;
                
                var _num = 0;
                _num = $(this).find('input[name=real_num]').val();
                
                if (_num.length==0 || parseInt(_num)<=0 || isNaN(parseInt(_num))){
                    _num = 0;
                    
                    var orderNumStr = $(this).find('.order_num').attr('data-ordernum');
                    if (orderNumStr.length > 0){
                        var orderNum = eval('(' + orderNumStr + ')');
                        for (var i in orderNum){
                            if (wid == orderNum[i].wid){
                                _num = orderNum[i].num;
                            }
                        }
                    }
                }
                if (parseInt(_num) <= 0) {
                    checkDesc = 'sid:'+_pinfo.sid+' 采购量为0，请核对！';
                    return false;
                }
                
                _pinfo.num = _num;
                products.push(_pinfo);
            }
        });
        
        if (checkDesc.length > 0){
            alert(checkDesc); return false;
        }
        
        if (products.length == 0){
            alert('采购商品为空，请现在商品！'); return false;
        }
        
        $(this).attr('disabled', true);
        var params = {
            wid: wid,
            supplier_id: supplierId,
            products: JSON.stringify(products)
        };
        K.post('/warehouse/ajax/create_inorder_4_supplier.php', params, 
            function(ret){
                alert('创建成功！');
                window.location.href = '/warehouse/edit_in_order.php?oid='+ret.id;
            },
            function(err){
                alert(err.errmsg);
                $('#confirmCreateInorder4Supplier').attr('disabled', false);
            }
        );
    }

    function createRefund4Supplier(){
        var dlg = $('#createRefund4SupplierModal');
        var step = $(this).attr('data-step');
        var wid;
        var para = {};
        if (step == 'show_products')
        {
            var dataObj = $('#supplier_skus_area');
            wid = dlg.find('select[name=wid]').val();
            var supplierId = dataObj.attr('data-supplierid');
            var sids = [];
            dataObj.find('.dialog').each(function(){
                if($(this).find('.bluk_selected_inorder').is(':checked')){
                    sids.push($(this).find('.sku_id').html());
                }
            });

            if (sids.length == 0){
                alert('退货商品为空，请选择商品！'); return false;
            }

            para = {
                wid: wid,
                supplier_id: supplierId,
                sids: JSON.stringify(sids)
            };

            K.post('/warehouse/ajax/get_supplier_refund_products.php', para, function (ret) {
                $('#createRefund4Supplier').attr('data-step', 'create');
                $('#select_warehouse_area').hide();
                dlg.find('.modal-title').html('创建退货单');
                dlg.find('.create_supplier_refund_area').html(ret.html);
            });
        }
        else if(step == 'create')
        {
            wid = dlg.find('select[name=wid]').val();
            var supplier_id = dlg.find('input[name=supplier_id]').val();
            var note = dlg.find('textarea[name=note]').val();
            var products=[];
            var flag = false;

            dlg.find('input[name=num]').each(function () {
                var _num = $(this).val();
                if (parseInt(_num) > 0)
                {
                    var _sid = $(this).closest('tr').attr('data-sid');
                    var _price = $(this).closest('tr').find('input[name=price]').val();
                    var _available_num = $(this).closest('tr').find('.available_num').html();
                    var _loc = $(this).closest('tr').attr('data-loc');
                    if (parseInt(_num) > parseInt(_available_num))
                    {
                        flag = true;
                    }
                    var _info = {
                        sid: _sid,
                        price: _price,
                        num: _num,
                        loc: _loc
                    };
                    products.push(_info);
                }
            });

            if (flag)
            {
                alert('退货数量不能大于可退数量！');
                return;
            }

            para = {
                supplier_id: supplier_id,
                wid: wid,
                note: note,
                products: JSON.stringify(products)
            };

            K.post('/warehouse/ajax/save_supplier_refund.php', para, function (ret) {
                window.location.href='/warehouse/stockin_refund_detail.php?srid=' + ret.srid;
            });
        }
    }

    function resetSupplierRefundModal() {
        $('#createRefund4Supplier').attr('data-step', 'show_products');
        $('#select_warehouse_area').show();
        $('#createRefund4SupplierModal').find('.create_supplier_refund_area').html('');
        $('#createRefund4SupplierModal').find('select[name=wid]').val(0);
        $('#createRefund4SupplierModal').find('.modal-title').html('创建退货单,选仓库');
    }

    function lookBankInfo()
    {
        var sid = $(this).data('sid');
        var para = {sid:sid};
        K.post('/warehouse/ajax/get_supplier_info.php', para, function (data) {
            $('#look_bank_info .modal-body').empty();
            var supplier = data.supplier;

            var html = "<div style='text-align:center; font-size:16px; margin-bottom: 20px;'>"+supplier.name+"</div>";

            if (supplier.public_bank.length > 0)
            {
                for (var i = 0;i < supplier.public_bank.length; i++)
                {
                    html += "<div><span style='color: red'>公户"+(i+1)+": </span>"+supplier.public_bank[i]+"</div>";

                }
            }

            if (supplier.bank_info.length > 0)
            {
                html += "<div><span style='color: red'>私户: </span>"+supplier.bank_info+"</div>";
            }

            if (supplier.bank_info.length == 0 && supplier.public_bank.length == 0)
            {
                html = "<div style='text-align:center;font-size:16px;'>暂无银行账户信息</div>"
            }

            $('#look_bank_info .modal-body').append(html);
            $('#look_bank_info').modal();
        });
    }

    function addMore() {
        var is_add = true;
        $(".bank_flag").each(function (i) {
            if (i == 2){
                is_add = false;
            }
        });

        if (is_add == false) {
            return alert('最多添加三个');
        }
        var html = '<div class="form-group"><label class="col-sm-2 control-label"></label><div class="col-sm-5"><input class="form-control bank_flag" name="public_bank" style="margin-bottom:5px;" value="" num="" placeholder="请输入公户银行"></div><button type="button" class="btn btn-primary del_bank">删除</button></div></div>';
        $('.pub_bank').append(html);
        initdata();
    }

    function initdata(){
        $(".bank_flag").each(function (i) {
            $(".pub_bank .bank_flag").eq(i).attr('num', i);
            $(".pub_bank .del_bank").eq(i).attr('num', i);
        });
    }

    $(document).on('click', '.pub_bank .del_bank', function() {
        $(this).parent().remove();
        initdata();
    });

    function addSupplierPrepay()
    {
        $('#add_prepay').modal();
    }

    function sureAddSupplierAmount()
    {
        var box = $('#add_prepay');
        var para = {
            sid: box.find('input[name=sid]').val(),
            price: box.find('input[name=price]').val(),
            note: box.find('textarea[name=note]').val(),
            payment_type: box.find('.payment_type option:selected').val(),
            city_id: box.find('.pay_city option:checked').val(),
        };

        $(this).attr('disabled', true);
        if (para.price.length == 0)
        {
            $(this).attr('disabled', false);
            alert('请填写金额！');
            return false;
        }
        if (para.city_id == 0)
        {
            $(this).attr('disabled', false);
            alert('请选择城市！');
            return false;
        }
        if (para.payment_type.length == 0)
        {
            $(this).attr('disabled', false);
            alert('请选择支付类型！');
            return false;
        }
        if (para.note.length == 0)
        {
            $(this).attr('disabled', false);
            alert('请填写备注！');
            return false;
        }

        K.post('/finance/ajax/add_supplier_prepay.php', para, function (data) {
            alert(data.msg);
            window.location.href = '/finance/supplier_amount_list.php?sid=' + para.sid;
        });
    }

    function savePurchasePrice()
    {
        var info = [];

        $('#purchasePriceInBatch .purchase_price').each(function () {
            info.push($(this).data('id')+'_'+$(this).val());
        });
        var para = {
            info: info,
            sid: $('input[name=supplier_id]').val()
        };
        if (para.sid.length == 0)
        {
            alert('参数错误：供应商ID为空！请联系技术人员。');
            return false;
        }
        $(this).attr('disabled', true);
        K.post('/warehouse/ajax/save_purchase_price_in_batch.php', para, function (ret) {
            alert('保存成功！');
            window.location.reload();
        }, function (err) {
            $('#_j_confirm_save_price').attr('disabled', false);
            alert(err.errmsg);
        });
    }

    main();

})();