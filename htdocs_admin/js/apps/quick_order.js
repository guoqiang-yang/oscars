/**
 * Created by joker on 16/10/19.
 */
$(function () {
    $('.content').on('click',function () {
        var src = $(this).attr('src');
        /*layer.open({
            type: 3,
            title: false,
            closeBtn: 0,
            area: '516px',
            skin: 'layui-layer-nobg', //没有背景色
            shadeClose: true,
            content: '<img src="'+src+'" style="width:500px" class="img">',
        });*/
        $('#img').attr('src', src);
    })
    $('.ensure').on('click',function () {
        var oid = $(this).attr('data_id');
        var status = $(this).attr('data_ensure');
        if (status == 1){
            return;
        }
        var para = {
            method:'ensure',
            oid:oid,
        }
        if (confirm('您是否已与该客户确认该订单？')) {
            K.post('/order/ajax/ensure_quick_order.php', para, function () {
                alert('操作成功');
                window.location.reload();
            })
        }
    })
    $('#img').on('click' ,function () {
        var step = $(this).attr('step')
        if (step == 1) {
            $(this).animate({rotate: '90'}, 1000);
            $(this).attr('step',2);
        }else if (step == 2) {
            $(this).animate({rotate: '180'}, 1000);
            $(this).attr('step',3);
        }else if (step == 3) {
            $(this).animate({rotate: '270'}, 1000);
            $(this).attr('step',4);
        }else if (step == 4) {
            $(this).animate({rotate: '360'}, 1000);
            $(this).attr('step',1);
        }

    })

})

