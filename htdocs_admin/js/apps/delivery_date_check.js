(function () {

	function main() {
		$('#select_delivery_date').on('change', checkDeliveryTime);
		$('#select_delivery_time').on('change', changeDeliveryTime);

		checkDeliveryTime();
	}

	function checkDeliveryTime() {
		var date = $.trim($('#select_delivery_date').val());
		var today = $('#today').val();
		var hour = parseInt($('#hour').val());

		if (date == today) {
			$("#select_delivery_time option").each(function() {
				var val = parseInt($(this).val());
				if (hour < 10) {
					if (val <= hour) {
						$(this).css('display', 'none');
					}
				} else if (hour < 11) {
					if (val < 12) {
						$(this).css('display', 'none');
					}
				} else if (hour < 12) {
					if (val < 14) {
						$(this).css('display', 'none');
					}
				} else if (hour < 14) {
					if (val < 15) {
						$(this).css('display', 'none');
					}
				} else if (hour < 16) {
					if (val < 16) {
						$(this).css('display', 'none');
					}
				} else if (hour < 17) {
					if (val < 17) {
						$(this).css('display', 'none');
					}
				} else if (hour < 18) {
					if (val < 18) {
						$(this).css('display', 'none');
					}
				} else if (hour < 19) {
					if (val < 19) {
						$(this).css('display', 'none');
					}
				} else {
					if (val <= hour) {
						$(this).css('display', 'none');
					}
				}
			});
		} else
		{
			$("#select_delivery_time option").each(function() {
				$(this).css('display', '');
			});
		}
	}

	function changeDeliveryTime() {
		var valStart = parseInt($(this).val());
		console.log(valStart);
		$("#select_delivery_time_end option").each(function() {
			var val = parseInt($(this).val());
			if (val < valStart + 2 && val != 0) {
				$(this).css('display', 'none');
			} else {
				$(this).css('display', '');
			}

			if (val == 0) {
				$(this).attr('selected', 'selected');
			}
		});
	}

	main();
})();