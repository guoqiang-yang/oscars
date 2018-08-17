(function(){
    
    function main(){
        $('#save_business').on('click', saveBusiness);
        
        $('#search_customer').on('click', searchCustomerForBind);
        $('#confirm_bind_customers').on('click', bindCustomer);
        $('.unbind_customer').on('click', unbindCustomer);
        $('.reset_df_passwd').on('click', resetDfPasswd);
        // 高级编辑企业信息
        $(".edit_business").on('click', editBusiness);
        // 提交修改企业高级编辑
        $(document).on('click', '.save_update_business', saveUpdateBusiness);
    }
    
    
    function saveBusiness(){
        var form = $('form');
        var para = {
            bid: $('#bid').val(),
            bname: form.find('input[name=bname]').val(),
            cname: form.find('input[name=cname]').val(),
            mobile: form.find('input[name=mobile]').val(),
            record_suid: form.find('select[name=record_suid]').val(),
            sales_suid: form.find('select[name=sales_suid]').val(),
            city: form.find('select[name=city]').val(),
            district: form.find('select[name=district]').val(),
            area: form.find('select[name=area]').val(),
            address: form.find('textarea[name=address]').val(),
            products: form.find('textarea[name=products]').val(),
            note: form.find('textarea[name=note]').val(),
            is_pay: form.find('select[name=is_pay]').val()
        };
        
        if (para.bname.length==0){
            alert('请填写企业名字'); return;
        }
        if (para.mobile.length==0){
            alert('请填写手机号'); return;
        }
        
        K.post('/crm2/ajax/save_business.php', para, function(ret){
            alert('添加成功');
            window.location.href = '/crm2/edit_business.php?bid='+ret.bid;
        });
    }
    
    function searchCustomerForBind(){
        var para = {
            bid: $('#bid').val(),
            otype: 'search_customers',
            search_customers: $('#bind_customer').find('textarea[name=customers]').val()
        };
        
        if (para.search_customers.length==0){
            alert('请输入客户ID，或手机号！'); return;
        }
        
        K.post('/crm2/ajax/modify_business.php', para, function(ret){
            $('#customers_area').html(ret.data.html);
        });
    }
    
    function bindCustomer(){
        var para = {
            bid: $('#bid').val(),
            otype: 'bind_customers'
        };
        
        var cids = [];
        $('#customers_area').find('input[name=select_customer]').each(function(){
            if ($(this).is(':checked')){
                cids.push($(this).closest('tr').attr('data-cid'));
            }
        });
        
        if (cids.length==0){
            alert('请选择需要绑定的客户！'); return;
        }
        para.bind_cids = cids.join(',');
        
        K.post('/crm2/ajax/modify_business.php', para, function(){
            alert('绑定成功！');
            window.location.reload();
        });
    }
    
    function unbindCustomer(){
        var para = {
            bid: $('#bid').val(),
            otype: 'unbind_customer',
            unbind_cid: $(this).closest('tr').attr('data-cid')
        };
        
        if (confirm('确定要解绑该用户？')){
            K.post('/crm2/ajax/modify_business.php', para, function(){
                alert('解绑成功！');
                window.location.reload();
            });
        }
    }
    
    // 重置为默认密码
    function resetDfPasswd(){
        var para = {
            bid: $(this).attr('data-bid')
        };
        
        if (para.bid.length==0) {
            alert('操作失败！');
            return;
        }
        
        if (confirm('确定要重置该用户密码？')){
            K.post('/crm2/ajax/reset_password.php', para, function(){
                alert('重置成功！');
            });
        }
    }
    
    function editBusiness()
    {
    	var para = {
    			bid : $(this).attr('data-bid'),
    			bname : $(this).attr('data-bname'),
    			contract_btime : $(this).attr('data-start-time'),
    			contract_etime : $(this).attr('data-end-time'),
    			discount_ratio : $(this).attr('data-discount'),
    			level_for_sys : $(this).attr('data-level'),
    			payment_days : $(this).attr('data-payment-days'),
    			payment_amount : $(this).attr('data-payment-amount'),
    			has_duty : $(this).attr('data-has-duty'),
    	};
    	$("input[name='bid']").val(para.bid);
    	$("input[name='bname']").val(para.bname);
    	$("input[name='contract_btime']").val(para.contract_btime);
    	$("input[name='contract_etime']").val(para.contract_etime);
    	$("input[name='discount_ratio']").val(para.discount_ratio);
    	$("select[name='level_for_sys']").val(para.level_for_sys);
    	$("input[name='payment_days']").val(para.payment_days);
    	$("input[name='payment_amount']").val(para.payment_amount);
    	// 为一个下拉框单独做一个ajax，无法动态修改selected这简直是bug搬得存在
    	K.post('/crm2/ajax/dlg_business_edit.php', para, function(ret){
    		$("select[name='level_for_sys']").html(ret.html);
        });
    	var dutyHtml = '';
    	if (para.has_duty == 1){
    		dutyHtml += "<option value='1' selected='selected'>含税企业</option><option value='2'>不含税企业</option>";
    	} else {
    		dutyHtml += "<option value='1'>含税企业</option><option value='2' selected='selected'>不含税企业</option>";
    	}
    	$("select[name='has_duty']").html(dutyHtml);
    }
    
    function saveUpdateBusiness()
    {
    	var para = {
    			bid : $("input[name='bid']").val(),
    			contract_btime : $("input[name='contract_btime']").val(),
    			contract_etime : $("input[name='contract_etime']").val(),
    			discount_ratio : $("input[name='discount_ratio']").val(),
    			level_for_sys : $("select[name='level_for_sys']").val(),
    			payment_days : $("input[name='payment_days']").val(),
    			payment_amount : $("input[name='payment_amount']").val(),
    			has_duty : $("select[name='has_duty']").val(),
    	};
    	K.post('/crm2/ajax/save_update_business.php', para, function(ret){
            alert("修改成功！");
            window.location.reload();
        });
    }
    
    main();
})();