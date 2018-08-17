"use strict";

(function () {
    $('#add_btn').bind('click', addCommunity);
    $('#community_name').bind('focus', gotoSelectCommunity);
    $('#add_address').bind('click', addAddress);
    $('#select_address').bind('click', selectAddress);
    $('.address_detail').bind('click', listSelectAddress);
    function addCommunity() {
    	var cid = $('#cid').val();
        var uid = $('#uid').val();
        var oid = $('#oid').val();
        var contact_phone = $('#contact_phone').val();
        var contact_name = $('#contact_name').val();
        var select_city = $('#select-city').val();
        var select_district = $('#select-district').val();
        var select_area = $('#select-area').val();
        var from = $('#from').val();
        var community_name = $('#community_name').val();
        var community_address = $('#community_address').val();
        var community_lat = $('#lat').val();
        var community_lng = $('#lng').val();
        var detail = $('#address_detail').val();
        var paras = {
        		cid : cid,
        		uid : uid,
        		from : from,
                contact_name: contact_name,
                contact_phone: contact_phone,
                addr_area: 'ddddd',
                addr_code: select_city + '-' + select_district + '-' + select_area,
                addr_communtiy: community_name,
                addr_address: community_address,
                addr_lat: community_lat,
                addr_lng: community_lng,
                addr_detail: detail
            };
        if (paras.contact_name.length == 0) {
        	return alert('请填写收货人');
        }
        if (paras.contact_phone == '0' || paras.contact_phone.length < 10) {
        	return alert('请填写正确的手机号码！');
        }
        if (select_city.length == 0 || select_city == '0') {
        	return alert('请选择城市信息');
        }
        if (select_district.length == 0 || select_district == '0') {
        	return alert('请选择城区信息');
        }
        if (select_area.length == 0 || select_area == '0') {
        	return alert('请选择区域范围信息');
        }
        if (paras.addr_communtiy.length == 0) {
        	return alert('请选择小区/大厦');
        }
        if (paras.addr_detail.length == 0) {
        	return alert('请填写详细地址');
        }
        K.post('/order/ajax/save_address.php', paras, onConfirmOrder);
    }

    function onConfirmOrder(ret) {
    	var cid = $('#cid').val();
        var uid = $('#uid').val();
        var oid = $('#oid').val();
        var from = $('#from').val();
        var platform = $('#platform').val();
        var version = $('#version').val();
        var delivery_type = $('#delivery_type').val();
    	var para = {address_id:ret.id, full_address:ret.address_detail, contact_name:ret.contact_name, contact_phone:ret.contact_phone,community_id:ret.community_id};
        if (platform == "ios") {
            window.webkit.messageHandlers.chooseAddress.postMessage(para);
        } else if (platform == "android") {
            product.chooseAddress(JSON.stringify(para));
        } else {
        	para.uid = uid;
        	para.cid = cid;
        	para.oid = oid;
        	para.delivery_type = delivery_type;
        	K.location('/order/add_order_logistics_h5.php', para);
        }
    }
    
    // 选择小区/大厦
    function gotoSelectCommunity()
    {
    	var cid = $('#cid').val();
        var uid = $('#uid').val();
        var oid = $('#oid').val();
        var contact_phone = $('#contact_phone').val();
        var contact_name = $('#contact_name').val();
        var select_city = $('#select-city').val();
        var select_district = $('#select-district').val();
        var select_area = $('#select-area').val();
        var from = $('#from').val();
        var platform = $('#platform').val();
        var version = $('#version').val();
        var delivery_type = $('#delivery_type').val();
        var params = {
        	cid : cid,
        	uid : uid,
        	oid : oid,
        	contact_phone : contact_phone,
        	contact_name : contact_name,
        	select_city : select_city,
        	select_district : select_district,
        	select_area : select_area,
        	from : from,
        	platform : platform,
        	version : version,
        	delivery_type : delivery_type
        };
        K.location('/order/add_select_map_h5.php', params);
    }
    
    // 填写添加地址
    function addAddress()
    {
    	$(this).attr('class', 'nav selected');
    	$('#select_address').attr('class', 'nav');
    	$('.address_list').hide();
    	$('.add_address').show();
    }
    
    // 选择已有地址
    function selectAddress()
    {
    	$(this).attr('class', 'nav selected');
    	$('#add_address').attr('class', 'nav');
    	$('.add_address').hide();
    	$('.address_list').show();
    }
    
    // 列表中选取地址
    function listSelectAddress()
    {
    	var cid = $('#cid').val();
        var uid = $('#uid').val();
        var oid = $('#oid').val();
        var from = $('#from').val();
        var platform = $('#platform').val();
        var version = $('#version').val();
    	var community_id = $(this).attr('data-community-id');
    	var address_detail = $(this).attr('data-address');
    	var contact_name = $(this).attr('data-contact-name');
    	var contact_phone = $(this).attr('data-contact-phone');
    	var delivery_type = $('#delivery_type').val();
    	var address_id = $(this).attr('data-address-id');
    	var para = {
    			address_id : address_id, 
    			full_address : address_detail, 
    			contact_name : contact_name, 
    			contact_phone : contact_phone,
    			community_id : community_id
    		};
        if (platform == "ios") {
            window.webkit.messageHandlers.chooseAddress.postMessage(para);
        } else if (platform == "android") {
            product.chooseAddress(JSON.stringify(para));
        } else {
        	para.uid = uid;
        	para.cid = cid;
        	para.oid = oid;
        	para.delivery_type = delivery_type;
        	K.location('/order/add_order_logistics_h5.php', para);
        }
    }
})();