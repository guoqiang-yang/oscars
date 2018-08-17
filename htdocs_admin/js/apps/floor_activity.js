/**
 * Created by joker on 16/9/18.
 */
$(function () {

    var type = $('#s_type').val();

    if (K.isEmpty(type)) {
        //添加的js逻辑
        $('#out').hide();
        $('#in').hide();
        $('#common').hide();
        $('#type_container').hide();
    } else {
        //修改的js逻辑
        var position = $('#s_position').val();
        if (type == 1) {
            $('#in').hide();
            $('#least_price').hide();
            $('#type_container').hide();
            $('#notice').hide();
        } else if (type >= 2) {
            if (position == 1) {
                $('#in').hide();
                $('#notice').hide();
            } else if (position == 2) {
                $('#out').hide();
                $('#common').show();
                $('#notice').show();
            }
        }
    }
    $('#type').on('change', function () {
        $('#out').hide();
        $('#in').hide();
        $('#common').hide();
        $('#type_container').hide();
        $('#position').val('0');
        $('#notice').hide();
        var val = $('#type').val();
        var p_val = $('#position').val();
        if (val == 1) {
            $('#out').show();
            $('#common').show();
            $('#least_price').hide();
        } else if (val >= 2) {
            $('#type_container').show();

        }
        $('.form-horizontal')[0].reset();
        $(this).val(val);
        $('#position').val(p_val);
    })
    $('#position').on('change', function () {
        $('#out').hide();
        $('#in').hide();
        $('#common').hide();
        $('#notice').hide();
        var val = $('#position').val();
        var t_val = $('#type').val();
        if (val == 1) {
            $('#out').show();
            $('#common').show();
            $('#least_price').show();
            $('#type_container').show();
        } else if (val == 2) {
            $('#notice').show();
            $('#in').show();
            $('#common').show();
            $('#type_container').show();
        }
        $('.form-horizontal')[0].reset();
        $(this).val(val);
        $('#type').val(t_val);
    })
    //搜索商品
    $('#search').on('click', function () {
        var keyword = $('#search_product').val();
        var fid = $('#fid').val();
        var url = $('#search').attr('url');
        var type = $('#type').val();
        window.location.href = '/activity/search_product.php?keyword=' + keyword + '&fid=' + fid + '&url=' + url + '&type=' + type + '&position=2';
    });
    //搜索商品(回车键)
    $('#search_product').keydown(function (e) {
        if (e.keyCode == 13) {
            var keyword = $('#search_product').val();
            var fid = $('#fid').val();
            var url = $('#search').attr('url');
            var type = $('#type').val();
            window.location.href = '/activity/search_product.php?keyword=' + keyword + '&fid=' + fid + '&type=' + type + '&position=2' + '&url=' + url;
        }
    })
    //保存活动
    $('#btn_save_floor_activity').on('click', function () {
        var para = {
            fid: $('#fid').val(),
            type: $('#floor_type').val(),
            sort: $('#sort').val(),
        }
        if (para.type == 0) {
            alert('请选择楼层类型');
            return false;
        }
        var city = '';
        $("input:checked[name='city']").each(function () {
            city += $(this).val() + ',';
        })
        para.city = city;
        if (para.city == '') {
            alert('请选择活动城市');
            return false;
        }
        if (para.sort == '') {
            alert('请填写楼层排序');
            return false;
        }
        K.post('/activity/ajax/save_floor_activity.php', para, function () {
            alert('保存成功');
            window.location.href = '/activity/floor_activity_list.php';
        })
    })
    //保存商品
    $('#btn_save_floor_sale').on('click', function () {
        var position = $('#position').val();
        var type = $('#type').val();
        var fid = $('#fid').val();

        if (type == 1) {
            para = {
                fid: fid,
                position: 1,
                name: $('#name').val(),
                url: $('#url').val(),
                detail: $('#detail').val(),
                start_time: $('#start_time').val(),
                end_time: $('#end_time').val(),
                sort: $('#sort').val(),
                pic_url: $('#_j_upload_view_img').attr('src'),
                activity_type: $("input[name='activity_type']:checked").val(),
                commodity_sid: $("input[name='commodity_sid']").val(),
            }


            if(para.activity_type == 1){
                if (para.url == '') {
                    alert('请填写链接地址');
                    return false;
                }
                if (para.commodity_sid != '') {
                    alert('您已选择文章类,请勿填写商品SID');
                    return false;
                }

            }else if(para.activity_type == 2) {
                if (para.commodity_sid == '') {
                    alert('请填写商品SID');
                    return false;
                }
                if (para.url != '') {
                    alert('您已选择落地页类,请勿填写链接地址');
                    return false;
                }
            }

            if (para.name == '') {
                alert('请填写活动名称');
                return false;
            }
            if (para.pic_url == '/i/nopic100.jpg') {
                alert('请上传图片');
                return false;
            }
            if (para.sort == '') {
                alert('请输入排序');
                return false;
            }
            if (para.start_time == '') {

                alert('请填写活动开始时间');
                return false;
            }
            if (para.end_time == '') {
                alert('请填写活动结束时间');
                return false;
            }
            if (para.start_time >= para.end_time) {
                alert('活动开始时间不能大于结束时间');
                return false;
            }
        } else if (type >= 2) {
            if (position == 1) {
                para = {
                    fid: fid,
                    position: 1,
                    name: $('#name').val(),
                    url: $('#url').val(),
                    detail: $('#detail').val(),
                    start_time: $('#start_time').val(),
                    end_time: $('#end_time').val(),
                }
                var price = '';
                $("select[name='least_price']").each(function () {
                    price += $(this).val();
                })
                para.price = price;
                if (para.name == '') {
                    alert('请填写活动名称');
                    return false;
                }
                if (para.price / 1 == 0) {
                    alert('请填写活动商品最低价');
                    return false;
                }
                if (para.url == '') {
                    alert('请填写活动链接地址');
                    return false;
                }
                if (para.pic_url == '/i/nopic100.jpg') {
                    alert('请上传图片');
                    return false;
                }
                if (para.start_time == '') {
                    alert('请填写活动开始时间');
                    return false;
                }
                if (para.end_time == '') {
                    alert('请填写活动结束时间');
                    return false;
                }
                if (para.start_time >= para.end_time) {
                    alert('活动开始时间不能大于结束时间');
                    return false;
                }
            } else if (position == 2) {
                para = {
                    fid: fid,
                    pid: $('#pid').val(),
                    position: 2,
                    name: $('#pro_name').html(),
                    mark: $("input:radio[name='mark']:checked").val(),
                    sale_num: $('#sale_num').val(),
                    limit_count: $('#limit_num').val(),
                    start_time: $('#start_time').val(),
                    end_time: $('#end_time').val(),
                    pic_url: $('#_j_upload_view_img').attr('src'),
                }
                var price = '';
                $("select[name='price']").each(function () {
                    price += $(this).val();
                })
                para.price = price;
                if (para.pic_url == '/i/nopic100.jpg') {
                    para.pic_url = $('#pic_url').attr('src');
                }

                if (para.name == '') {
                    alert('请填写活动名称');
                    return false;
                }
                if (para.price / 1 == 0) {
                    alert('请填写商品活动价');
                    return false;
                }
                if (K.isEmpty(para.mark)) {
                    alert('请选择活动标识');
                    return false;
                }
                if (para.start_time == '') {
                    alert('请填写活动开始时间');
                    return false;
                }
                if (para.end_time == '') {
                    alert('请填写活动结束时间');
                    return false;
                }
                if (para.start_time >= para.end_time) {
                    alert('活动开始时间不能大于结束时间');
                    return false;
                }
            } else {
                alert('请选择活动落地页类型');
                return false;
            }
        } else {
            alert('请选择活动位置');
            return false;
        }
        para.sort = $('#sort').val()
        if (para.sort == '') {
            alert('请填写商品排序');
            return false;
        }
        para.type = type;
        para.sid = $('#sid').val();
        K.post('/activity/ajax/save_floor_sale.php', para, function () {
            alert('保存成功');
            window.location.href = '/activity/floor_sale_list.php?fid=' + para.fid;
        })
    })
    //楼层活动上线与下线
    $('.floor_action').on('click', function () {
        var method = $(this).attr('method');
        var fid = $(this).attr('data-id');
        var type = '#data-type-' + fid
        var city = '#data-city-' + fid
        var para = {
            fid: fid,
            method: method,
            data_city: $(city).val(),
            data_type: $(type).val()
        }
        if (method == 'down') {
            if (confirm('你确定将此活动下线吗？')) {
                K.post('/activity/ajax/save_floor_activity.php', para, function () {
                    alert('操作成功');
                    window.location.reload();
                })
            }

        } else if (method == 'up') {
            if (confirm('你确定将此活动上线吗？')) {
                K.post('/activity/ajax/save_floor_activity.php', para, function () {
                    alert('操作成功');
                    window.location.reload();
                })
            }
        }
    })
    //商品的上线与下线
    $('.sale_action').on('click', function () {
        var method = $(this).attr('method');
        var sid = $(this).attr('data-id');
        var para = {
            sid: sid,
            fid: $('#fid').val(),
            method: method,
            type: $(this).attr('data-type'),
            start_time: $(this).attr('start_time'),
            end_time: $(this).attr('end_time')
        }
        if (method == 'down') {
            if (confirm('你确定将此商品下架吗？')) {
                K.post('/activity/ajax/save_floor_sale.php', para, function () {
                    alert('操作成功');
                    window.location.reload();
                })
            }

        } else if (method == 'up') {
            if (confirm('你确定将此商品上架吗？')) {
                K.post('/activity/ajax/save_floor_sale.php', para, function () {
                    alert('操作成功');
                    window.location.reload();
                })
            }
        }
    })
    //商品的排序的更改
    $('.sale_sort').on('click', function () {

        var id = $(this).attr('data-id');
        var sort_id = '#data-' + id;
        var para = {
            sid: $(this).attr('data-id'),
            s_sort: $(sort_id).val()
        }
        K.post('/activity/ajax/save_floor_sale.php', para, function () {
            alert('操作成功');
            window.location.reload();
        })
    })
    //楼层活动排序的更改
    $('.floor_sort').on('click', function () {
        var id = $(this).attr('data-id');
        var sort_id = '#data-' + id;
        var para = {
            fid: $(this).attr('data-id'),
            f_sort: $(sort_id).val()
        }
        K.post('/activity/ajax/save_floor_activity.php', para, function () {
            alert('操作成功');
            window.location.reload();
        })
    })
    //活动排序的更改
    $('.activity_sort').on('click', function () {
        var id = $(this).attr('data-id');
        var sort_id = '#data-' + id;
        var para = {
            id: $(this).attr('data-id'),
            sort: $(sort_id).val()
        }
        K.post('/activity/ajax/save_activity_flash.php', para, function () {
            alert('操作成功');
            window.location.reload();
        })
    })
    $('.delete_btn').bind('click', function() {
        if (confirm('确定要删除该活动吗？')) {
            var id = $(this).data('id');
            var para = {id: id};
            K.post('/activity/ajax/delete_floor_activity.php', para, function () {
                alert('操作成功');
                window.location.reload();
            })
        }
    });
    $("input[name='activity_type']").click(function(){

        var activity_type = $("input[name='activity_type']:checked").val();

        if(activity_type == 1){
            $('.article_link').show();
            $('.commodity_sid').hide();
        }else{
            $('.article_link').hide();
            $('.commodity_sid').show();
        }
    })

})
//全选和反选功能
function checkAll(obj) {
    $("#box input[type='checkbox']").prop('checked', $(obj).prop('checked'));
}
