/**
 * Created by joker on 16/9/12.
 */
$(function () {
    //搜索商品
    $('#search').on('click', function () {
        var keyword = $('.form-control').val();
        var fid = $('#fid').val();
        var url = $('#search_url').val();
        var type = $('#type').val();
        var position = $('#position').val();
        window.location.href='/activity/search_product.php?keyword='+keyword+'&fid='+fid+'&url='+url+'&type='+type+'&position='+position;
    });
    //搜索商品(回车键)
    $('#search_product').keydown(function(e) {
        if (e.keyCode == 13) {
            var keyword = $('.form-control').val();
            var fid = $('#fid').val();
            var url = $('#search_url').val();
            var type = $('#type').val();
            var position = $('#position').val();
            window.location.href='/activity/search_product.php?keyword='+keyword+'&fid='+fid+'&url='+url+'&type='+type+'&position='+position;
        }
    })
    //保存活动
    $('#btn_save_activity').on('click', function () {
        var para = {
            id: $('#id').val(),
            name: $('#activity_name').val(),
            type: $('#activity_type').val(),
            platform: $('#platform').val(),
            city: $('city').val(),
            rule: $('#rule').val(),
            start_time: $('#activity_start_time').val(),
            end_time: $('#activity_end_time').val(),
        }
        if (para.name == '') {
            alert('请填写活动名称');
            return false;
        }
        if (para.type == 0) {
            alert('请选择活动类型');
            return false;
        }
        if (para.platform == 0) {
            alert('请选择活动平台');
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
        if (para.rule == '') {
            alert('请填写活动规则');
            return false;
        }
        if (K.isEmpty(para.start_time)) {
            alert('请填写活动开始时间');
            return false;
        }
        if (K.isEmpty(para.end_time)) {
            alert('请填写活动结束时间');
            return false;
        }
        K.post('/activity/ajax/save_activity_flash.php', para, function () {
            alert('保存成功');
            window.location.href='/activity/flash_activity_list.php';
        })
    })
    //保存商品
    $('#btn_save_flash').on('click', function () {
        var para = {
            id:$('#id').val(),
            pid: $('#pid').val(),
            aid: $('#aid').val(),
            fid: $('#fid').val(),
            cover: $('#_j_upload_view_img').attr('src'),
            sale_num: $('#sale_num').val(),
            sort: $('#sort').val(),
            limit_num: $('#limit_num').val(),
            start_time: $('#start_time').val(),
            end_time: $('#end_time').val(),
        }
        if (para.limit_num == '') {
            para.limit_num = 0;
        }
        if (para.cover == '/i/nopic100.jpg') {
            para.cover = $('#pic_url').attr('src');
        }
        var price = '';
        $("select[name='price']").each(function () {
            price += $(this).val();
        })
        para.price = price;
        if (para.price/1 == 0) {
            alert('请填写商品活动价格');
            return false;
        }
        if (para.sort == '') {
            alert('请填写商品排序');
            return false;
        }
        if (K.isEmpty(para.start_time)) {
            alert('请填写活动开始时间');
            return false;
        }
        if (K.isEmpty(para.end_time)) {
            alert('请填写活动结束时间');
            return false;
        }
        K.post('/activity/ajax/save_flash_sale.php', para, function () {
            alert('保存成功');
            window.location.href='/activity/flash_sale_list.php?fid='+para.fid;
        })
    })
    //商品下架与上架
    $('.action').on('click', function () {
        var method = $(this).attr('method');
        var para = {
            id:$(this).attr('data-id'),
            method:method,
        }
        if (method == 'down') {
            if (confirm('你确定将此商品下架吗？')) {
                K.post('/activity/ajax/save_flash_sale.php', para, function () {
                    alert('操作成功');
                    window.location.reload();
                })
            }
        }else if (method == 'up') {
            if (confirm('你确定将此商品上架吗？')) {
                K.post('/activity/ajax/save_flash_sale.php', para, function () {
                    alert('操作成功');
                    window.location.reload();
                })
            }
        }
    })
    //活动上线与下线
    $('.activity_action').on('click', function () {
        var method = $(this).attr('method');
        var para = {
            id:$(this).attr('data-id'),
            method:method,
        }
        if (method == 'down') {
            if (confirm('你确定将此活动下线吗？')) {
                K.post('/activity/ajax/save_activity_flash.php', para, function () {
                    alert('操作成功');
                    window.location.reload();
                })
            }

        }else if (method == 'up') {
            if (confirm('你确定将此活动上线吗？')) {
                K.post('/activity/ajax/save_activity_flash.php', para, function () {
                    alert('操作成功');
                    window.location.reload();
                })
            }
        }
    });
    //商品的排序的更改
    $('.sale_sort').on('click', function () {

        var id = $(this).attr('data-id');
        var sort_id = '#data-'+id;
        var para = {
            id:$(this).attr('data-id'),
            s_sort:$(sort_id).val()
        }
        K.post('/activity/ajax/save_flash_sale.php', para, function () {
            alert('操作成功');
            window.location.reload();
        })
    });
    //活动排序的更改
    $('.activity_sort').on('click', function () {
        var id = $(this).attr('data-id');
        var sort_id = '#data-'+id;
        var para = {
            id:$(this).attr('data-id'),
            sort:$(sort_id).val()
        }
        K.post('/activity/ajax/save_activity_flash.php', para, function () {
            alert('操作成功');
            window.location.reload();
        })
    });
    //活动排序的更改
    $('.delete_btn').bind('click', function () {
        if (confirm('确定要删除该活动吗？')) {
            var id = $(this).data('id');
            var para = {id: id};
            K.post('/activity/ajax/delete_flash_activity.php', para, function () {
                alert('操作成功');
                window.location.reload();
            })
        }
    });

})
//全选和反选功能
function checkAll(obj){
    $("#box input[type='checkbox']").prop('checked', $(obj).prop('checked'));
}