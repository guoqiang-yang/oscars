(function(){
    
    function main(){
        showMap();
        getAvailableDrivers();
        
        $('#order_line_view').on('click', regEventOnOrderLineArea);
        $('.wait_order_line').on('click', waitOrderLine);
        $('.top_order_line').on('click', topOrderLine);
        $('.show_pt_BaiduMap').on('click', showPtInBaiduMap);
        
        $('#add_car_model_orderline').on('click', addCarModelOrderLine);
        $('#confirm_order_line').on('click', saveSetOrderLine);
        $('.change_order_line').on('click', changeOrderLine);
        $('#save_changeOrderLine').on('click', saveChangeOrderLine);
        $('#changeOrderLine').on('click', regEventForChangeOrderLine);
        $(document).ready(has_new_order);
    }
    
    function showMap(){
        $(document).ready(function(){
            if ($('#order_map').length){
                var warehousePoints = eval('(' + $('#warehouse_points').html() + ')');
                
                var centerPoint = {lng: 116.404, lat: 39.915};
                if (warehousePoints.length==1){
                    centerPoint = warehousePoints[0];
                }
                
                // 显示地图
                _showBaiduMap('order_map', {lng: 116.404, lat: 39.915}, 10);
                
                // 标仓库点
                _markWarehouseInBaiduMap(warehousePoints);
                
                // 标点
                _markPointInBaiduMap();
                
            }
        });
    }
    
	// 显示地图
	function _showBaiduMap(id, pos, scale) {
		baiduMapHandler = new BMap.Map(id);
		baiduMapHandler.centerAndZoom(new BMap.Point(pos.lng, pos.lat), scale);
		baiduMapHandler.enableScrollWheelZoom(true);
		baiduSearchHandler = new BMap.LocalSearch(baiduMapHandler, {
			renderOptions: {map: baiduMapHandler, autoViewport: true}
		});

		// 添加带有定位的导航控件
		var navigationControl = new BMap.NavigationControl({
			// 靠左上角位置
			anchor: BMAP_ANCHOR_TOP_LEFT,
			// LARGE类型
			type: BMAP_NAVIGATION_CONTROL_LARGE,
			// 启用显示定位
			enableGeolocation: true
		});
		baiduMapHandler.addControl(navigationControl);
	}
    
    function _markWarehouseInBaiduMap(warehousePoints){
        var info;
        var wpointArray = new Array();
        for(var j=0; j<warehousePoints.length; j++){
            // 地图上标点
            info = warehousePoints[j];
            
            var pt = new BMap.Point(info.lng, info.lat);
            var icon = new BMap.Icon('http://vdata.amap.com/icons/b18/1/2.png', new BMap.Size(24,24));
            var marker = new BMap.Marker(pt, {icon:icon}); // 创建点
            
            baiduMapHandler.addOverlay(marker);    //增加点
            
            wpointArray[j] = pt;
        }
        
        //让所有点在视野范围内
        baiduMapHandler.setViewport(wpointArray);
    }
    
    // baidu-map 标点
    function _markPointInBaiduMap(){
        var orderList = eval('(' + $('#order_list').html() + ')');
        var pointArray = new Array();
        
        var oinfo;
        for(var i=0; i<orderList.length; i++){
            
            // 地图上标点
            oinfo = orderList[i];
            var pt = new BMap.Point(oinfo.lng, oinfo.lat);
            var icon = new BMap.Icon(oinfo.mapimg, new BMap.Size(19,29));
            var marker = new BMap.Marker(pt, {icon:icon}); // 创建点
            baiduMapHandler.addOverlay(marker);    //增加点
              marker.disableDragging();
            
            // 添加点击事件
            _showOrderInfoInMap(oinfo, marker, pt, orderList);
            _addClickPointInBaiduMap(oinfo, marker, orderList);
            
            pointArray[i] = pt;
        }

        //让所有点在视野范围内
        baiduMapHandler.setViewport(pointArray);
    }
    
    function _showOrderInfoInMap(oinfo, marker, point, orderList){
        var lng = oinfo.lng;
        var lat = oinfo.lat;
        var oids = [];
        for(var i=0;i<orderList.length;i++)
        {
            if (orderList[i].lng == lng && orderList[i].lat == lat)
            {
                oids.push(orderList[i].oid);
            }
        }
        var ids = oids.join(',');
        var opts = {
            width : 20,     // 信息窗口宽度
            height: 5,     // 信息窗口高度
            title : "" , // 信息窗口标题
            enableMessage:false,//设置允许信息窗发送短息
            message:"订单ID: "+oinfo.oid
        };
        var infoWindow = new BMap.InfoWindow("订单ID: "+ids, opts);  // 创建信息窗口对象
        marker.addEventListener("click", function(){
            baiduMapHandler.openInfoWindow(infoWindow, point); //开启信息窗口
        });
    }
    
    function _addClickPointInBaiduMap(oinfo, marker, orderList){
        var lng = oinfo.lng;
        var lat = oinfo.lat;
        var oids = [];
        for(var i=0;i<orderList.length;i++)
        {
            if (orderList[i].lng == lng && orderList[i].lat == lat)
            {
                oids.push(orderList[i].oid);
            }
        }

        marker.addEventListener("click", function(){
            var ids = oids.join(',');
            // 去掉选中标红
            $('.selected_summary_order').each(function(){
                $(this).removeClass('selected_summary_order');
                $(this).addClass('un_selected_summary_order');
            });

            for (var j=0;j<oids.length;j++)
            {
                var obj = {};
                obj = $('#order_summary_'+oids[j]);
                obj.removeClass('un_selected_summary_order');
                obj.addClass('selected_summary_order');
                obj[0].scrollIntoView(true);

                var pColor = obj.find('.summary_info').css('background-color');
                obj.find('.summary_info').css('background-color', '');
                obj.css('background-color', '#FF0000');
                myTime(obj, pColor);
            }
        });
	}
    
	function myTime(obj, pColor) {
         setTimeout(function(){
            obj.css('background-color', '');
            obj.find('.summary_info').css('background-color', pColor);
        }, 1000);
    }
	
    function getAvailableDrivers(){
        $(document).ready(function(){
            if ($('#show_availabled_driver').length > 0){
                var para = {
                    wid: $('form').find('select[name=wid]').val()
                };
                
                K.post('/logistics/ajax/get_availabled_drivers.php', para, function(ret){
                    $('#show_availabled_driver').html(ret.html);
                    $('#show_availabled_driver').show();
                });
            }
        });
    }
    
    function regEventOnOrderLineArea(evt){
        var tgt = $(evt.target);
        if (tgt.hasClass('close_order_summary_info')){  // 移出选择订单
            tgt.closest('.order_summary_info').remove();
            
        } else if (tgt.hasClass('show_order_detail_info')){ // 显示订单详情

            var para = {
                oid: tgt.closest('.order_summary_info').attr('data-oid')
            };
            // 查看是否存在该模块信息
            var addOids = [];
            $('.order_summary_info').each(function(){
                addOids.push($(this).attr('data-oid'));
            });
            para.add_oids = addOids.join(',');
            
            _showOrderInfo(para);  
            
        } else if (tgt.hasClass('cancel_added_car_model')) { //删除选择的车型
            if ($('#add_orderline_carmodel').find('.had_car_model').length<2){
                alert('取消失败！'); return false;
            }
            tgt.closest('.had_car_model').remove();
        }
        
    }
    
    function _showOrderInfo(para){
        K.post('/logistics/ajax/get_order_detail.php', para, function(res){
            if (res.errno==0){
                $('#show_order_detail_inmap').find('.modal-body').html(res.data.detail);
                $('#show_order_detail_inmap').modal();
//                $('#order_line_summary_info').html(res.data.summary);
//
//                if (res.data.had_add){
//                    $('#ready_to_order_line').html('已装');
//                    $('#ready_to_order_line').attr('disabled', 'true');
//                }
            } else {
                alert('对不起，订单不存在！');
            }
        });
    }
    
    function showPtInBaiduMap(){
        var lng = $(this).attr('data-lng');
        var lat = $(this).attr('data-lat');
        var pt = new BMap.Point(lng, lat);
          
        var oid = $(this).closest('.order_summary_info').attr('data-oid');
        var opts = {
            width : 20,     // 信息窗口宽度
            height: 5,     // 信息窗口高度
            title : "" , // 信息窗口标题
            enableMessage:false,//设置允许信息窗发送短息
            message:"订单ID: "+ oid
        };
        var infoWindow = new BMap.InfoWindow("订单ID: "+oid, opts);  // 创建信息窗口对象 
        baiduMapHandler.openInfoWindow(infoWindow, pt); //开启信息窗口
    }
    
    // 标记等待装车
    function waitOrderLine(){
        var type = $(this).attr('data-type');
        
        if (type == 1){ //取消装车
            $(this).attr('data-type', 0);
            $(this).html('分配订单');
            $(this).css('background-color', 'cornsilk');
        } else { //装车
            var oid = $(this).closest('.order_summary_info').attr('data-oid');
            var cid = $(this).closest('.order_summary_info').attr('data-cid');
            var community_id = $(this).closest('.order_summary_info').attr('data-community-id');
            var para = {
                oid: oid,
                cid: cid,
                community_id: community_id
            };
            K.post('/logistics/ajax/get_refund_order.php', para, function (ret) {
                if (ret.st == 1 && (ret.rids || ret.oids || ret.oids2))
                {
                    var title_str = '该订单有需要';
                    if(ret.rids)
                    {
                        title_str += '随单退货的退货单' + ret.rids + '，';
                    }
                    if(ret.oids)
                    {
                        title_str += '随单换货的补单' + ret.oids + '，';
                    }
                    if(ret.oids2)
                    {
                        title_str += '随单补漏的补单' + ret.oids2 + '，';
                    }
                    title_str += '请务必同时处理！';
                    alert(title_str);
                }
            });
            $(this).attr('data-type', 1);
            $(this).html('取消分配');
            $(this).css('background-color', 'pink');
        }
    }
    
    // 标记置顶
    function topOrderLine(){
        var obj = $(this).closest('.order_summary_info');
        var oid = obj.attr('data-oid');
        var placeholderId = 'placeholder_'+oid;
        
        var type = $(this).attr('data-type');
        if (type == 1){  //取消置顶
            $('#'+placeholderId).after(obj);
            $('#'+placeholderId).remove();
            $(this).attr('data-type', 0);
            $(this).html('置顶');
            $(this).css('background-color', 'cornsilk');
        }
        else{   //置顶
            var placeholderHtml = '<div id="'+placeholderId+'"></div>';
            obj.after(placeholderHtml);
            $('#order_line_area').prepend(obj);
            $(this).attr('data-type', 1);
            $(this).html('取消置顶');
            $(this).css('background-color', 'pink');
        }
        
    }
    
    // 加车型
    function addCarModelOrderLine(){
        var obj = $('#add_orderline_carmodel').find('.had_car_model').clone();
        
        $(obj[0]).css('margin-top', '10px');
        $('.car_area').append(obj[0]);
    }
    
    // 保存排线
    function saveSetOrderLine(){
        var oids = []; //oid:priority,oid:priority,...
        var carModels = []; //car_model:fright,...,...
        var line_type = $('.line_type_area').find('input[name=line_type]:checked').val();

        $('.car_area').find('.had_car_model').each(function(){
            carModels.push($(this).find('select[name=car_model]').val()
                    +':'+$(this).find('input[name=fee]').val()*100);
        });
        $('#order_line_area').find('.wait_order_line').each(function(){
            if ($(this).attr('data-type')==1)
            {
                var obj = $(this).closest('.order_summary_info');
                oids.push(obj.attr('data-oid')+':'+obj.attr('data-priority'));
            }
        });
        
        var para = {
            oids: oids.join(','),
            car_models: carModels.join(','),
            line_type: line_type
        };

        $('#confirm_order_line').attr('disabled', true);
        K.post('/logistics/ajax/save_order_line.php', para,
            function(ret){
                var msg = '排线成功！';
                
                if (ret.retno == 2){
                    msg += '分配司机成功！';
                } else if (ret.retno == 1) {
                    msg += '部分分配司机！';
                } else {
                    msg += '未分配司机！';
                }
                        
                alert(msg);
                window.location.reload();
            },
            function(err){
                alert(err.errmsg);
                $('#confirm_order_line').attr('disabled', false);
            }
        );
    }
    
    function changeOrderLine(){
        var dlg = $('#changeOrderLine');
        var obj = $(this).closest('.line_info');
        var opType = $(this).attr('data-type');
        var lineId = obj.attr('data-id');
        
        var modalTitle = '';
        if (opType == 'modify_order'){
            modalTitle = '修改排线-修改线路订单';
            dlg.find('.rejust_reason').hide();
        } else if (opType == 'chg_carmodel'){
            modalTitle = '修改排线-更换车型';
            dlg.find('.rejust_reason').hide();
        } else if (opType == 'cancel'){ 
            modalTitle = '修改排线-取消排线';
            dlg.find('.rejust_reason').show();
        } else if(opType == 'reject') {
            modalTitle = '修改排线-司机拒单';
            dlg.find('.rejust_reason').show();
        } else if(opType == 'arrive') {
            modalTitle = '请选择需要确认送达的订单';
            dlg.find('.rejust_reason').show();
        }
        
        
        if(modalTitle.length==0 || lineId.length==0){
            alert('操作失败，请重试！'); return false;
        }

        dlg.find('.modal-title').html(modalTitle);
        dlg.find('#save_changeOrderLine').attr('data-id', lineId);
        dlg.find('#save_changeOrderLine').attr('data-optype', opType);
        if (opType == 'modify_order' || opType=='chg_carmodel'){
            $('#save_changeOrderLine').hide();
        } else {
            $('#save_changeOrderLine').show();
        }
        
        var para = {line_id:lineId, optype:opType};
        K.post('/logistics/ajax/get_order_line_info.php', para, function(ret){
            $('#changeOrderLine').find('.modal-body').html(ret.html);
            
            dlg.modal();
        });
    }
    
    function regEventForChangeOrderLine(evt){
        var tgt = $(evt.target);
        var lineId = tgt.closest('form').attr('data-lineid');
        var para;
        if (tgt.hasClass('del_modify_order')){  // 移出选择订单
            para = {
                op_type: 'del_modify_order',
                line_id: lineId,
                oid: tgt.closest('tr').attr('data-oid')
            };
            K.post('/logistics/ajax/change_order_line.php', para, function(){
                window.location.reload();
            });
        } else if (tgt.hasClass('query_modify_order')){
            para = {
                optype: 'query_modify_order',
                add_oid: tgt.closest('form').find('input[name=add_oid]').val()
            };
            if (para.add_oid.length==0){
                alert('请输入订单号！'); return false;
            }
            K.post('/logistics/ajax/get_order_line_info.php', para, function(ret){
                $('#changeOrderLine').find('.show_query_modify_order').append(ret.html);
            });
            
        } else if (tgt.hasClass('add_modify_order')) {
            para = {
                op_type: 'add_modify_order',
                line_id: lineId,
                oid: tgt.closest('tr').attr('data-oid')
            };
            
            K.post('/logistics/ajax/change_order_line.php', para, function(){
                window.location.reload();
            });
        } else if (tgt.hasClass('show_add_car_model')) {
            var obj = tgt.parent().find('.copy_car_model').clone();
            obj.show();
            tgt.closest('form').append(obj);
        } else if (tgt.hasClass('del_chg_carmodel')) { // 删除车型
            tgt.remove();
            para = {
                op_type: 'del_chg_carmodel',
                line_id: lineId,
                car_model: tgt.attr('data-car_model'),
                did: tgt.attr('data-did'),
                step: tgt.attr('data-step')
            };
            K.post('/logistics/ajax/change_order_line.php', para, function(){
                window.location.reload();
            });
        } else if (tgt.hasClass('add_chg_carmodel')) { // 添加车型
            var obj = tgt.closest('.copy_car_model');
            para = {
                op_type: 'add_chg_carmodel',
                line_id: lineId,
                car_model: obj.find('select[name=add_car_model]').val(),
                price: obj.find('input[name=add_price]').val()
            };

            tgt.remove();
            K.post('/logistics/ajax/change_order_line.php', para, function(){
                window.location.reload();
            });
        }
    }
    
    function saveChangeOrderLine(){
        var para = {
            line_id: $(this).attr('data-id'),
            op_type: $(this).attr('data-optype'),
            reason: $('#changeOrderLine').find('textarea[name=reason]').val()
        };

        //送达订单
        if (para.op_type == 'arrive')
        {
            var oids = '';
            $.each($('input:checked'), function(i, v){
                oids += v.value+',';
            })
            var line_id = $('#line_id').val();
            if (oids == '')
            {
                alert('请选择订单');
                return false;
            }
            var remark = $('#remark').val();
            var para = {oids:oids,remark:remark,line_id:line_id};

            K.post('/logistics/ajax/order_arrive.php', para, function(){
                alert('操作已成功！');
                window.location.reload();
            });
            return;
        }
        var drivers = [];
        var driverNum = 0;
        $('#changeOrderLine').find('input[name=chg_driver]').each(function(){
            if ($(this).is(':checked')){
                drivers.push($(this).attr('date-did'));
            };
            driverNum++;
        });
        
        if (driverNum>0 && drivers.length==0){
            alert('请选择待处理的司机！'); return false;
        }
        para.dids = JSON.stringify(drivers);
        
        $(this).attr('disabled', true);
        K.post('/logistics/ajax/change_order_line.php', para, 
            function(){
                alert('操作已成功！');
                window.location.reload();
            },
            function(err){
                alert(err.errmsg);
                $('#save_changeOrderLine').attr('disabled', false);
            }
        );
    }

    function has_new_order() {
        if ($('#order_line_area').length) {
            var max_oid = $('#order_line_area').attr('data-max-oid');
            setInterval(function () {
                var para ={
                    max_oid: max_oid,
                    wid:$('select[name=wid]').val(),
                    delivery_data: $('input[name=delivery_data]').val(),
                    delivery_btime: $('input[name=delivery_btime]').val(),
                    delivery_etime: $('input[name=delivery_etime]').val()
                };

                K.post('/logistics/ajax/has_new_unline_order.php', para,
                    function(ret){
                        if (ret.num > 0){
                            $('#flush_orders').html('有<span style="color:red">' + (ret.num) +'</span>条新的待调度订单！请点击加载');
                            $('#new_unline_order').show();
                        }
                    }
                );
            },20000);
        }
    }


    main();
})();