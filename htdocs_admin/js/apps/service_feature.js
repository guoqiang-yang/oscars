/**
 * Created by joker on 16/11/3.
 */
$(function ()
{
    $('#btn_save_service_feature').on('click', function (){

        var city = '';
        $("input:checked[name='city']").each(function () {
            city += $(this).val() + ',';
        })

        var para = {
            sid : $('#sid').val(),
            feature1 : $('#feature1').val(),
            feature2 : $('#feature2').val(),
            feature3 : $('#feature3').val(),
            sort : $('#sort').val(),
            start_time : $('#start_time').val(),
            end_time : $('#end_time').val(),
            city : city,
        }
        if (para.feature1 == '') {
            alert('请输入特点1');
            return false;
        }if (para.feature2 == '') {
            alert('请输入特点2');
            return false;
        }if (para.feature3 == '') {
            alert('请输入特点3');
            return false;
        }if (para.city == '') {
            alert('请选择活动城市');
            return false;
        }if (para.start_time== '') {
            alert('请输入开始时间');
            return false;
        }if (para.end_time== '') {
            alert('请输入结束时间');
            return false;
        }
        if (para.start_time > para.end_time) {
            alert('开始时间不能小于结束时间');
            return false;
        }

        K.post('/activity/ajax/save_service_feature.php', para, function () {
            alert('保存成功');
            window.location.href='/activity/service_feature_list.php';
        })
    })
    //上线与下线
    $('.shortcut_action').on('click', function () {
        var method = $(this).attr('data-method');
        var para = {
            sid:$(this).attr('data-id'),
            method:method,
        }
        if (method == 'down') {
            if (confirm('你确定下线吗？')) {
                K.post('/activity/ajax/save_service_feature.php', para, function () {
                    alert('操作成功');
                    window.location.reload();
                })
            }

        }else if (method == 'up') {
            if (confirm('你确定上线吗？')) {
                K.post('/activity/ajax/save_service_feature.php', para, function () {
                    alert('操作成功');
                    window.location.reload();
                })
            }
        }
    })
    //排序的更改
    $('.shortcut_sort').on('click', function () {

        var id = $(this).attr('data-id');
        var sort_id = '#data-'+id;
        var para = {
            sid:$(this).attr('data-id'),
            s_sort:$(sort_id).val()
        }
        K.post('/activity/ajax/save_service_feature.php', para, function () {
            alert('操作成功');
            window.location.reload();
        })
    })
})
//全选和反选逻辑
function checkAll(obj) {
    $("#box input[type='checkbox']").prop('checked', $(obj).prop('checked'));
}
