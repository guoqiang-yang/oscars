(function() {

	function main() {
		$('.city_privilege').click(_showSaleDialog);
		$('._j_save_sale_city_privilege').click(_saveSalePrivilege);
	}

	function _showSaleDialog() {
		var obj = $(this);
		var suid = obj.attr('data-suid');
        var city_suid = $('#saleModifyPrivilegeModal').find('input[name=city_saler]').val();
		$('#saleModifyPrivilegeModal').find('.modal-title').html('优惠额度修改-'+obj.attr('data-name')+'('+obj.attr('data-suid')+')');
		$('#saleModifyPrivilegeModal').find('input[name=sale_suid]').val(obj.attr('data-suid'));
		$('#sale_old_amount').html(obj.attr('data-total')+'元');
        if(suid != city_suid)
        {
            $('#sale_can_amount').html((Number(obj.attr('data-total'))+Number($('#saleModifyPrivilegeModal').attr('data-amount')))+'元');
			$('#sale_can_amount').attr('data-amount',Number(obj.attr('data-total'))+Number($('#saleModifyPrivilegeModal').attr('data-amount')));
        }else{
            $('#sale_can_amount').html(Number(obj.attr('data-total'))+'元');
			$('#sale_can_amount').attr('data-amount',Number(obj.attr('data-total')));
        }
        $('#sale_used_amount').html((Number(obj.attr('data-total'))-Number(obj.attr('data-available'))).toFixed(2)+'元');
		$('#saleModifyPrivilegeModal').modal('show');
	}

	function _saveSalePrivilege() {
		var suid = $('#saleModifyPrivilegeModal').find('input[name=sale_suid]').val();
		var amount = Number($('#saleModifyPrivilegeModal').find('input[name=privilege]').val());
        var can_amount = Number($('#sale_can_amount').attr('data-amount'));
        var city_saler = $('#saleModifyPrivilegeModal').find('input[name=city_saler]').val();
        if(can_amount<amount)
        {
            alert('新总优惠金额不能大于可分配额度');
            return false;
        }
		var para = {suid: suid, amount: amount, city_saler:city_saler};

		if (confirm('确定要修改销售优惠额度为'+ amount +'元吗？')) {
			K.post('/order/ajax/change_city_privilege.php', para, _onSendSuccess);
		}
	}


	function _onSendSuccess(data) {
		alert('修改完毕！');
		window.location.reload();
	}

	main();

} )();