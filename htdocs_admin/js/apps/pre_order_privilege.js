//提前下单优惠，后台js逻辑代码
(function () {
    var preDate = $('#pre_date').val();
    var privilege = parseInt($('#pre_date_privilege').val());
    var lock = false;
    var preOrderSet = parseInt($('#pre_order_set').val());

	function main() {
		$('#select_delivery_date').keyup(_calPreOrderPrivilege);
		$('#select_delivery_time').change(_calPreOrderPrivilege);

        _calPreOrderPrivilege();
	}

	function _calPreOrderPrivilege() {
        //var date = $('#select_delivery_date').val();
        //var time = $('#select_delivery_time').val();
        //
        //var dateTime = date + ' ' + time + ":00:00";
        //
        ////如果所选时间大于等于提前下单优惠的时间
        //if (preOrderSet == 0 && privilege > 0 && Date.parse(dateTime) >= Date.parse(preDate)) {
        //    if (!lock) {
        //        $('#pre_order_privilege').val(privilege / 100);
        //        lock = true;
        //    }
        //} else {
        //    if (lock) {
        //        $('#pre_order_privilege').val(0);
        //        lock = false;
        //    }
        //}
    }

	main();
})();
