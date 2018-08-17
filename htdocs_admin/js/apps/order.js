(function () {

    function main() {
        $('#_j_btn_save_order_step1').click(onSaveOrderStep1);
        $('#_j_btn_save_order_edit').click(onSaveOrderEdit);
        $('._j_del_order_product').click(onDeleteOrderProduct);
        $('._j_delete_order').click(onDeleteOrder);
        $('._j_chg_order_step').click(onChgOrderStep);
        $('#_j_btn_save_refund').click(onSaveRefund);
        $('._j_chg_refund_step').click(onChgRefundStep);
        $('._j_back_order_step').click(onBackOrderStep);
        $('._j_order_product_warehouse').click(showOrderProductsWarehouse);

        $('._j_save_order_note').click(onSaveOrderNote);
        $('._j_save_customer_order_note').click(onSaveCustomerOrderNote);
        $('._j_save_order_driver').click(onSaveOrderDriver);
        $('._j_save_money_in').click(onSaveMoneyIn);
        $('#_j_use_balance_paid').click(useBalancePaid);
        $('._j_adjust_money_in').click(onAdjustMoneyIn);
        $('._j_adjust_money_out').click(onAdjustMoneyOut);
        $('._j_pay_privilege_money_out').click(financePayForMoneyOut);
        $('#_j_order_finance_modify').click(financeModifyOrder);
        $('#_j_order_operator_modify').click(operatorModifyOrder);
        $('#_j_confirm_sale_preferential_amount').click(saleModifyOrderPreferential);
        $('._j_delete_refund').click(deleteRefund);
        $('._j_rebut_refund').click(rebutRefund);
        $('._j_save_carriage').click(saveCarriage);
        $('._j_chg_delivery_type').click(changeDeliveryType);

        $('._j_notice_print').click(onNoticePrint);
        $('#set_part_paid').click(onSetPartPaid);
        $('#refund_and_delete').click(onRefundAndDelete);
        $('#order_delete').click(onOrderDelete);
        $('#service_as_soon').click(onServiceAsSoon);
        
        //切换城市
        $('#change_product_city').click(onChangeProductCity);
        $('#change_order_city_done').click(onChangeProductCityDone);
        
        $('.select_city_4_order').on('click', selectCity4Order);
        $('#confirmChangeOrderCity').on('click', confirmChangeOrderCity);

        // 司机 && 搬运工新逻辑
        $('.select_driver_carrier').click(showSelectDriverCarrier);
        $('#addCoopworkerModal').click(addCoopworker);
        $('#_j_coopworker_area').click(execCoopworder); //coopworker区域操作
        $('._j_save_modify_Coopworker').click(saveModifyCoopworker);
        $('.show_paid_for_coopworker').click(showPaidForCoopworker);
        $('#_j_paid_for_coopworker').click(payForCoopworker);

        $('#search_toggle').click(function () {
            $('#search_other_condition').toggle();
        });
        $('#add_product').click(onOrderSelectProduct);

        // 修改订单的销售
        $('._j_save_edit_saler').click(saveEditSaler);

        //订单详情页转入余额
        $('#transfer_amount_in_order').on('click', showTransferAmountInOrder);
        $('#confirmTransferAmount').on('click', confirmTransferAmountInOrder);

        //恢复订单
        $('._j_reset_order').on('click', resetOrder);
        //取消订单
        $('._j_cancel_order').click(onCancelOrder);
        $('#save_service').on('click', onSaveService);

        //选择拣货组
        //$('.change_picking_group').on('click', changePickingGroup);

        //修改数量
        $('.change_product_num').on('click', changeProductNum);

        $('#no_print').on('click', showNoPrint);

        // 新建小区
        var baiduMapHandler = null;
        var baiduSearchHandler = null;

        $('#community_city').on('change', changeCommunityCity);
        $('#community_district').on('change', changeCommunityDistrict);
        $('#search_community_inmap').on('click', searchCommunityInmap);
        $('#add_community_inmap').on('click', addCommunityInmap);

        $('._j_not_local_order').on('click', notLocalOrder);

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
                    nextStep: autoSuggestNextStep
                });
            });
        }

        //推荐仓库
        if ($('#recommend_order_wid').length) {
            $(document).ready(function () {
                var cmid = $('#recommend_order_wid').attr('data-cmid');
                if (parseInt(cmid) > 0) {
                    var para = {cmid:cmid};
                    K.post('/order/ajax/recommend_order_wid.php', para, function (ret) {
                        $('#recommend_order_wid').html(ret.html);
                    });
                }

            });
        }

        // 显示添加小区的弹框
        $('#show_add_new_community').on('click', showAddNewCommunity);
        //显示历史工地的弹框
        $("#show_order_community_list").on('click', showOrderCommunity);
        //编辑小区
        $('.edit_communtiy').on('click', showEditCommunity);
        //合并小区
        $('.merge_communtiy').on('click', showMergeCommunity);
        $('#_j_btn_merge_community').on('click', mergeCommunity);

        // 客户的地址
        $('.del_customer_construction').on('click', delCustomerConstruction);
        $('.modify_custoemr_construction').on('click', showModifyCustomerConstruction);
        $('#confirm_save_construction').on('click', confirmSaveConstruction);

        // 担保订单
        $('._j_save_guaranteed_order').on('click', guaranteedOrder);

        // 刷新拣货详情页的拣货区的商品
        $('.refresh_picking_product').on('click', refreshPickingProduct);
        $('.mark_vnum_flag').on('click', markVnumFlag);

        //计算小区和仓库之间距离
        $('#cal_distance').on('click', calDistinceWithWarehouse);

        $('.edit_coopworker_srcinfo').on('click', countTotalPrice);

        //查看商品占用
        $('.get_occupied_products').on('click', showOccupiedByOrder);

        //批量选择删除的商品
        $('form').on('click', clickFormArea);
        $('#bulk_del_order_products').on('click', bulkDelOrderProducts);

        //重置编辑司机、搬运工运费模态框
        $('#editCoopworkerPrice').on('hidden.bs.modal', resetEditCoopworkerPriceModal);

        //订单列表页立送货日期筛选
        $('#deliver_date_type_select').on('change', changeDeliverDateType);
        
        //修正异常占用
        $('.clear_err_occupied').on('click', clearErrOccupied);
    }

    function autoSuggestNextStep() {
        var obj = $(".as-selected");
        var value = eval('(' + decodeURIComponent(obj.attr('data-value')) + ')');

        $('#select-city').val(value.city_id);
        $('#select-city').trigger('change');
        $('#select-district').val(value.district_id);
        $('#select-district').trigger('change');
        $('#select-area').val(value.ring_road);
        $('#select-area').trigger('change');
        obj.closest('.form-group').find('input[name=community_id]').val(value.cmid);

        // add more-info to DOM For show in map
        var cObj = $('#show_add_new_community');
        cObj.attr('data-zone', value.city_id + ':' + value.district_id + ':' + value.ring_road);
        cObj.attr('data-status', value.status);
        cObj.attr('data-pos', value.pos);
        cObj.closest('#addr_community').find('input[name=community_address]').val(value.address);

        //推荐仓库
        var para = {cmid: value.cmid};
        K.post('/order/ajax/recommend_order_wid.php', para, function (ret) {
            $('#recommend_order_wid').html(ret.html);
        });
    }

    function showOrderCommunity() {
        var para = {
            oid: $(this).attr('data-oid')
        };
        var box = $('#order_community_list');
        box.modal();
        K.post('/order/ajax/get_order_community.php', para, function (ret) {
            $('#show_community_html').html(ret.html);
            $('#show_community_html a').each(function () {
                $(this).on('click', addCommunity2Order);
            });
        });
    }

    function addCommunity2Order() {
        var value = eval('(' + decodeURIComponent($(this).attr('data-value')) + ')');

        $('#select-city').val(value.city);
        $('#select-city').trigger('change');
        $('#select-district').val(value.district);
        $('#select-district').trigger('change');
        $('#select-area').val(value.ring_road);
        $('#select-area').trigger('change');
        $('#auto_suggest_position').val(value.community_name);
        $('body').find('input[name=community_id]').val(value.community_id);
        $('body').find('input[name=community_address]').val(value.community_addr);
        $('body').find('input[name=addr_detail]').val(value.address);

        var zone = value.city + ':' + value.district + ':' + value.ring_road;
        var pos = value.lng + ':' + value.lat;
        $('body').find('#show_add_new_community').attr('data-status', value.status);
        $('body').find('#show_add_new_community').attr('data-zone', zone);
        $('body').find('#show_add_new_community').attr('data-pos', pos);

        $('#order_community_list').modal('hide');
    }

    function showAddNewCommunity() {
        var pos = $(this).attr('data-pos');
        var zone = $(this).attr('data-zone');

        if (typeof pos != 'undefined' && typeof zone != 'undefined'
            && pos.length != 0 && zone.length != 0
            && pos != '0:0' && zone != '0:0:0') {

            var communtiyInfo = {
                pos: $(this).attr('data-pos').split(':'),
                zone: $(this).attr('data-zone').split(':'),
                name: $(this).closest('#addr_community').find('input[name=community_name]').val(),
                address: $(this).closest('#addr_community').find('input[name=community_address]').val(),
                cmid: $(this).closest('#addr_community').find('input[name=community_id]').val(),
                pos_str: $(this).attr('data-pos'),  //formate {lng:lat}
                status: $(this).attr('data-status'),
                from: $(this).attr('data-from')
            };

            _showEditCommunity(communtiyInfo);
        }
        else {
            _showAddNewCommunity();
        }
    }

    function _showAddNewCommunity() {
        var box = $('#add_new_community');
        box.modal();

        box.on('shown.bs.modal', function () {
            // 显示地图
            var curCityPoi = eval('(' + $('#curr_city_poi-json').html() + ')');
            
            if (typeof curCityPoi == 'undefined')
            {   
                curCityPoi = {lng: 116.404, lat: 39.915}; //北京
            }
                
            showBaiduMap(curCityPoi, 10);
        });

        box.find('#community_city').html('<option value="0">选择城市</option>');
        var city = eval('(' + $('#city-json').html() + ')');
        for (var i in city) {
            var option = '<option value="' + i + '">' + city[i] + '</option>';
            box.find('#community_city').append(option);
        }
        box.find('#community_district').val(0);
        box.find('#community_area').val(0);

        box.find('input[name=cm_name]').val('');
        box.find('textarea[name=cm_address]').val('');
        box.find('input[name=cm_lng]').val('');
        box.find('input[name=cm_lat]').val('');

        box.find('.modal-title').html('添加新小区');
        box.find('#add_community_inmap').attr('data-cmid', '0');
        box.find('#add_community_inmap').attr('data-pos', '');
        box.find('.cm_status').hide();
    }

    function showEditCommunity() {
        var obj = $(this).closest('.community_dialog');

        var communtiyInfo = {
            pos: obj.attr('data-pos').split(':'),
            zone: obj.attr('data-zone').split(':'),
            name: obj.find('.name').html(),
            alias: obj.find('.alias').html(),
            address: obj.find('.address').html(),
            cmid: obj.attr('data-cmid'),
            pos_str: obj.attr('data-pos'),  //formate {lng:lat}
            status: obj.attr('data-status'),
        };

        _showEditCommunity(communtiyInfo);
    }

    function _showEditCommunity(communtiyInfo) {
        var box = $('#add_new_community');

        box.modal();
        box.on('shown.bs.modal', function () {

            // 显示地图
            showBaiduMap({lng: communtiyInfo.pos[0], lat: communtiyInfo.pos[1]}, 16);
            // 创建标注
            markPointInBaiduMap(communtiyInfo.pos[0], communtiyInfo.pos[1]);
        });

        $('#community_city').html('<option value="0">选择城市</option>');
        var city = eval('(' + $('#city-json').html() + ')');
        for (var i in city) {
            var option = '<option value="' + i + '">' + city[i] + '</option>';
            $('#community_city').append(option);
        }

        box.find('#community_city').val(communtiyInfo.zone[0]);
        box.find('#community_city').trigger('change');
        box.find('#community_district').val(communtiyInfo.zone[1]);
        box.find('#community_district').trigger('change');
        box.find('#community_area').val(communtiyInfo.zone[2]);

        box.find('input[name=cm_name]').val(communtiyInfo.name);
        box.find('input[name=cm_alias]').val(communtiyInfo.alias);
        box.find('textarea[name=cm_address]').val(communtiyInfo.address);
        box.find('input[name=cm_lng]').val(communtiyInfo.pos[0]);
        box.find('input[name=cm_lat]').val(communtiyInfo.pos[1]);

        box.find('.modal-title').html('编辑小区');
        box.find('#add_community_inmap').attr('data-cmid', communtiyInfo.cmid);
        box.find('#add_community_inmap').attr('data-pos', communtiyInfo.pos_str);
        box.find('#add_community_inmap').attr('data-from', communtiyInfo.from);
        box.find('.cm_status').show();

        box.find('input[name=cm_status]').attr('checked', false);
        if (communtiyInfo.status == '0') {
            box.find('input[name=cm_status]')[0].checked = true;
        } else if (communtiyInfo.status == '1') {
            box.find('input[name=cm_status]')[1].checked = true;
        }
    }

    function showMergeCommunity() {
        var obj = $(this).closest('.community_dialog');
        var communtiyInfo = {
            name: obj.find('.name').html(),
            alias: obj.find('.alias').html(),
            address: obj.find('.address').html(),
            cmid: obj.attr('data-cmid')
        };
        var box = $('#merge_community');

        box.modal();

        box.find('input[name=cm_cmid]').val(communtiyInfo.cmid);
        //box.find('input[name=cm_name]').val(communtiyInfo.name);
        //box.find('input[name=cm_alias]').val(communtiyInfo.alias);
        //box.find('textarea[name=cm_address]').val(communtiyInfo.address);

        var curCommunity = box.find('._j_current_community');
        curCommunity.html(communtiyInfo.name);

        if (communtiyInfo.alias && communtiyInfo.alias.length > 0) {
            curCommunity.append("　[别名:]" + communtiyInfo.alias);
        }
        curCommunity.append("　[地址:]" + communtiyInfo.address);
    }

    function mergeCommunity() {
        var box = $('#merge_community');
        var cmid = box.find('input[name=cm_cmid]').val();
        var toCmid = box.find('input[name=cm_to_cmid]').val();

        if (!toCmid || toCmid.length == 0) {
            alert('请填写合并到哪个小区');
            return;
        }

        var para = {from_cmid: cmid, to_cmid: toCmid};
        K.post('/order/ajax/merge_community.php', para, function (data) {
            window.location.reload();
            return;
        });
    }

    function changeCommunityCity() {
        var distinct = eval('(' + $('#distinct-json').html() + ')');
        var curCity = $('#community_city').val();
        if (distinct.hasOwnProperty(curCity)) {
            $('#community_district').html('');
            $('#community_district').append('<option value="0">选择区域</option>');

            for (var i in distinct[curCity]) {
                $('#community_district').append('<option value="' + i + '">' + distinct[curCity][i] + '</option>');
            }
        }
    }

    function changeCommunityDistrict() {
        var areas = eval('(' + $('#area-json').html() + ')');
        var curDistrict = $('#community_district').val();
        if (areas.hasOwnProperty(curDistrict)) {
            $('#community_area').html('');
            if (K.keys(areas[curDistrict]).length > 1) {
                $('#community_area').append('<option value="0">选择环线</option>');
            }

            for (var i in areas[curDistrict]) {
                $('#community_area').append('<option value="' + i + '">' + areas[curDistrict][i] + '</option>');
            }
        }
    }

    // 显示地图
    function showBaiduMap(pos, scale) {
        baiduMapHandler = new BMap.Map("allmap");
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
        // 监听点击事件
        baiduMapHandler.addEventListener("click", function (e) {
            $('#add_new_community').find('input[name=cm_lng]').val(e.point.lng);
            $('#add_new_community').find('input[name=cm_lat]').val(e.point.lat);
            if (!e.overlay) {
                baiduMapHandler.clearOverlays();
                markPointInBaiduMap(pos.lng, pos.lat);
                getTenAroundCommunitys(e.point.lng, e.point.lat);
            }
        });
        getTenAroundCommunitys(pos.lng, pos.lat);
    }

    function getTenAroundCommunitys(lng, lat) {
        $.ajax({
            url: '/order/ajax/get_around_community.php',
            type: 'POST',
            data: 'lng=' + lng + '&lat=' + lat,
            dataType: 'json',
            success: function (ret) {
                if (ret.errno == 1) {
                    var obj_list = ret.list;
                    $.each(obj_list, function (i, value) {
                        var content = '<p>小区ID：' + this.cmid + '</p>';
                        content += '<p>小区名称：' + this.name + '</p>';
                        content += '<p>小区地址：' + this.city + this.district + this.address + '</p>';
                        addMarker(this.lng, this.lat, content);
                    });
                }
            }
        });
    }

    // 标记点
    function markPointInBaiduMap(lng, lat) {
        var point = new BMap.Point(lng, lat);
        var marker = new BMap.Marker(point);
        baiduMapHandler.addOverlay(marker);
        marker.disableDragging();
    }

    // 编写自定义函数,创建标注
    function addMarker(lng, lat, content) {
        var point = new BMap.Point(lng, lat);
        var myIcon = new BMap.Icon("http://haocaisong.oss-cn-hangzhou.aliyuncs.com/common/b0.png", new BMap.Size(19, 29));
        var marker = new BMap.Marker(point, {icon: myIcon});
        baiduMapHandler.addOverlay(marker);
        addClickHandler(content, marker);
    }

    function addClickHandler(content, marker) {
        marker.addEventListener("click", function (e) {
                openInfo(content, e)
            }
        );
    }

    //标注信息窗口
    function openInfo(content, e) {
        var opts = {
            width: 250,     // 信息窗口宽度
            height: 100,     // 信息窗口高度
            title: "", // 信息窗口标题
            enableMessage: true//设置允许信息窗发送短息
        };
        var p = e.target;
        var point = new BMap.Point(p.getPosition().lng, p.getPosition().lat);
        var infoWindow = new BMap.InfoWindow(content, opts);  // 创建信息窗口对象
        baiduMapHandler.openInfoWindow(infoWindow, point); //开启信息窗口
    }

    function searchCommunityInmap() {
        var keyword = $('#add_new_community').find('input[name=cm_name]').val();

        baiduSearchHandler.search(keyword);
    }

    function addCommunityInmap() {
        var dialog = $('#add_new_community');
        var para = {
            //cmid: $(this).attr('data-cmid'),
            city_id: dialog.find('select[name=cm_city]').val(),
            district_id: dialog.find('select[name=cm_district]').val(),
            ring_road: dialog.find('select[name=cm_ring_road]').val(),
            name: dialog.find('input[name=cm_name]').val(),
            alias: dialog.find('input[name=cm_alias]').val(),
            address: dialog.find('textarea[name=cm_address]').val(),
            lng: dialog.find('input[name=cm_lng]').val(),
            lat: dialog.find('input[name=cm_lat]').val(),
            status: dialog.find('input[name=cm_status]:checked').val(),
            merge_to_cmid: dialog.find('input[name=cm_merge_to_cmid]').val()
        };

        if ($(this).attr('data-from') != 'order') {
            para.cmid = $(this).attr('data-cmid');
        }

        if (para.city_id == '0' || para.district_id == '0'
            || para.name.length == 0 || para.address.length == 0 || para.lng.length == 0
            || para.lat.length == 0) {
            alert('请将信息填写完全！');
            return;
        }

        if (para.ring_road == '0') {
            alert('请选择环线位置');
            return;
        }

        if (para.cmid != '0' && typeof para.status == 'undefined') {
            alert('请选择审核状态');
            return;
        }

        var pos = $(this).attr('data-pos').split(':');
        var isShiftPos = (pos[0] == para.lng && pos[1] == para.lat) ? 0 : 1;
        if (para.cmid != "0" && isShiftPos) {
            if (!confirm('小区的坐标有改动是否保存？')) {
                return false;
            }
        }

        $(this).attr('disabled', 'true');
        K.post('/order/ajax/edit_community.php', para, function (ret) {

            if ($('body').find('input[name=community_id]').length > 0
                && $('#auto_suggest_position').length > 0) {
                var value = ret.data.cdata;
                $('#select-city').val(value.city_id);
                $('#select-city').trigger('change');
                $('#select-district').val(value.district_id);
                $('#select-district').trigger('change');
                $('#select-area').val(value.ring_road);
                $('#select-area').trigger('change');
                $('#auto_suggest_position').val(value.name);
                $('body').find('input[name=community_id]').val(ret.data.cmid);

                var zone = value.city_id + ':' + value.district_id + ':' + value.ring_road;
                var pos = value.lng + ':' + value.lat;
                $('body').find('#show_add_new_community').attr('data-status', value.status);
                $('body').find('#show_add_new_community').attr('data-zone', zone);
                $('body').find('#show_add_new_community').attr('data-pos', pos);

                $('#add_new_community').modal('hide');
                alert('保存成功！');
            }
            else {
                alert('保存成功！');
                window.location.reload();
            }
        });
    }

    function delCustomerConstruction() {
        var para = {
            id: $(this).closest('.dialog').attr('data-id'),
            otype: 'del'
        };

        if (confirm('确认要删除该 地址？')) {
            K.post('/crm2/ajax/modify_construction_site.php', para, function () {
                alert('删除成功！');
                window.location.reload();
            });
        }
    }

    function showModifyCustomerConstruction() {
        var obj = $(this).closest('.dialog');
        var addr = obj.find('.addr').html();

        var dialog = $('#matchCommunity');

        dialog.find('input[name=community_name]').val('');
        dialog.find('input[name=community_id]').val('0');
        dialog.find('select[name=city]').val('0');
        dialog.find('select[name=district]').val('0');

        dialog.find('input[name=address]').val(addr);
        dialog.find('.position').html(obj.find('.position').html());
        $('#confirm_save_construction').attr('data-id', obj.attr('data-id'));

        var communityObj = obj.find('.community_name');
        if (communityObj.length != 0) {
            $('#auto_suggest_position').val(communityObj.html());
        }
        dialog.modal();
    }

    function confirmSaveConstruction() {
        var dialog = $('#matchCommunity');
        var para = {
            community_id: dialog.find('input[name=community_id]').val(),
            id: $(this).attr('data-id'),
            address: dialog.find('input[name=address]').val(),
            otype: 'match'
        };

        if (para.address.length == 0) {
            alert('工地地址不能为空！');
            return false;
        }
        if (para.community_id.length == 0 || para.community_id == '0') {
            alert('参数错误！');
            return false;
        }

        K.post('/crm2/ajax/modify_construction_site.php', para, function () {
            alert('修改成功！');
            window.location.reload();
        });
    }

    // 删除订单商品
    function onDeleteOrderProduct(ev) {
        var tgt = $(ev.currentTarget),
            oid = tgt.closest('form').find('input[name=oid]').val(),
            pid = tgt.closest('tr').data('pid'),
            para = {oid: oid, pid: pid};

        if (confirm('确认删除该商品？')) {
            K.post('/order/ajax/delete_product.php', para, _onDeleteProductSuccess);
        }
    }

    function _onDeleteProductSuccess(data) {
        window.onbeforeunload = function () {
        };
        window.location.reload();
    }

    // 删除订单
    function onDeleteOrder(ev) {
        var tgt = $(ev.currentTarget),
            oid = tgt.closest('tr').data('oid'),
            para = {oid: oid};

        if (confirm('确认删除该订单？')) {
            K.post('/order/ajax/delete_order.php', para, _onDeleteOrderSuccess);
        }
    }

    function _onDeleteOrderSuccess(data) {
        if (data.url) {
            window.location.href = data.url;
        } else {
            window.location.reload();
        }
    }

    //恢复订单
    function resetOrder(ev) {
        console.log('reset order...');
        var tgt = $(ev.currentTarget),
            oid = tgt.closest('tr').data('oid'),
            para = {oid: oid};

        if (confirm('确认要恢复该订单？')) {
            K.post('/order/ajax/reset_order.php', para, _onResetOrderSuccess);
        }
    }

    // 删除订单
    function onCancelOrder(ev) {
        var tgt = $(ev.currentTarget),
            oid = tgt.closest('tr').data('oid'),
            para = {oid: oid};

        if (confirm('确认取消该订单？')) {
            K.post('/order/ajax/cancel_order.php', para, _onCancelOrderSuccess);
        }
    }

    function _onCancelOrderSuccess(data) {
        if (data.url) {
            window.location.href = data.url;
        } else {
            window.location.reload();
        }
    }

    function _onResetOrderSuccess(data) {
        if (data.url) {
            window.location.href = data.url;
        } else {
            window.location.reload();
        }
    }

    // 保存商品
    function onSaveProducts(ev) {
        var tgt = $(ev.currentTarget),
            oid = tgt.data('oid'),
            para = {oid: oid},
            products = [];

        $('._j_product_item').each(function () {
            var cb = $(this),
                pid = cb.data('pid'),
                num = parseInt(cb.find('input[name=num]').val()),
                note = cb.find('input[name=note]').val();
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

    function _onSaveProductsSuccess(data) {
        window.onbeforeunload = function () {
        };
        window.location.reload();
    }

    // 选择商品
    function onOrderSelectProduct(ev) {
        ev.preventDefault();
        var tgt = $(ev.currentTarget),
            oid = $('#dlgAddProduct').data('oid'),
            para = {href: tgt.attr('href'), oid: oid};

        K.post('/order/ajax/dlg_get_products.php', para, _onGetProductsSuccess);
    }

    function _onGetProductsSuccess(data) {
        $('#product_list_container').html('').append($(data.html));

        bindEvent();
    }

    //订单编辑 - 搜索框
    function onOrderSearchProductKeydown(ev) {
        if (event.keyCode == 13) {
            onOrderSearchProduct(ev);
        }
    }

    function onOrderSearchProduct(ev) {
        ev.preventDefault();
        var tgt = $(ev.currentTarget),
            oid = $('#dlgAddProduct').data('oid'),
            keyword = $('._j_form').find('input[name=keyword]').val(),
            start = $(this).attr('data-start');
            para = {keyword: keyword, oid: oid, start: start}

        K.post('/order/ajax/dlg_get_products.php', para, _onOrderSearchProductSuccess);
    }

    function _onOrderSearchProductSuccess(data) {
        $('#product_list_container').html('').append($(data.html));

        bindEvent();
    }

    // 保存订单
    function _getOrderFormInfo() {
        var para = {
            oid: $('input[name=oid]').val(),
            cid: $('input[name=cid]').val(),
            uid: $('input[name=uid]').val(),
            wid: $('select[name=wid]').val(),
            contact_name: $('input[name=contact_name]').val(),
            contact_phone: $('input[name=contact_phone]').val(),
            contact_phone2: $('input[name=contact_phone2]').val(),
            city: $('select[name=city]').val(),
            district: $('select[name=district]').val(),
            area: $('select[name=area]').val(),
            //address: $('input[name=address]').val(),
            community_name: $('input[name=community_name]').val(),
            addr_detail: $('input[name=addr_detail]').val(),
            community_id: $('input[name=community_id]').val(),
            freight: $('input[name=freight]').val(),
            privilege_special: $('input[name=privilege_special]').val(),
            privilege_special_desc: $('input[name=privilege_special_desc]').val(),
            pre_order_privilege: $('input[name=pre_order_privilege]').val(),
            delivery_date: $('input[name=delivery_date]').val(),
            delivery_time: $('select[name=delivery_time]').val(),
            delivery_time_end: $('select[name=delivery_time_end]').val(),
            delivery_type: $('._j_chg_delivery_type:checked').val(),
            order_step: $('select[name=order_step]').val(),
            note: $('textarea[name=note]').val(),
            customer_note: $('textarea[name=customer_note]').val(),
            driver_name: $('input[name=driver_name]').val(),
            driver_phone: $('input[name=driver_phone]').val(),
            driver_money: $('input[name=driver_money]').val(),
            payment_type: $('select[name=payment_type]').val(),
            service: $('#service').val(),
            floor_num: $('#floor-num').val(),
            customer_carriage: $('#carry_fee').val(),
            source: $('input[name=source]:checked').val(),
            is_print_price: $('input[name=is_print_price]:checked').val()
        };
        return para;
    }

    function onSaveOrderStep1() {

        if ($('#_j_btn_save_order_step1').attr('disabled')) return;

		var deliveryType = $('._j_chg_delivery_type:checked').val();
		if (deliveryType == 1) {
//			var district = $('#select-district').val();
//			if (district == 0) {
//				alert('请选择城区');
//				return false;
//			}

            var address = $('input[name=address]').val();
            if (K.isEmpty(address)) {
                alert('请填写详细地址');
                return false;
            }
        }

        $('#_j_btn_save_order_step1').attr('disabled', true);

        var para = _getOrderFormInfo();
        var gift_products = '';
        var discount_products = '';
        $('input[type="checkbox"][name="gift_pid"]:checked').each(function() {
            var pid = $(this).val();
            var num = $(this).data('num');
            gift_products += pid+':'+num+';';
        });
        var error_status = false;
        $('input[type="checkbox"][name="special_price_pid"]:checked').each(function() {
            var pid = $(this).val();
            var num = Number($(this).parent().next().next().next().next().children("input:first-child").val());
            var max_num = Number($(this).parent().next().next().next().next().children("input:first-child").data('num'));
            if(num == 0 || max_num < num)
            {
                error_status = true;
                alert('特价商品PID:'+pid+' 购买数量错误');
                return false;
            }
            discount_products += pid+':'+num+';';
        });
        if(error_status)
        {
            return false;
        }
        para.gift_products = gift_products;
        para.discount_products = discount_products;
        K.post('/order/ajax/save_order.php', para, _onSaveOrderStep1Success, _onSaveOrderStep1Fail);
    }

    function _onSaveOrderStep1Success(data) {
        window.location.href = "/order/add_order.php?step=2&oid=" + data.oid;
    }

    function _onSaveOrderStep1Fail(data) {
        $('#_j_btn_save_order_step1').attr('disabled', false);
        alert(data.errmsg);
    }

    // 保存订单
    function _getProductsStr() {
        var products = [];
        $('._j_product').each(function () {
            var cb = $(this),
                pid = cb.data('pid'),
                num = parseInt(cb.find('input[name=num]').val()),
                note = cb.find('input[name=note]').val();
            if (K.isNumber(num)) {
                products.push(pid + ':' + num + ':' + note);
            }
        });
        return products.join(',');
    }

    function saveButton(flag) {
        $('#_j_btn_save_order_edit').attr('disabled', flag);
    }

    function onSaveOrderEdit(ev) {
        var para = {oid: $('input[name=oid]').val()};
        saveButton(true);
        K.post('/order/ajax/check_duplicate.php', para, _onCheckSucc);
    }

    function _onCheckSucc(data) {
        //console.log(data.duplicate_oid);
        if (parseInt(data.duplicate_oid) <= 0) {
            var para = _getOrderFormInfo();

            var para2 = {oid: para.oid, service: para.service, floor_num: para.floor_num, city: para.city, district: para.district, area: para.area, cmid: para.community_id, delivery_type: para.delivery_type};
            K.post('/order/ajax/cal_fee.php', para2, _onCalFeeSucc);
        } else {
            if (confirm("此订单可能和订单：" + data.duplicate_oid + "重复！！\n\n建议先确认后，再点击确定提交！！")) {
                var para = _getOrderFormInfo();

                var para2 = {oid: para.oid, service: para.service, floor_num: para.floor_num, city: para.city, district: para.district, area: para.area, cmid: para.community_id, delivery_type: para.delivery_type};
                K.post('/order/ajax/cal_fee.php', para2, _onCalFeeSucc);
            } else {
                saveButton(false);
            }
        }

    }

    function _onCalFeeSucc(data) {
        var para = _getOrderFormInfo();

        if (data.freight != para.freight) {
            if (!confirm("系统计算出来的运费是：" + data.freight + "\n" + "填写的运费是：" + para.freight + "\n\n" + "确定要保存吗？")) {
                saveButton(false);
                return false;
            }
        }

        if (data.carry_fee != para.customer_carriage) {
            if (!confirm("系统计算出来的搬运费是：" + data.carry_fee + "\n" + "填写的搬运费是：" + para.customer_carriage + "\n\n" + "确定要保存吗？")) {
                saveButton(false);
                return false;
            }
        }

        // 计算总优惠金额
        para.privilege = 0;
        para.privilege += $('input[name=privilege_special]').val() * 1;

        para.product_str = _getProductsStr();
        para.step = 3;
        var gift_products = '';
        var discount_products = '';
        $('input[type="checkbox"][name="gift_pid"]:checked').each(function() {
            var pid = $(this).val();
            var num = $(this).data('num');
            gift_products += pid+':'+num+';';
        });
        var error_status = false;
        $('input[type="checkbox"][name="special_price_pid"]:checked').each(function() {
            var pid = $(this).val();
            var num = Number($(this).parent().next().next().next().next().children("input:first-child").val());
            var max_num = Number($(this).parent().next().next().next().next().children("input:first-child").data('num'));
            if(num == 0 || max_num < num)
            {
                error_status = true;
                alert('特价商品PID:'+pid+' 购买数量错误');
                saveButton(false);
                return false;
            }
            discount_products += pid+':'+num+';';
        });
        if(error_status)
        {
            return false;
        }
        para.gift_products = gift_products;
        para.discount_products = discount_products;

        saveButton(true);
        K.post('/order/ajax/save_order.php', para, _onSaveOrderEditSuccess, _onSaveOrderEditFailed);
    }

    function _onSaveOrderEditSuccess(data) {
        alert('保存成功');

        window.onbeforeunload = function () {
        };
        window.location.href = '/order/order_detail.php?oid=' + data.oid;
    }

    function _onSaveOrderEditFailed(err) {
        alert(err.errmsg);
        saveButton(false);
    }

    // 确认订单状态
    function onChgOrderStep(ev) {
        ev.preventDefault();
        var tgt = $(ev.currentTarget),
            oid = tgt.closest('form').attr('data-oid'),
            next_step = tgt.attr('data-next_step'),
            para = {step: next_step, type: 'next_step', oid: oid};

        if (2 == next_step) {
            var today = $('#today').val();
            var deliveryDate = $('#delivery_date').val();
            var carryFee = parseInt($('#carry_fee').val());
            var freight = parseInt($('#freight').val());
            var curCarryFee = parseInt($('#cur_carry_fee').val());
            var curFreight = parseInt($('#cur_freight').val());

            var showOrderDeliveryTimes = $('.show_delivery_times').html();
            if (!confirm("请确认订单配送时间：\n\n" + showOrderDeliveryTimes)) {
                return false;
            }

            if (deliveryDate < today) {
                if (deliveryDate == '0000-00-00') {
                    alert('订单配送日期为空，请先编辑配送日期，然后再确认！');
                    return false;
                }
                if (!confirm('配送日期小于当前日期，确定要继续吗？')) {
                    return false;
                }
            }

            if (carryFee != curCarryFee) {
                carryFee /= 100;
                curCarryFee /= 100;

                if (!confirm("系统计算出来的搬运费是：" + carryFee + "\n" + "当前搬运费是：" + curCarryFee + "\n\n" + "确定要继续吗？")) {
                    return false;
                }
            }

            if (freight != curFreight) {
                freight /= 100;
                curFreight /= 100;

                if (!confirm("系统计算出来的运费是：" + freight + "\n" + "当前运费是：" + curFreight + "\n\n" + "确定要继续吗？")) {
                    return false;
                }
            }
        }

        if (5 == next_step) {
            if (tgt.attr('data-hadabnormalproducts') == '1') {
                alert('该订单存在：已经拣货但是被删除的商品，请查看删除列表，并和司机确认商品！！');
            }
            if (!confirm('确认已出库？')) {
                return;
            }
        }

        if (2 == next_step)
        {
            var chkPara = {oid: oid};
            K.post('/order/ajax/order_products_ischanged.php', chkPara, function(ret){
                if (ret.is_chg)
                {
                    var chgInfo = '商品id: 原价: 现价' + "\n" + ret.chg_info.join("\n");
                    if (!confirm('订单商品价格已调整，请联系客户是否继续确认订单：'+ "\n"+chgInfo)){
                        return;
                    }
                }
                $('._j_chg_order_step').attr('disabled', true);
                K.post('/order/ajax/set_order.php', para, _onChangeOrderStepSuccess);
                
            });
        }
        else
        {
            $(this).attr('disabled', true);
            K.post('/order/ajax/set_order.php', para, _onChangeOrderStepSuccess);
        }
    }

    function _onChangeOrderStepSuccess(data) {
        window.location.reload();
    }

    // 回退订单状态
    function onBackOrderStep(ev) {
        ev.preventDefault();
        var tgt = $(ev.currentTarget),
            oid = tgt.closest('form').data('oid'),
            para = {oid: oid, type: 'back'};

        var isInPicking = $('#is_in_picking').val();
        if (isInPicking) {
            if (!confirm("该订单库房兄弟已经拣货了，如非要修改请联系对应库管人员确认后再进行修改！\n\n如要继续修改，请点击“确定”。")) {
                return false;
            }
        }

        if (!confirm('确认要重新修改该订单？')) {
            return;
        }

        K.post('/order/ajax/set_order.php', para, _onBackOrderStepSuccess);
    }

    function _onBackOrderStepSuccess(data) {
        window.onbeforeunload = function () {
        };
        window.location.reload();
    }

    // 确认退货单状态
    function onChgRefundStep(ev) {
        ev.preventDefault();
        var tgt = $(ev.currentTarget),
            rid = $('input[name=rid]').val(),
            next_step = tgt.data('next_step'),
            refund_to_balance = $('input[name="refund_to_balance"]').is(':checked') ? 1 : 0,
            adjust = $('input[name="adjust"]').val(),
            optype = tgt.data('optype'),

            para = {next_step: next_step, rid: rid, refund_to_balance: refund_to_balance, adjust: adjust, optype: optype};

        var unStockinPids = [];

        if ($('.refund_products_area').length) {
            var pid;
            $('.refund_products_area').find('input[name=stock_shelves]').each(function () {
                if (!$(this).is(':checked')) {
                    pid = $(this).closest('._j_product').attr('data-pid');
                    unStockinPids.push(pid);
                }
            });

        }

        para.unstockin_pids = JSON.stringify(unStockinPids);

        if (optype == 'finance') {
            if (!confirm('确认退款？ 请检查【余额】是否正确！！')) {
                return false;
            }
        }

        $(this).attr('disabled', true);
        K.post('/order/ajax/change_refund_step.php', para, _onChgRefundStepSuccess);
    }

    function _onChgRefundStepSuccess(data) {
        alert('操作已成功');
        window.location.reload();
    }

    //退款单
    function _getRefundFormInfo() {
        var para = {
            rid: $('input[name=rid]').val(),
            oid: $('input[name=oid]').val(),
            wid: $('select[name=wid]').val(),
            adjust: $('input[name=adjust]').val(),
            note: $('textarea[name=note]').val()
        };
        return para;
    }

    function onSaveRefund(ev) {
        var para = _getRefundFormInfo();
        var products = [];

        if (K.trim(para.note).length <= 0) {
            alert('请先填写退货原因，以备后续处理');
            return;
        }
        if (!para.rid && !confirm("您是否确定将所选产品退货？")) {
            return;
        }

        if (!confirm("你选择的退货仓库是：" + para.wid + "号仓库")) {
            return;
        }

        $('._j_product').each(function () {
            var cb = $(this),
                pid = cb.data('pid'),
                num = parseInt(cb.find('input[name=num]').val()),
                price = parseFloat(cb.find('input[name=price]').val());
            if (K.isNumber(num)) {
                products.push(pid + ':' + num + ':' + price);
            }
        });

        $(this).attr('disabled', true);
        para.product_str = products.join(',');
        K.post('/order/ajax/save_refund.php', para, _onSaveRefundSuccess);
    }

    function _onSaveRefundSuccess(data) {
        if (data.st == 0) {
            window.location.href = '/order/edit_refund.php?rid=' + data.rid;
        } else if (data.st == -1) {
            alert('失败：退货商品数量大于订单商品数量！');
            $('#_j_btn_save_refund').attr('disabled', false);
            return;
        } else if (data.st == -2) {
            alert('失败：退货商品为空！');
            $('#_j_btn_save_refund').attr('disabled', false);
            return;
        }
    }

    function onSaveOrderNote(ev) {
        ev.preventDefault();
        var tgt = $(ev.currentTarget),
            oid = tgt.data('oid'),
            note = tgt.closest('._j_dialog').find('textarea[name=note]').val(),
            para = {oid: oid, note: note, type: 'note'};

        K.post('/order/ajax/set_order.php', para, _onSaveOrderNoteSuccess);
    }

    function _onSaveOrderNoteSuccess(data) {
        alert('操作已成功');
        window.location.reload();
    }

    function onSaveCustomerOrderNote(ev) {
        ev.preventDefault();
        var tgt = $(ev.currentTarget),
            oid = tgt.data('oid'),
            note = tgt.closest('._j_dialog').find('textarea[name=customer_note]').val(),
            para = {oid: oid, customer_note: note, type: 'customer_note'};

        K.post('/order/ajax/set_order.php', para, _onSaveOrderNoteSuccess);
    }

    function _onSaveCustomerOrderNoteSuccess(data) {
        window.location.reload();
    }

    //通知库房打印
    function onNoticePrint(ev) {
        ev.preventDefault();

        var tgt = $(ev.currentTarget),
            oid = tgt.data('oid'),
            para = {oid: oid};
        K.post('/order/ajax/set_order_print.php', para, _onNoticePrintSuccess);
    }

    //保存订单成功
    function _onNoticePrintSuccess(data) {
        alert('通知成功');
    }

    function onSaveOrderDriver(ev) {
        ev.preventDefault();
        var tgt = $(ev.currentTarget),
            dialog = tgt.closest('._j_dialog'),
            oid = tgt.data('oid'),
            driver_name = dialog.find('input[name=driver_name]').val(),
            driver_phone = dialog.find('input[name=driver_phone]').val(),
            driver_money = dialog.find('input[name=driver_money]').val(),
            carrier_name = dialog.find('input[name=carrier_name]').val(),
            carrier_phone = dialog.find('input[name=carrier_phone]').val(),
            carrier_money = dialog.find('input[name=carrier_money]').val();

        var pattern = /\d*/i;
        driver_phone = driver_phone.match(pattern)[0];

        var para = {
            oid: oid,
            driver_name: driver_name,
            driver_phone: driver_phone,
            driver_money: driver_money,
            carrier_name: carrier_name,
            carrier_phone: carrier_phone,
            carrier_money: carrier_money,
            type: 'driver'
        };

        K.post('/order/ajax/set_order.php', para, _onSaveOrderDriverSuccess);
    }

    function _onSaveOrderDriverSuccess(data) {
        alert('操作已成功');
        window.location.reload();
    }

    function useBalancePaid() {
        var st = $(this).is(':checked');
        var hPrice = $(this).closest('._j_dialog').find('input[name=price]');
        if (st) {
            var diffPrice = $(this).attr('data-willpay') - $(this).attr('data-balance');
            hPrice.val(diffPrice > 0 ? diffPrice / 100 : 0);
        } else {
            hPrice.val($(this).attr('data-willpay') / 100);
        }
    }

    function onSaveMoneyIn(ev) {
        ev.preventDefault();
        var tgt = $(ev.currentTarget),
            box = tgt.closest('._j_dialog'),
            price = box.find('input[name=price]').val(),
            note = box.find('textarea[name=note]').val(),
            payType = box.find('select[name=payment_type]').val(),
            moling = box.find('input[name=moling]').val(),
            useBalance = box.find('input[name=use_balance]').is(':checked'),
            discount = box.find('input[name=discount]').val(),
            badDebt = box.find('input[name=bad_debt]').val(),

            para = {
                cid: tgt.data('cid'),
                uid: tgt.data('uid'),
                type: tgt.data('type'),
                objid: tgt.data('objid'),
                wid: tgt.data('wid'),
                price: price,
                note: note,
                payment_type: payType,
                //paid: paid,
                moling: moling,
                discount: discount,
                use_balance: useBalance ? 1 : 0,
                bad_debt: badDebt,
                service_fee: box.find('input[name=service_fee]').val()
            };

        $(this).attr('disabled', true);
        K.post('/finance/ajax/save_money_in.php', para, _onSaveOrderFinanceSuccess);
    }

    function onAdjustMoneyIn(env) {
        var tgt = $(env.currentTarget),
            dialog = tgt.closest('._j_dialog');

        var adtype = dialog.find('input[name="adtype"]:checked').val(),
            price = dialog.find('input[name="price"]').val(),
            note = dialog.find('textarea[name="note"]').val(),
            para = {
                cid: tgt.data('cid'),
                type: tgt.data('type'),
                price: price,
                note: note,
                adtype: adtype,
                payment_type: dialog.find('select[name=payment_type]').val()
            };
        var st = _checkParam();
        if (st) {
            $(this).attr('disabled', true);
            K.post('/finance/ajax/save_money_in.php', para, _onSaveOrderFinanceSuccess);
        }

        function _checkParam() {
            var st = true;
            if (para.adtype == undefined) {
                alert('请选择 “类型”');
                st = false;
            }
            else if (para.note.length == 0) {
                alert('请填写 “备注”');
                st = false;
            }
            else if (para.price.length == 0) {
                alert('请填写 “数量”');
                st = false;
            }
            else if (isNaN(para.price)) {
                alert('“数量” 必须为数字');
                st = false;
            }

            return st;
        }
    }

    function onAdjustMoneyOut() {
        var dialog = $(this).closest('._j_dialog');

        var adtype = dialog.find('input[name="adtype"]:checked').val(),
            price = dialog.find('input[name="price"]').val(),
            note = dialog.find('textarea[name="note"]').val(),
            para = {
                sid: $(this).data('sid'),
                price: price,
                note: note,
                adtype: adtype,
            };
        var st = _checkParam();
        if (st) {
            $(this).attr('disabled', true);
            K.post('/finance/ajax/save_money_out.php', para, _onSaveOrderFinanceSuccess);
        }

        function _checkParam() {
            var st = true;
            if (para.adtype == undefined) {
                alert('请选择 “类型”');
                st = false;
            }
            else if (para.note.length == 0) {
                alert('请填写 “备注”');
                st = false;
            }
            else if (para.price.length == 0) {
                alert('请填写 “数量”');
                st = false;
            }
            else if (isNaN(para.price)) {
                alert('“数量” 必须为数字');
                st = false;
            }

            return st;
        }
    }

    // 财务预付和返现
    function financePayForMoneyOut() {
        var dialog = $(this).closest('._j_dialog');

        var prePay = dialog.find('input[name="pre_pay"]').val(),
            privilege = dialog.find('input[name="privilege"]').val(),
            note = dialog.find('textarea[name="note"]').val();
        paidSource = dialog.find('select[name=paid_source]').val();
        var para = {
            sid: $(this).data('sid'),
            paid_source: paidSource,
            pre_pay: prePay,
            privilege: privilege,
            note: note
        };

        st = 1;
        if (st) {
            $(this).attr('disabled', true);
            K.post('/finance/ajax/save_money_out.php', para, _onSaveOrderFinanceSuccess);
        }
    }

    function financeModifyOrder() {
        var dialog = $(this).closest('._j_dialog');
        var para = {
            oid: $(this).attr('data-oid'),
            privilege: dialog.find('input[name="privilege"]').val(),	//优惠
            note: dialog.find('textarea[name=note]').val()
        };

        if (para.note.length == 0) {
            alert('请填写调整原因！');
            return;
        }

        $(this).attr('disabled', true);
        K.post('/order/ajax/modify_by_finance.php', para, _onSaveOrderFinanceSuccess);
    }

    function operatorModifyOrder() {
        var dialog = $(this).closest('._j_dialog');
        var para = {
            oid: $(this).attr('data-oid'),
            freight: dialog.find('input[name="freight"]').val(),		//客户运费
            customer_carriage: dialog.find('input[name="customer_carriage"]').val(), //客服搬运费
            note: dialog.find('textarea[name=note]').val()
        };

        if (para.note.length == 0) {
            alert('请填写调整原因！');
            return;
        }

        $(this).attr('disabled', true);
        K.post('/order/ajax/modify_by_operator.php', para, _onSaveOrderFinanceSuccess);
    }

    function saleModifyOrderPreferential() {
        var dialog = $(this).closest('._j_dialog');
        var para = {
            oid: $(this).attr('data-oid'),
            suid: dialog.find('select[name="send_suid"]').val(),		//发放人
            price: dialog.find('input[name="price"]').val(),	//优惠
        };

        if (para.suid == 0) {
            alert('请选择发放人！');
            return;
        }
        if (para.price == '')
        {
            alert('请输入优惠金额');
            return;
        }

        $(this).attr('disabled', true);
        K.post('/order/ajax/save_order_sale_privilege.php', para, _onSaveOrderSalePreferentialSuccess, _onSaveOrderSalePreferentialFailed);
    }

    function _onSaveOrderSalePreferentialSuccess(data) {
        alert('销售优惠调整操作已成功！');
        window.location.reload();
    }

    function _onSaveOrderSalePreferentialFailed(data) {
        alert(data.errmsg);
        $('#_j_confirm_sale_preferential_amount').attr('disabled', false);
    }

    function _onSaveOrderFinanceSuccess(data) {
        alert('操作已成功');
        window.location.reload();
    }

    // 删除退款单商品（未入库状态）
    function deleteRefund() {
        var rid = $(this).attr('data-rid'),
            oid = $(this).attr('data-oid');

        var para = {rid: rid, oid: oid};

        if (!confirm('确认要删除该退款单？')) {
            return;
        }

        K.post('/order/ajax/delete_refund_order.php', para, function (ret) {
            alert(ret.st != 0 ? '操作已成功' : '操作失败！请联系管理员！');
            window.location.reload();
        });
        return;
    }

    // 驳回退款单商品（未审核状态）
    function rebutRefund() {
        var rid = $(this).attr('data-rid'),
            oid = $(this).attr('data-oid');

        var para = {rid: rid, oid: oid};

        if (!confirm('确认要驳回该退款单？')) {
            return;
        }

        K.post('/order/ajax/rebut_refund_order.php', para, function (ret) {
            alert(ret.st != 0 ? '操作已成功' : '操作失败！请联系管理员！');
            window.location.reload();
        });
        return;
    }

    // 搬运费
    function saveCarriage() {
        var dialog = $('#editCarrierModal');
        var para = {
            oid: $(this).attr('data-oid'),
            customer_carriage: parseInt(dialog.find('input[name=customer_carriage]').val()),
            carrier_money: parseInt(dialog.find('input[name=carrier_money]').val()),
            type: 'carriage'
        };

//        if (para.customer_carriage==0 && para.carrier_money==0){
//            alert('输入金额为空！'); return;
//        }

        K.post('/order/ajax/set_order.php', para, function () {
            alert('保存成功！');
            window.location.reload();
        });
    }

    function changeDeliveryType() {
        var deliveryType = parseInt($('._j_chg_delivery_type:checked').val());
        //1配送2自提
        switch (deliveryType) {
            case 1:
            case 3:
                $('#address_info').css('display', '');
                $('#address_info2').css('display', '');
                $('#addr_community').show();
                $('#addr_detail').show();
                break;
            case 2:
                $('#address_info').css('display', 'none');
                $('#address_info2').css('display', 'none');
                $('#addr_community').hide();
                $('#addr_detail').hide();
                break;
            default:
                //nothing
        }
    }

    // 司机，搬运工逻辑
    function showSelectDriverCarrier() {
        var role = $(this).attr('data-role'),
            roleName = '';
        var box = $('#addCoopworkerModal');
        if (role == 'carrier') {
            roleName = '搬运工';
            box.find('.driver_property').hide();
            box.find('.coopworker_role').val(2);
            box.find('.show_carrier_fee').show();
        } else {
            roleName = '司机';
            box.find('.driver_property').show();
            box.find('.coopworker_role').val(1);
            box.find('.show_carrier_fee').hide();
        }
        $('#show_coopworker_area').html('');
        box.find('.modal-title').html('选择' + roleName);
        box.modal();
        box.on('hidden.bs.modal', function () {
            window.location.reload();
        });
    }

    //商品库存明细
    function showOrderProductsWarehouse() {
        var box = $('#OrderProductsWarehouse');
        var para = {
            oid: $('#oid').val()
        };
        $("#OrderProductsWarehouse_body").html('正在加载中......');
        box.modal();
        K.post('/order/ajax/get_order_products_warehouse.php', para, function (ret) {
            $('#OrderProductsWarehouse_body').html(ret['html']);
        });
    }

    function addCoopworker(evt) {
        if ($(evt.target).hasClass('_j_search_driver_carrier')) {
            var pageStart = $(evt.target).attr('data-start') || 0;
            searchDriverCarrier(pageStart);
        } else if ($(evt.target).hasClass('_j_select_cooperworker')) {
            selectDriverCarrier($(evt.target));
        }

        return;
    }

    function searchDriverCarrier(pageStart) {
        var box = $('#addCoopworkerModal');
        var para = {
            oid: $('#addCoopworkerModal').attr('data-oid'),
            role: box.find('select[name=role]').val(),
            keyword: box.find('input[name=keyword]').val(),
            wid: box.find('select[name=wid]').val(),
            start: pageStart,
            ret_type: 'html'
        };
        if (para.role == 1) {
            para.car_model = box.find('select[name=car_model]').val();
        }

        K.post('/logistics/ajax/get_driver_carrier.php', para, function (ret) {
            $('#show_coopworker_area').html(ret['html']);
        });
    }

    function selectDriverCarrier(target) {
        var para = {
            oid: $('#addCoopworkerModal').attr('data-oid'),
            cuid: target.attr('data-cuid'),
            price: target.parent().find('input[name=price]').val(),
            role: target.attr('data-role'),
            user_type: target.attr('data-utype'),
            wid: $('#addCoopworkerModal').attr('data-wid')
        };

        if (para.oid.length = 0 || para.cuid.length == 0) {
            alert('系统错误，请联系管理员！');
            return;

        }

        var currTarget = $(this);
        K.post('/logistics/ajax/select_driver_carrier.php', para, function (ret) {
            if (ret.st == 1) {
                currTarget.hide();
                currTarget.parent().append('<span class="col-md-2">已选择<span>');
                alert('选择成功!');
                window.location.reload();
            }
        });
    }

    function showPaidForCoopworker() {
        var obj = $(this).closest('.coopworker_info');
        var price = obj.attr('data-price');

        if (price == "0" || price.length == 0) {
            alert('请先填写价钱，然后在支付！');
            return;
        }

        var dialog = $('#showPaidCoopworker');

        var cinfo = obj.find('.name').html() + ' ' +
            obj.find('.phone').html();
        dialog.find('.cinfo').html(cinfo);
        dialog.find('.price').html(price + ' 元');

        var cDialog = $('#_j_paid_for_coopworker');
        cDialog.attr('data-type', obj.attr('data-type'));
        cDialog.attr('data-cuid', obj.attr('data-cuid'));
        cDialog.attr('data-usertype', obj.attr('data-usertype'));
        cDialog.attr('data-price', price);

        $('#showPaidCoopworker').modal();
    }

    function payForCoopworker() {
        var para = {
            oid: $(this).attr('data-oid'),
            cuid: $(this).attr('data-cuid'),
            type: $(this).attr('data-type'),
            user_type: $(this).attr('data-usertype'),
            price: $(this).attr('data-price'),
            payment_type: $('#showPaidCoopworker').find('select[name=payment_type]').val(),
            exec_type: 'paid'
        };

        if (para.oid.length == 0 || para.cuid.length == 0 || para.type.length == 0) {
            alert('参数错误！');
            return;
        }
        if (para.price == "0" || para.price.length == 0) {
            alert('请先填写价钱，然后在支付！');
            return;
        }
        if (para.payment_type == "0" || para.payment_type.length == 0) {
            alert('请选择支付类型！');
            return;
        }

        $(this).attr('disabled', true);
        K.post('/logistics/ajax/exec_order_coopworker.php', para, function (ret) {
            if (ret.st == 1) {
                //alert('操作成功！');
                window.location.reload();
            }
        });
    }

    // coopworker 区域操作
    function execCoopworder(evt) {
        var target = $(evt.target);
        var para = {
            oid: target.closest('form').attr('data-oid'),
            cuid: target.closest('.coopworker_info').attr('data-cuid'),
            type: target.closest('.coopworker_info').attr('data-type'),
            user_type: target.closest('.coopworker_info').attr('data-usertype')
        };
        var st = 0;
        if (target.hasClass('_j_edit_coopworker')) {
            editCoopworker(target, para);
            return;
        } else if (target.hasClass('_j_del_coopworker')) {
            st = 1;
            para.exec_type = 'del';
            para.line_id = $('#line_id').attr('data-line-id');
            para.id = target.closest('.coopworker_info').attr('data-id');
        }

        if (st) {
            target.attr('disabled', true);
            K.post('/logistics/ajax/exec_order_coopworker.php', para, function (ret) {
                if (ret.st == 1) {
                    //alert('操作成功！');
                    window.location.reload();
                }
            });
        }
    }

	function editCoopworker(target, para) {
		var city_id = target.attr('data-city-id');
        var infoHandler = target.closest('.coopworker_info');
        if (city_id == 1310 || (city_id == 120 && para.type == 1)) {
            var box = $('#editCoopworkerPrice');
            var name = infoHandler.find('.name').html();
            var distance = $('#distance_fee_content').find('.order_distance').attr('data-distance');
            var car_model = infoHandler.attr('data-car-model-name');
            var price = infoHandler.find('.price').attr('data-price');
            var note = infoHandler.attr('data-money-note');
            if (para.type == 1) {
                var referral_price = infoHandler.attr('data-base-price')/100;
                var price_box = $('#edit_driver_price_area');
                price_box.css('display', 'block');
                price_box.find('.driver_name').html(name);
                price_box.find('.order_distance').html(distance);
                price_box.find('.car_model').html(car_model);
                price_box.find('.referral_price').html(referral_price);
                price_box.find('input[name=price]').attr('data-old-price', price);
                var end_price = price;
                if (price == 0) {
                    end_price = referral_price;
                }
                price_box.find('input[name=price]').val(end_price);
                price_box.find('input[name=price]').attr('data-old-price', price);
                price_box.find('textarea[name=note]').val(note);
            } else {
                var referral_price = infoHandler.attr('data-referral-price')/100;
                var price_box = $('#edit_carrier_price_area');
                price_box.css('display', 'block');
                price_box.find('.carrier_name').html(name);
                price_box.find('.order_distance').html(distance);
                price_box.find('.car_model').html(car_model);
                price_box.find('.referral_price').html(referral_price);
                price_box.find('input[name=price]').attr('data-old-price', price);
                if (price == 0) {
                    price = referral_price;
                }
                price_box.find('input[name=price]').val(price);
                price_box.find('textarea[name=note]').val(note);
            }
        } else {
            var box = $('#editCoopworker');
            var	driver_box = $('#edit_driver_part');
            var	carrier_box = $('#edit_carrier_part');
            var showHtml = '';
            var base_price = infoHandler.attr('data-base-price')/100;
            var decline_price = infoHandler.attr('data-decline-price');
            var times = infoHandler.attr('data-times');
            var max_carrier_fee = infoHandler.attr('data-max-carrier-fee');
            driver_box.find('#driver_times').attr('data-decline-price', decline_price);
            driver_box.find('#driver_times option:selected').attr('selected', false);
            driver_box.find('#driver_times').val(times);
            driver_box.find('#driver_times option[text=' + times +']').attr('selected', true);

            if (infoHandler.attr('data-other-price')) {
                var other_price_json = eval('(' + infoHandler.attr('data-other-price') + ')');
            }

            var money_note = infoHandler.attr('data-money-note');
            driver_box.find('.money_note').html(money_note);
            driver_box.find('input[name=trash_price]').val('');
            driver_box.find('input[name=second_ring_road_price]').val('');
            driver_box.find('input[name=reward_price]').val('');
            driver_box.find('input[name=fine_price]').val('');
            driver_box.find('input[name=other_price]').val('');
            carrier_box.find('input[name=reward_price]').val('');
            carrier_box.find('input[name=fine_price]').val('');
            carrier_box.find('input[name=other_price]').val('');
            carrier_box.find('input[name=base_carrier_price]').val('');
            carrier_box.find('#max_arrier_fee').attr('data-max-carrier-fee', max_carrier_fee);
            carrier_box.find('#max_arrier_fee').html(max_carrier_fee/100);
            carrier_box.find('.money_note').html(money_note);
            for (var one in other_price_json) {
                switch (one) {
                    case '1':
                        driver_box.find('input[name=trash_price]').val(other_price_json[one]/100);
                        break;
                    case '2':
                        driver_box.find('input[name=second_ring_road_price]').val(other_price_json[one]/100);
                        break;
                    case '3':
                        driver_box.find('input[name=reward_price]').val(other_price_json[one]/100);
                        carrier_box.find('input[name=reward_price]').val(other_price_json[one]/100);
                        break;
                    case '4':
                        driver_box.find('input[name=fine_price]').val(other_price_json[one]/100);
                        carrier_box.find('input[name=fine_price]').val(other_price_json[one]/100);
                        break;
                    case '5':
                        driver_box.find('input[name=other_price]').val(other_price_json[one]/100);
                        carrier_box.find('input[name=other_price]').val(other_price_json[one]/100);
                        break;
                }
            }
            if (base_price > 0) {
                driver_box.find('#driver_base_price').attr('data-base-price',base_price);
                driver_box.find('#driver_base_price').html(base_price);
                carrier_box.find('input[name=base_carrier_price]').val(base_price);
                carrier_box.find('input[name=base_carrier_price]').attr('data-base-price', base_price);
            } else {
                driver_box.find('#driver_base_price').attr('data-base-price',0);
                driver_box.find('#driver_base_price').html(0);
                carrier_box.find('input[name=base_carrier_price]').attr('data-base-price', 0);
            }

            var total_price = infoHandler.find('.price').attr('data-price');
            showHtml += '<span class="col-sm-3">' + infoHandler.find('.name').html() + '</span>'
                + '<span class="col-sm-3">' + infoHandler.find('.phone').html() + '</span>'
                + '<span class="col-sm-3 price" data-price=' + total_price + '>' + infoHandler.find('.price').html() + '</span>';
            if (para.type == 1) {
                showHtml += '<span class="count_driver_money"><a href="javascript: void(0);">计算应付</a></span>';
            } else if (para.type == 2) {
                showHtml += '<span class="count_carrier_money"><a href="javascript: void(0);">计算应付</a></span>';
            }
            box.find('.edit_coopworker_srcinfo').html(showHtml);

            if (para.type == 1) {
                var car_model_name = infoHandler.attr('data-car-model-name');
                box.find('.car_model_name').html(car_model_name);
                $('._j_save_modify_Coopworker').attr('data-type', para.type);
                $('#edit_carrier_part').css('display', 'none');
                $('#edit_driver_part').css('display', 'block');
            } else if(para.type == 2) {
                $('#edit_driver_part').css('display', 'none');
                $('#edit_carrier_part').css('display', 'block');
            }

            if ($(target).attr('data-st') == 'modify_paid') {
                $('.cfee_had_paid').show();
                para.modify_type = 'paid';
            } else {
                $('.cfee_had_paid').hide();
                para.modify_type = 'unpaid';
            }
        }
        if (city_id == 120 && para.type == 1)
        {
            $('#editCoopworker').attr('data-modify-info', JSON.stringify(para));
        }
        box.attr('data-modify-info', JSON.stringify(para));

        box.modal();
	}

	function saveModifyCoopworker() {
        var city_id = $(this).attr('data-city-id');
        if (city_id == 1310)
        {
            var para = JSON.parse($('#editCoopworkerPrice').attr('data-modify-info'));
        }
        else
        {
            var para = JSON.parse($('#editCoopworker').attr('data-modify-info'));
            if (city_id == 120 && para.type == 1)
            {
                para = JSON.parse($('#editCoopworkerPrice').attr('data-modify-info'));
            }
        }

        if (city_id == 1310 || (city_id == 120 && para.type == 1)) {
            para.line_info = $('#line_id').attr('data-line-info');
            if (para.type == 1) {
                var price_box = $('#edit_driver_price_area');
                para.exec_type = 'modify_driver_price_lf';
            } else {
                var price_box = $('#edit_carrier_price_area');
                para.exec_type = 'modify_carrier_price_lf';
            }
            para.price = price_box.find('input[name=price]').val();
            para.base_price = price_box.find('input[name=price]').val();
            para.money_note = price_box.find('textarea[name=note]').val();

            var referral_price = price_box.find('.referral_price').html();
            if (referral_price != para.price && para.money_note == '') {
                alert('录入费用与推荐费用不一致，请填写备注！');
                return;
            }
        } else {
            para.line_info = $('#line_id').attr('data-line-info');
            if (para.type == 1) {
                countTotalPrice();
                var driver_box = $('#edit_driver_part');
                para.base_price = $('#driver_base_price').attr('data-base-price');
                para.times = driver_box.find('select[name=times]').val();
                para.trash_price = driver_box.find('input[name=trash_price]').val();
                para.second_ring_road_price = driver_box.find('input[name=second_ring_road_price]').val();
                para.reward_price = driver_box.find('input[name=reward_price]').val();
                para.fine_price = driver_box.find('input[name=fine_price]').val();
                para.other_price = driver_box.find('input[name=other_price]').val();
                para.money_note = driver_box.find('.money_note').val();
                para.price = $('#editCoopworker').find('.price').attr('data-price');
                para.exec_type = 'modify_driver_price';
                para.source_oid = $('#line_id').attr('data-source-oid');

                if (!((para.trash_price == '' || K.isNumber(parseFloat(para.trash_price))) && (para.second_ring_road_price == '' || K.isNumber(parseFloat(para.second_ring_road_price)))
                    && (para.reward_price == '' || K.isNumber(parseFloat(para.reward_price))) && (para.fine_price == '' || K.isNumber(parseFloat(para.fine_price)))
                    && (para.other_price == '' || K.isNumber(parseFloat(para.other_price))))) {
                    alert('金额格式不正确！');
                    $('#editCoopworker').find('._j_save_modify_Coopworker').attr('disabled', false);
                    return;
                }
                if( (para.reward_price != '' || para.fine_price != '' || para.other_price != '') &&  para.money_note == '') {
                    alert('奖励、惩罚、其他车型必须填写备注！');
                    $('#editCoopworker').find('._j_save_modify_Coopworker').attr('disabled', false);
                    return;
                }
                if (parseFloat(para.trash_price) > 245) {
                    alert('拉垃圾金额最多不超过245！请重新填写金额');
                    $('#editCoopworker').find('._j_save_modify_Coopworker').attr('disabled', false);
                    return;
                }
                if (parseFloat(para.second_ring_road_price) > 20) {
                    alert('二环订单附加运费最多不超过20！请重新填写金额');
                    $('#editCoopworker').find('._j_save_modify_Coopworker').attr('disabled', false);
                    return;
                }
            } else if (para.type == 2) {
                countTotalPrice();
                var carrier_box = $('#edit_carrier_part');
                para.base_price = carrier_box.find('input[name=base_carrier_price]').val();
                para.reward_price = carrier_box.find('input[name=reward_price]').val();
                para.fine_price = carrier_box.find('input[name=fine_price]').val();
                para.other_price = carrier_box.find('input[name=other_price]').val();
                para.money_note = carrier_box.find('.money_note').val();
                para.price = $('#editCoopworker').find('.price').attr('data-price');
                para.exec_type = 'modify_driver_price';
                var old_base_price = carrier_box.find('input[name=base_carrier_price]').attr('data-base-price')*100;
                var max_carrier_fee = carrier_box.find('#max_arrier_fee').attr('data-max-carrier-fee');
                var base_price_list = $('.carrier-list').find('.coopworker_info');
                var total_base_price = 0;
                base_price_list.each(function () {
                    total_base_price += parseFloat($(this).attr('data-base-price'));
                });
                if (para.base_price > max_carrier_fee/100) {
                    alert('应得搬运费不得超过推荐上楼费！');
                    return;
                }
                if ((parseFloat(total_base_price) + parseFloat(para.base_price)*100 - parseFloat(old_base_price)) > parseFloat(max_carrier_fee)) {
                    alert('多个搬运工应得搬运费总和不能超过推荐上楼费！');
                    return;
                }
                if (!((para.base_price == '' || K.isNumber(parseFloat(para.base_price))) && (para.reward_price == '' || K.isNumber(parseFloat(para.reward_price)))
                    && (para.fine_price == '' || K.isNumber(parseFloat(para.fine_price))) && (para.other_price == '' || K.isNumber(parseFloat(para.other_price))))) {
                    alert('金额格式不正确！');
                    $('#editCoopworker').find('._j_save_modify_Coopworker').attr('disabled', false);
                    return;
                }
                if( (para.reward_price != '' || para.fine_price != '' || para.other_price != '') &&  para.money_note == '') {
                    alert('奖励、惩罚、其他费用必须填写备注！');
                    $('#editCoopworker').find('._j_save_modify_Coopworker').attr('disabled', false);
                    return;
                }
            }
        }

        $(this).attr('disabled', true);
        K.post('/logistics/ajax/exec_order_coopworker.php', para, function (ret) {
            if (ret.st == 1) {
                alert('保存成功！');
                window.location.reload();
            }
        }, function (ret) {
            alert(ret.errmsg);
            $('._j_save_modify_Coopworker').attr('disabled', false);
        });
    }

    function bindEvent() {
        $('a._j_order_select_product').unbind('click').bind('click', onOrderSelectProduct);
        $('._j_order_search_product').unbind('click').bind('click', onOrderSearchProduct);
        $('._j_order_search_product').closest('._j_form').find('input[name=keyword]').unbind('keydown').bind('keydown', onOrderSearchProductKeydown);
        $('#_j_btn_save_products').unbind('click').bind('click', onSaveProducts);
        $('#_j_btn_save_products2').unbind('click').bind('click', onSaveProducts);
    }

    // 修改订单的销售人员
    function saveEditSaler() {
        var para = {
            oid: $(this).attr('data-oid'),
            old_saler: $(this).attr('data-suid'),
            new_saler: $(this).closest('._j_dialog').find('select[name=modify_saler]').val()
        };

        if (typeof para.oid == "undefined") {
            alert('异常错误，不能修改！');
            return;
        }
        if (para.old_saler == para.new_saler) {
            alert('销售人员没有改变，不需要修改！');
            return;
        }

        K.post('/order/ajax/chg_saler.php', para, function () {
            window.location.reload();
        });
    }

    // 确认订单状态
    function onSetPartPaid(ev) {
        if (confirm('确定要将订单支付状态更改为“部分收款”吗？')) {
            ev.preventDefault();
            var tgt = $(ev.currentTarget),
                oid = tgt.closest('form').attr('data-oid'),
                para = {oid: oid};

            $(this).attr('disabled', true);
            K.post('/order/ajax/set_part_paid.php', para, _onSetPartPaidSuccess);
        }
    }

    function _onSetPartPaidSuccess(data) {
        window.location.reload();
    }

    // 确认订单状态
    function onRefundAndDelete(ev) {
        if (confirm('确定要删除该订单吗？\n删除后，已支付货款会退入用户余额。')) {
            ev.preventDefault();
            var tgt = $(ev.currentTarget),
                oid = tgt.closest('form').attr('data-oid'),
                para = {oid: oid};

            $(this).attr('disabled', true);
            K.post('/order/ajax/refund_and_delete.php', para, _onRefundAndDeleteSuccess);
        }
    }

    function _onRefundAndDeleteSuccess(data) {
        window.location.reload();
    }

    // 详情页删除订单
    function onOrderDelete(ev) {
        if (confirm('确定要删除该订单吗？')) {
            ev.preventDefault();
            var tgt = $(ev.currentTarget),
                oid = tgt.closest('form').attr('data-oid'),
                para = {oid: oid};

            $(this).attr('disabled', true);
            K.post('/order/ajax/delete_order.php', para, _onOrderDeleteSuccess);
        }
    }

    function _onOrderDeleteSuccess() {
        alert('操作已成功！');
        window.location.href = '/order/order_list.php';
    }

    //尽快送达
    function onServiceAsSoon() {
        if ($('#service_as_soon').is(':checked')) {
            var mydate = new Date();
            var hour = mydate.getHours();
            var my_day = '';
            if (hour > 18) {
                mydate.setDate(mydate.getDate() + 1);
                my_day = mydate.Format('yyyy-MM-dd');
                $('#select_delivery_date').val(my_day);
                $('#select_delivery_time').val(8);
                $('#select_delivery_time_end').val(10);
            }
            else if (hour < 8) {
                my_day = mydate.Format('yyyy-MM-dd');
                $('#select_delivery_date').val(my_day);
                $('#select_delivery_time').val(8);
                $('#select_delivery_time_end').val(10);
            }
            else {
                my_day = mydate.Format('yyyy-MM-dd');
                $('#select_delivery_date').val(my_day);
                $('#select_delivery_time').val(hour + 1);
                $('#select_delivery_time_end').val(hour + 3);
            }
            $('#select_delivery_date').attr('disabled', true);
            $('#select_delivery_time').attr('disabled', true);
            $('#select_delivery_time_end').attr('disabled', true);
        }
        else
		{
			$('#select_delivery_date').attr('disabled', false);
			$('#select_delivery_time').attr('disabled', false);
			$('#select_delivery_time_end').attr('disabled', false);
		}
    }

    function onChangeProductCity(ev) {
        var tgt = $(ev.currentTarget),
            oid = tgt.closest('form').attr('data-oid'),
            city_id = $('#change_product_city').attr('data-cid'),
            para = {oid: oid, city_id: city_id, model: 'check'};
        K.post('/order/ajax/change_order_city.php', para, _onChangeProductCitySuccess);
    }

    function _onChangeProductCitySuccess(data) {
        if (data.status == true) {
            onChangeProductCityDone();
        }
        else {
            var products = eval('(' + data.products + ')');
            var str_html = '';
            $.each(products, function (n, value) {
                $.each($("tr._j_product"), function (n2, value2) {
                    var pid = value2.getAttribute('data-pid');
                    if (pid == value.pid) {
                        value2.setAttribute('style', 'color:#FF0000; background-color:#CCCCCC;');
                    }
                });
                str_html = str_html + '<tr><td>' + value.pid + '</td><td>' + value.sid + '</td><td>' + value.pname + '</td><td>' + value.cate + '</td><td>' + value.num + '</td>';
            });
            $("#change_order_city_body").html(str_html);
            $("#change_order_city").modal();
        }
    }

    function onChangeProductCityDone() {
        var oid = $('#_j_btn_save_products').attr('data-oid'),
            city_id = $('#change_product_city').attr('data-cid'),
            para = {oid: oid, city_id: city_id, model: 'done'};
        K.post('/order/ajax/change_order_city.php', para, _onDoSuccess);
    }

    function _onDoSuccess() {
        window.location.reload();
    }

    function showTransferAmountInOrder() {
        var para = {
            oid: $(this).closest('form').attr('data-oid'),
            otype: 'show'
        };

        K.post('/order/ajax/transfer_amount_in_order.php', para, function (ret) {
            var dlgBody = $('#dlgTransferAmountInOrder').find('.modal-body');
            dlgBody.html(ret.data.html);

            if (ret.errno > 0) {
                $('#confirmTransferAmount').attr('disabled', 'true');
                dlgBody.find('.trans_note').hide();
            }

            $('#dlgTransferAmountInOrder').modal();
        });
    }

    function confirmTransferAmountInOrder() {
        var dlg = $('#dlgTransferAmountInOrder');
        var para = {
            oid: $(this).attr('data-oid'),
            note: dlg.find('textarea').val(),
            otype: 'trans'
        };

        if (para.note.length == 0) {
            alert('请填写备注！');
            return;
        }

        var transPrice = Math.abs(dlg.find('#trans_price').attr('data-price'));
        if (!confirm('转余额金额：' + transPrice + '元，请确认！')) {
            return;
        }

        $(this).attr('disabled', true);
        K.post('/order/ajax/transfer_amount_in_order.php', para, function (ret) {
            if (ret.errno == 0) {
                alert('转余额成功！' + ret.errmsg);
                window.location.reload();
            }
            else {
                alert(ret.errmsg);
                $('#confirmTransferAmount').attr('disabled', false);
            }
        });
    }

    function _onBalance2AmountSuccess(data) {
        window.location.reload();
    }

//	function changePickingGroup() {
//		var para = {
//			oid: $(this).closest('form').attr('data-oid'),
//			group: $(this).attr('data-group')
//		};
//
//		K.post('/order/ajax/chg_picking_group.php', para, function () {
//			//alert('修改成功!');
//			window.location.reload();
//		});
//	}

    //修改数量
    function changeProductNum(ev) {
        var pid = $(this).data('pid'),
            oid = $(this).data('oid'),
            num = $('#product_num_' + pid).val(),
            note = $('#product_note_' + pid).val(),
            para = {oid: oid, pid: pid, num: num, note: note};

        K.post('/order/ajax/change_order_product_num.php', para, _onChangeProductNumSuccess);
    }

    function _onChangeProductNumSuccess(data) {
        window.onbeforeunload = function () {
        };

        if (data.url) {
            window.location.href = data.url;
        } else {
            window.location.reload();
        }
    }

    function showNoPrint() {
        var deliveryDate = $('select[name=delivery_date]').val();

        if (!K.isEmpty(deliveryDate)) {
            window.location.href = $(this).attr('href') + '&delivery_date=' + deliveryDate;
            return false;
        }
    }

    function onSaveService() {
        var oid = $(this).data('oid'),
            service = $('#service').val(),
            floorNum = $('#floor_num').val(),
            para = {oid: oid, service: service, floor_num: floorNum};

        K.post('/order/ajax/save_service.php', para, _onSaveServiceSucc);
    }

    function _onSaveServiceSucc(data) {
        window.location.reload();
    }

    function guaranteedOrder() {
        var dlg = $('#guaranteedOrder');
        var para = {
            oid: $(this).attr('data-oid'),
            guaranteed: $(this).attr('data-guaranteed'),
            note: dlg.find('textarea[name=note]').val()
        };

        if (para.note.length == 0) {
            alert('请填写备注！');
            return false;
        }

        var notice = para.guaranteed != '1' ? '担保' : '取消担保';
        if (confirm('确认要' + notice + '该订单？')) {
            $(this).attr('disabled', true);
            K.post('/order/ajax/guaranteed_order.php', para, function () {
                alert('操作已成功！！');
                window.location.reload();
            })
        }
    }

    function refreshPickingProduct() {
        var para = {
            oid: $(this).closest('form').attr('data-oid'),
            pid: $(this).closest('.dialog').attr('data-pid')
        };

        K.post('/order/ajax/refresh_picking_product.php', para, function () {
            alert('刷新成功！');
            window.location.reload();
        });
    }

    // 空采(外采/临采)标记
    function markVnumFlag() {
        var para = {
            oid: $(this).closest('form').attr('data-oid'),
            pid: $(this).closest('.dialog').attr('data-pid'),
            flag: $(this).attr('data-flag')
        };

        if (!confirm('确定要标记为【外采】！')) {
            return false;
        }

        K.post('/order/ajax/mark_vnum_flag.php', para,
            function () {
                alert('操作已成功！');
                window.location.reload();
            },
            function (err) {
                alert(err.errmsg);
            }
        );
    }

    function calDistinceWithWarehouse() {
        var para = {
            community_id: $('input[name=community_id]').val()
        };

        K.post('/order/ajax/cal_warehouses_distance.php', para, calDistinceWithWarehouseSucc);
    }

    function calDistinceWithWarehouseSucc(data) {
        $('#show_warehouses_distance').css('display', '');

        var text = "";
        for (var i in data.data) {
            var name = data.data[i].name;
            var distance = data.data[i].distance;
            text += name + "：" + distance + "公里；";
        }

        $('#show_warehouses_distance_text').text(text);
    }

    function countTotalPrice() {
        var para = JSON.parse($('#editCoopworker').attr('data-modify-info'));
        if (para.type == 1) {
            var driver_box = $('#edit_driver_part');
            var base_price = $('#driver_base_price').attr('data-base-price') * 100;
            var times = $('select[name=times]').val();
            var para = {
                trash_price: driver_box.find('input[name=trash_price]').val(),
                second_ring_road_price: driver_box.find('input[name=second_ring_road_price]').val(),
                reward_price: driver_box.find('input[name=reward_price]').val(),
                fine_price: driver_box.find('input[name=fine_price]').val(),
                other_price: driver_box.find('input[name=other_price]').val(),
            };
            var other_price = 0;
            for (var item in para) {
                if (parseFloat(para[item]) > 0) {
                    if (item == 'fine_price') {
                        other_price = other_price - parseFloat(para[item]);
                    }
                    else {
                        other_price = other_price + parseFloat(para[item]);
                    }
                }
            }
            var second_times_decline_price = driver_box.find('#driver_times').attr('data-decline-price');
            if (parseInt(base_price) > 0) {
                var price = parseFloat(base_price) / 100 + (parseInt(times) - 1) * (parseFloat(base_price) / 100 - parseFloat(second_times_decline_price) / 100) + parseFloat(other_price);
            }
            else {
                var price = parseFloat(other_price);
            }

            if (parseFloat(price) < 0) {
                price = 0;
            }
        }
        else if (para.type == 2) {
            var carrier_box = $('#edit_carrier_part');

            var para = {
                base_carrier_price: carrier_box.find('input[name=base_carrier_price]').val(),
                reward_price: carrier_box.find('input[name=reward_price]').val(),
                fine_price: carrier_box.find('input[name=fine_price]').val(),
                other_price: carrier_box.find('input[name=other_price]').val(),
            };
            var price = 0;
            for (var item in para) {
                if (parseFloat(para[item]) > 0) {
                    if (item == 'fine_price') {
                        price = price - parseFloat(para[item]);
                    }
                    else {
                        price = price + parseFloat(para[item]);
                    }
                }
                if (parseFloat(price) < 0) {
                    price = 0;
                }
            }
        }

        $('#editCoopworker').find('.price').attr('data-price', price);
        $('#editCoopworker').find('.price').html('￥' + price + '元');
    }

    function showOccupiedByOrder() {
        var dom = $(this).closest('.dialog');
        var para = {
            pid: dom.attr('data-pid'),
            sid: dom.attr('data-sid'),
            oid: $(this).closest('form').attr('data-oid'),
            wid: $(this).closest('form').attr('data-wid'),
            title: dom.find('.p_title').attr('data-title')
        };

        K.post('/order/ajax/get_occupied_product_by_order.php', para, function (ret) {

            $('#show_occupied_products').find('.modal-body').html(ret.html);
            $('#show_occupied_products').modal();
        });

    }

    function clickFormArea(evt) {
        if ($(evt.target).hasClass('select_all_del_products')) {
            var list = $(this).find('input[name=select_bulk_del_products]');
            if ($(evt.target).is(':checked')) {
                list.each(function () {
                    $(this).prop('checked', true);
                });
            }
            else {
                list.each(function () {
                    $(this).prop('checked', false);
                });
            }

        }

        if ($(evt.target).hasClass('select_invert_del_products')) {
            var list = $(this).find('input[name=select_bulk_del_products]');
            list.each(function () {
                if ($(this).is(':checked')) {
                    $(this).prop('checked', false);
                }
                else {
                    $(this).prop('checked', true);
                }
            });
        }
    }

    function bulkDelOrderProducts() {
        var pid_list = [];
        var oid = $('form').attr('data-oid');
        var list = $('form').find('input[name=select_bulk_del_products]:checked');
        list.each(function () {
            pid_list.push($(this).closest('tr').attr('data-pid'));
        });

        var para = {
            oid: oid,
            pid_list: pid_list
        };

        if (confirm('确定要删除选中的商品吗？')) {
            $(this).attr('disabled', true);
            K.post('/order/ajax/delete_product.php', para, _onDeleteProductSuccess,
                function () {
                    $('#bulk_del_order_products').attr('disabled', false);
                }
            );
        }
    }

    function resetEditCoopworkerPriceModal(){
        $('#edit_carrier_price_area').css('display', 'none');
        $('#edit_driver_price_area').css('display', 'none');
        var price_box = $(this);
        price_box.find('.driver_name').html('');
        price_box.find('.car_model_name').html('');
        price_box.find('.order_distance').html(0);
        price_box.find('.base_price').html(0);
        $('#edit_carrier_price_area').find('input[name=price]').attr('data-old-price', 0);
        $('#edit_driver_price_area').find('input[name=price]').attr('data-old-price', 0);
    }

    main();

    changeDeliveryType();

    if (window.applicationCache) {
        $('#common-deliver-date').css('display', 'none');
    } else {
        $('#h5-deliver-date').css('display', 'none');
    }

    // 对Date的扩展，将 Date 转化为指定格式的String
    // 月(M)、日(d)、小时(h)、分(m)、秒(s)、季度(q) 可以用 1-2 个占位符，
    // 年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字)
    // 例子：
    // (new Date()).Format("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423
    // (new Date()).Format("yyyy-M-d h:m:s.S")      ==> 2006-7-2 8:9:4.18
    Date.prototype.Format = function (fmt) { //author: meizz
        var o = {
            "M+": this.getMonth() + 1, //月份
            "d+": this.getDate(), //日
            "h+": this.getHours(), //小时
            "m+": this.getMinutes(), //分
            "s+": this.getSeconds(), //秒
            "q+": Math.floor((this.getMonth() + 3) / 3), //季度
            "S": this.getMilliseconds() //毫秒
        };
        if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
        for (var k in o)
            if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
        return fmt;
    }

    function notLocalOrder() {
        if (confirm('确定要将该订单设置为非本地订单吗？')) {
            $(this).attr('disabled', true);
            var oid = $('form').attr('data-oid');
            var para = {oid: oid};
            K.post('/order/ajax/not_local_order.php', para,
                function () {
                    window.location.href = '/order/order_list.php';
                }
            );
        }
    }

    function selectCity4Order(){
        var para = {
            oid: $('#changeOrderCity').attr('data-oid'),
            city_id: $(this).attr('data-cityid'),
            model: 'show_product'
        };

        $('#changeOrderCity').find('.select_city_4_order').each(function(){
            $(this).css('border', '');
        });
        $(this).css('border', '3px solid red');
        $('#confirmChangeOrderCity').attr('data-chg_city', para.city_id);

        K.post('/order/ajax/change_order_city.php', para, function(ret){
            if (ret.errno == 0)
            {
                $('#changeOrderProductInfo').html(ret.data.html);
            }
            else
            {
                $('#changeOrderProductInfo').html('<p>'+ ret.errmsg +'</p>');
            }
        });
    }

    function confirmChangeOrderCity(){
        var para = {
            oid: $('#changeOrderCity').attr('data-oid'),
            city_id: $(this).attr('data-chg_city'),
            model: 'confirm_chg'
        };

        //   $(this).attr('disabled', true);
        K.post('/order/ajax/change_order_city.php', para,
            function(ret){
                if (ret.errno == 0){ 
                    alert('修改成功！');
                    window.location.reload();
                } else {
                    alert(ret.errmsg);
                    $('#confirmChangeOrderCity').attr('disabled', false);
                }
            },
            function(err){
                alert(err.errmsg);
                $('#confirmChangeOrderCity').attr('disabled', false);
            }
        );
    }

    function changeDeliverDateType() {
        var begin_date = $('#deliver_date_type_select').find('option:selected').attr('data-begin');
        var end_date = $('#deliver_date_type_select').find('option:selected').attr('data-end');
        $('#h5-deliver-date input[name=from_date]').val(begin_date);
        $('#h5-deliver-date input[name=end_date]').val(end_date);
    }
    
    function clearErrOccupied(){
        var param = {
            wid: $(this).attr('data-wid'),
            sid: $(this).attr('data-sid')
        };
        
        K.post('/warehouse/ajax/clear_err_occupied.php', param, 
            function(){
                alert('处理成功');
                window.location.reload();
            },
            function(err){
                alert(err.errmsg);
            }
        );
    }

})();