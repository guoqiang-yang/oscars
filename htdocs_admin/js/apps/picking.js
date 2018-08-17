/**
 * Created by qihua on 16/12/30.
 */
(function () {

	var oid = 0;
	var sid = 0;
	var vnum = 0;
	var total = 0;

	function main() {
		//更新缺货数
		$('.update_vnum').on('click', showUpdateVnumDlg);
		$('#new_vnum').on('input', checkNewVnum);
		$('#update_vnum').on('click', updateVnum);

		//强制刷新
		$('.refresh_force').on('click', refreshForce);
        
        //清空拣货数
        $('.clear_picked_num').on('click', clearPickedNum);
	}

	function refreshForce() {
		if (confirm('确定要强制刷新该商品的占用吗？')) {
			oid = $(this).data('oid');
			sid = $(this).data('sid');
			var para = {oid: oid, sid: sid};
			K.post('/order/ajax/refresh_force.php', para, _onRefreshForceSucc);
		}

		return false;
	}

	function _onRefreshForceSucc(ret) {
        alert(ret.errmsg);
		window.location.reload();
	}

	function showUpdateVnumDlg(){
		var dlg = $('#update_vnum_dlg');
		oid = $(this).data('oid');
		sid = $(this).data('sid');
		vnum = $(this).data('vnum');
		total =$(this).data('total');
		$('#total_num').val(total);
		$('#vnum_num').val(vnum);
		vnum = parseInt(vnum);
		total = parseInt(total);
		var vnumMin = vnum + 1;
		var msg = "取值范围：" + vnumMin.toString() + '~' + $(this).data('total');
		if (vnumMin > total) {
			msg = '目前缺货数已经是最大，不可以再调整！';
			$('#new_vnum').attr('disabled', 'disabled');
		}
		$('#alert_msg').html(msg);

		dlg.modal();
	}

	function checkNewVnum() {
		var newVnum = parseInt($('#new_vnum').val());
		if (isNaN(newVnum) || newVnum <= vnum || newVnum > total) {
			$('#update_vnum').attr('disabled', 'disabled');
		} else {
			$('#update_vnum').removeAttr('disabled');
		}
	}

	function updateVnum() {
		var vnum = parseInt($('#vnum_num').val());
		var newVnum = parseInt($('#new_vnum').val());
		var para = {
			oid: oid,
			sid: sid,
			vnum: vnum,
			new_vnum: newVnum
		};

        $(this).attr('disabled', true);
		K.post('/order/ajax/update_vnum.php', para, function(){
            window.location.reload();
        });
	}
    
    function clearPickedNum() {
        var para = {
            oid: $(this).attr('data-oid'),
            pid: $(this).attr('data-pid')
        };
        
        if (confirm('确定要清空拣货数量'))
        {
            K.post('/order/ajax/clear_picked_num.php', para, function(){
                window.location.reload();
            });
        }
    }

	main();


})();