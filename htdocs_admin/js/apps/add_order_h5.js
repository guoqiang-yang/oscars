"use strict";

(function () {
    $('._j_chg_delivery_type').bind('click', changeDeliveryType);
    changeDeliveryType();
    $('#confirm_step_1').bind('click', confirmStep1);
    $('#select-city').bind('change', changeCity);
    $('.add_product_btn').bind('click', addProduct);
    var cityId = $('#city_id').val();
    $('#select-city').val(cityId);
    $('#service').bind('change', changeService);
    $('#service').bind('change', calCarryFee);
    changeService();
    $('#floor_num').bind('input', calCarryFee);
    $('#confirm_step_3').bind('click', confirmStep3);
    $('#confirm_order_btn').bind('click', confirmOrder);
    // 页面加载计算搬运费
    calCarryFee();
    // 初始化活动信息
    var check_num = 0;
    getActivity('[]', '[]');
    // 点击减商品数量
    $('.minus_product_num').bind('click', minusProductNum);
    // 点击添加商品数量
    $('.plus_product_num').bind('click', plusProductNum);
    // 添加商品
    $('#plus_product').bind('click', plusProduct);
    // 批量删除订单商品
    $('#remove_product').bind('click', removeProduct);
    // 订单核对页点击减商品数量
    $('.minus_product_num_exe').bind('click', minusProductNumExe);
    // 订单核对页点击加商品数量
    $('.plus_product_num_exe').bind('click', plusProductNumExe);
    // 核对订单商品下步操作
    $('#goto_order_fee').bind('click', gotoOrderFee);
    // 选择地址
    $('#select_address').bind('click', selectAddress);
    // 点击减特价商品数量
    $(document).on('click', '.minus_special_num' , minusSpecialNum);
    // 点击添加特价商品数量
    $(document).on('click', '.plus_special_num' , plusSpecialNum);
    // 点击选择赠品
    $(document).on('click', '.gift_checkbox', clickGiftProduct);
    // 点击选择特价商品
    $(document).on('click', '.special_checkbox', clickSpecialProduct);

    function confirmOrder() {
        var oid = $('#oid').val();
        var para = {oid: oid};
        K.post('/order/ajax/confirm_order_h5.php', para, onConfirmOrder);
    }

    function onConfirmOrder(data) {
        window.location.href = '/order/add_order_succ_h5.php';
    }

    function confirmStep3() {
        var freight = $('#freight').val();
        var service = $('#service').val();
        var floorNum = $('#floor_num').val();
        var customerCarriage = $('#customer_carriage').val();
        var note = $('#note').val();
        var oid = $('#oid').val();
        var isPrintPrice = $('input[name=is_print_price]:checked').val();
        var paymentType = $('#payment_type').val();
        var para = {oid: oid, freight: freight, service: service, floor_num: floorNum, customer_carriage: customerCarriage, note: note, is_print_price: isPrintPrice,payment_type: paymentType};
        K.post('/order/ajax/save_order_fee_h5.php', para, onSaveFeeSucc);
    }

    function onSaveFeeSucc(data) {
        window.location.href = '/order/add_order_confirm_h5.php?oid=' + data.oid;
    }

    function changeService() {
        var service = parseInt($('#service').val());
        if (service == 2) {
            $('#floor_num_info').css('display', '');
        } else {
            $('#floor_num_info').css('display', 'none');
        }
    }

    function calCarryFee() {
        var service = parseInt($('#service').val());
        var floorNum = parseInt($('#floor_num').val());
        var oid = $('#oid').val();

        if (isNaN(service) || service == 0) {
            $('#customer_carriage').val('0');
            return false;
        }
        if (service == 2 && (isNaN(floorNum) || floorNum == 0)) {
            $('#customer_carriage').val('0');
            return false;
        }

        var para = {oid: oid, service: service, floor_num: floorNum};
        K.post('/order/ajax/cal_carry_fee.php', para, onCalCarryFee);
    }

    function onCalCarryFee(data) {
        $('#customer_carriage').val(data.carry_fee / 100);
    }

    function addProduct() {
        var pid = $(this).data('pid');
        var num = $('#product_num_' + pid).val();
        var oid = $('#oid').val();

        if (parseInt(num) < 0 || isNaN(parseInt(num))) {
            alert('请输入正确的数量！');
            return false;
        }

        var productStr = pid + ':' + num;
        var para = {from_h5: 1, product_str: productStr, oid: oid};
        K.post('/order/ajax/add_products.php', para, _onAddProductScc);
    }

    function _onAddProductScc(data) {
        $('#total_price').html(data.price);
        console.log('add product succ');
    }

    function changeCity(evt, arg1) {
        if (!arg1) {
            var cityId = $("#select-city").val();
            var cid = $('#cid').val();
            var uid = $('#uid').val();
            var href = window.location.href;
            var pos = href.indexOf("?");
            if (pos >= 0) {
                href = href.substr(0, pos) + '?' + 'cid=' + cid + '&uid=' + uid + '&city_id=' + cityId;
            } else {
                href = href + '?' + 'cid=' + cid + '&uid=' + uid + '&city_id=' + cityId;
            }

            window.location.href = href;
        }
    }

    function confirmStep1() {
    	var delivery_time = $('#select_delivery_time').val();
    	var delivery_time_array = delivery_time.split('-');
    	var ziti_delivery_time = $('#select_ziti_delivery_time').val();
    	var ziti_delivery_time_array = ziti_delivery_time.split('-');
        var para = {
            uid: $('#uid').val(),
            cid: $('#cid').val(),
            oid: $('#oid').val(),
            source: $('input[name=source]:checked').val(),
            delivery_type: $('input[name=delivery_type]:checked').val(),
            community_id: $('#community_id').val(),
            address_id : $('#address_id').val(),
            addr_detail: $('#community_address').val(),
            contact_name: $('#contact_name').val(),
            contact_phone: $('#contact_phone').val(),
            delivery_date: $('#select_delivery_date').val(),
            delivery_time: delivery_time_array[0],
            delivery_end_time: delivery_time_array[1],
            ziti_date: $('#select_zidi_date').val(),
            ziti_time: ziti_delivery_time_array[0],
            ziti_time_end: ziti_delivery_time_array[1],
            wid: $('#wid').val()
        };
        if (parseInt(para.uid) <= 0 || isNaN(parseInt(para.uid))) {
        	return alert('uid不能为空！');
        }
        if (parseInt(para.cid) <= 0 || isNaN(parseInt(para.cid))) {
        	return alert('cid不能为空！');
        }
        if (parseInt(para.delivery_type) <= 0 || isNaN(parseInt(para.delivery_type))) {
        	return alert('请选择送货方式！');
        }
        if (parseInt(para.wid) <= 0 || isNaN(parseInt(para.wid))) {
        	return alert('请选择仓库！');
        }

        if (parseInt(para.delivery_type) == 1) {        //普通配送
            if (K.isEmpty(para.addr_detail)) {
            	return alert('请选择地址！');
            }
            if (K.isEmpty(para.contact_name)) {
            	return alert('请填写收货人！');
            }
            if (K.isEmpty(para.contact_phone)) {
            	return alert('请填写收货电话！');
            }
            if (parseInt(para.delivery_date) <= 0 || isNaN(parseInt(para.delivery_date))) {
            	return alert('请选择配送日期！');
            }
            if (parseInt(para.delivery_time) <= 0 || isNaN(parseInt(para.delivery_time))) {
            	return alert('请选择配送时间！');
            }
            if (parseInt(para.delivery_end_time) <= 0 || isNaN(parseInt(para.delivery_end_time))) {
            	return alert('请选择配送时间！');
            }
        } else if (parseInt(para.delivery_type) == 2) { //自提
        	para.contact_name = $('#self_contact_name').val();
        	para.contact_phone = $('#self_contact_phone').val();
        	if (K.isEmpty(para.contact_phone)) {
            	return alert('请填写自提人联系方式！');
            }
            if (K.isEmpty(para.contact_name)) {
            	return alert('请填写自提人！');
            }
            if (parseInt(para.ziti_date) <= 0 || isNaN(parseInt(para.ziti_date))) {
            	return alert('请选择自提日期！');
            }
            if (parseInt(para.ziti_time) <= 0 || isNaN(parseInt(para.ziti_time))) {
            	return alert('请选择自提时间！');
            }
            if (parseInt(para.ziti_time_end) <= 0 || isNaN(parseInt(para.ziti_time_end))) {
            	return alert('请选择自提时间！');
            }
        } else if (parseInt(para.delivery_type) == 3) { //尽快送达
            if (parseInt(para.community_id) <= 0 || isNaN(parseInt(para.community_id))) {
            	return alert('请选择小区！');
            }
            if (K.isEmpty(para.addr_detail)) {
            	return alert('请填写门牌号！');
            }
            if (K.isEmpty(para.contact_name)) {
            	return alert('请填写收货人！');
            }
            if (K.isEmpty(para.contact_phone)) {
            	return alert('请填写收货电话！');
            }
        }

        K.post('/order/ajax/save_order_h5.php', para, _onStep1Succ);
    }
    function _onStep1Succ(data) {
        window.location.href = '/order/edit_order_product_h5.php?oid=' + data.oid;
    }

    //订单选地址suggest
    if ($('#auto_suggest_position').length) {
        $(document).ready(function () {
            $('#auto_suggest_position').autosuggest({
                url: '/order/ajax/search_community.php',
                align: 'left',
                minLength: 2,
                maxNum: 10,
                highlight: false,
                queryParamName: 'keyword',
                immediate: true,
                extra: {city_id: $('#select-city').val()},
                nextStep: autoSuggestNextStep
            });
        });
    }
    function autoSuggestNextStep() {
        var obj = $(".as-selected");
        var value = eval('(' + decodeURIComponent(obj.attr('data-value')) + ')');

        $('#select-city').val(value.city_id);
        $('#select-city').trigger('change', true);
        $('#select-district').val(value.district_id);
        $('#select-district').trigger('change');
        if (value.ring_road == 0) {
            $("#select-area option:first").prop("selected", 'selected');
        } else {
            $('#select-area').val(value.ring_road);
        }

        $('#select-area').trigger('change');
        $('#community_id').val(value.cmid);

        // add more-info to DOM For show in map
        var cObj = $('#show_add_new_community');
        cObj.attr('data-zone', value.city_id + ':' + value.district_id + ':' + value.ring_road);
        cObj.attr('data-status', value.status);
        cObj.attr('data-pos', value.pos);
        $('#community_address').val(value.address);
    }

    function changeDeliveryType() {
        var deliveryType = parseInt($('._j_chg_delivery_type:checked').val());
        if (deliveryType == 1) {
            $('.delivery_self').css('display', 'none');
            $('.delivery_quickly').css('display', 'none');
            $('.delivery_common').css('display', '');
        } else if (deliveryType == 2) {
            $('.delivery_common').css('display', 'none');
            $('.delivery_quickly').css('display', 'none');
            $('.delivery_self').css('display', '');
        } else if (deliveryType == 3) {
        	$('.delivery_self').css('display', 'none');
            $('.delivery_quickly').css('display', 'none');
            $('.delivery_common').css('display', '');
        }
    }
    
    function minusProductNum()
    {
    	var pid = $(this).attr('data-pid');
    	var productNum = $('#product_num_'+pid).val();
    	productNum = Number(productNum);
    	if (productNum > 0){
    		productNum = productNum -1;
    	}
    	$('#product_num_'+pid).val(productNum);
    }
    
    function plusProductNum()
    {
    	var pid = $(this).attr('data-pid');
    	var productNum = $('#product_num_'+pid).val();
    	productNum = Number(productNum);
    	$('#product_num_'+pid).val(productNum + 1);
    }
    
    function plusProduct()
    {
    	var oid = $('#oid').val();
        var para = {oid: oid};
        var products = [];
	    $('.product_list').each(function () {
	        var num = $(this).val();
	        num = Number(num);
	        var pid = $(this).attr('data-product-id');
	        var note = '';
	        if (K.isNumber(num) && num > 0) {
	            products.push(pid + ':' + num + ':' + note);
	        }
	    });
	    if (products.length == 0) {
	        alert('请先选择商品');
	        return;
	    }
	    para.product_str = products.join(',');
	    K.post('/order/ajax/add_products.php', para, _onSaveProductsSuccess);
    }
    
    function _onSaveProductsSuccess(data)
    {
    	var param = {
    			oid : data.oid
    	};
    	K.location('/order/edit_order_product_h5.php', param);
    }
    
    function removeProduct()
    {
    	var product_ids = K.getSelectedVal('.product_checkbox', 'array');
    	if (product_ids.length == 0){
    		return alert('请选择要移除的商品！');
    	} else {
    		var oid = $('#oid').val();
    		var para = {
    	            oid: oid,
    	            pid_list: product_ids
    	        };
	        if (confirm('确定要删除选中的商品吗？')) {
	            K.post('/order/ajax/delete_product.php', para, _onDeleteProductSuccess);
	        }
    	}
    }
    
    function _onDeleteProductSuccess(data)
    {
    	window.location.reload();
    }
    
    function minusProductNumExe()
    {
    	var oid = $('#oid').val();
    	var pid = $(this).attr('data-pid');
    	var productNum = $('#product_num_'+pid).val();
    	productNum = Number(productNum);
    	if (productNum > 1){
    		productNum = productNum -1;
    	} else {
    		return;
    	}
    	var para = {oid: oid};
    	var products = [];
    	var note = '';
    	$('#product_num_'+pid).val(productNum);
    	products.push(pid + ':' + productNum + ':' + note);
    	para.product_str = products.join(',');
	    K.post('/order/ajax/add_products.php', para, _onDeleteProductSuccess);
    }
    
    function plusProductNumExe()
    {
    	var oid = $('#oid').val();
    	var pid = $(this).attr('data-pid');
    	var productNum = $('#product_num_'+pid).val();
    	productNum = Number(productNum);
    	productNum = productNum + 1;
    	$('#product_num_'+pid).val(productNum);
    	var para = {oid: oid};
    	var products = [];
    	var note = '';
    	$('#product_num_'+pid).val(productNum);
    	products.push(pid + ':' + productNum + ':' + note);
    	para.product_str = products.join(',');
	    K.post('/order/ajax/add_products.php', para, _onDeleteProductSuccess);
    }
    
    function gotoOrderFee()
    {
    	var oid = $('#oid').val();
    	var product_ids = new Array();
    	$.each($('.product_checkbox' + ' input[type="checkbox"]'),function(){
    		product_ids.push(1);
	    });
    	if (product_ids.length == 0){
    		return alert('请先添加商品！');
    	} else {
    		// 更新活动商品信息
    		if (oid !== '0'){
        		var gift_products = '';
        		var discount_products = '';
        		// 赠品
            	$.each($('.activity_gift_products input:checkbox:checked'), function (){
            		gift_products = gift_products + $(this).attr('data-pid') + ':' + $(this).attr('data-gift-num') + ';';
            	});
            	// 特价
            	$.each($('.activity_special_products input:checkbox:checked'), function (){
            		discount_products = discount_products + $(this).attr('data-pid') + ':' + $(this).attr('data-special-num') + ';';
            	});
            	var params = {
            		oid : oid,
            		gift_products : gift_products,
            		discount_products : discount_products
            	};
        		K.post('/order/ajax/save_order_activity_h5.php', params, toOrderFee);
    		} else {
    			var param = {
    	    			oid : oid
    	    		};
    	    	K.location('/order/add_order_fee_h5.php', param);
    		}
    	}
    }
    
    function toOrderFee(ret){
		var oid = $('#oid').val();
		var param = {
    			oid : oid
    		};
    	K.location('/order/add_order_fee_h5.php', param);
	}
    
    function selectAddress()
    {
    	var oid = $('#oid').val();
    	var cid = $('#cid').val();
    	var uid = $('#uid').val();
    	var delivery_type = $('input[name=delivery_type]:checked').val();
    	var params = {
    			oid : oid,
    			cid : cid,
    			order_uid : uid,
    			delivery_type : delivery_type
    	};
    	K.location('/order/add_user_address_h5.php', params);
    }
    
    function getActivity(gift, discount)
    {
    	var oid = $('#oid').val();
    	if (oid && oid !== '0'){
    		var param = {
        			oid : oid,
        			gift_products : gift,
        			discount_products : discount,
        			check_num : check_num
        	}; 
        	check_num = check_num + 1;
        	K.post('/order/ajax/get_order_activity_products_h5.php', param, showActivityHtml);
    	}
    }
    
    function showActivityHtml(ret)
    {
    	$('#activity_html').html(ret.html);
    }
    
    function minusSpecialNum()
    {
    	var pid = $(this).attr('data-pid');
    	var productNum = Number($('#special_num_' + pid).val());
    	if (productNum > 0){
    		$('#special_num_' + pid).val(productNum - 1);
    		$('#special_checkbox_' + pid).attr('data-special-num', productNum - 1);
    	}
    }
    
    function plusSpecialNum()
    {
    	var pid = $(this).attr('data-pid');
    	var productNum = Number($('#special_num_' + pid).val());
    	var maxNum = Number($('#special_num_' + pid).attr('data-max-num'));
    	if (productNum < maxNum){
    		$('#special_num_' + pid).val(productNum + 1);
    		$('#special_checkbox_' + pid).attr('data-special-num', productNum + 1);
    	} else {
    		alert('该特价商品限购数量为' + maxNum);
    	}
    }
    
    function clickGiftProduct()
    {
    	var data = getActivityProduct();
    	getActivity(data.gift, data.special);
    }
    
    function clickSpecialProduct()
    {
    	var data = getActivityProduct();
    	getActivity(data.gift, data.special);
    }
    
    function getActivityProduct()
    {
    	var ret = {
    			gift : 	new Array(),
    			special : new Array()
    	};
    	// 赠品
    	$.each($('.activity_gift_products input:checkbox:checked'), function (){
    		var gift = new Array();
    		gift.push($(this).attr('data-pid'));
    		gift.push($(this).attr('data-price'));
    		gift.push($(this).attr('data-gift-num'));
    		ret.gift.push(gift);
    	});
    	// 特价
    	$.each($('.activity_special_products input:checkbox:checked'), function (){
    		var special = new Array();
    		special.push($(this).attr('data-pid'));
    		special.push($(this).attr('data-price'));
    		special.push($(this).attr('data-special-num'));
    		ret.special.push(special);
    	});
    	ret.gift = JSON.stringify(ret.gift);
    	ret.special = JSON.stringify(ret.special);
    	return ret;
    }
})();