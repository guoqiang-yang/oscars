/**
 * Created by joker on 16/10/19.
 */
$(function () {
    $('.ensure').on('click',function () {
        var fid = $(this).attr('data_id');
        var status = $(this).attr('data_ensure');
        if (status == 1){
            return;
        }
        $('#send').attr('data_fid', fid);
        $('#send').attr('data_ensure', status);


    })
    $('#send').on('click', function () {
        var fid = $(this).attr('data_fid');
        var status = $(this).attr('data_ensure');
        var content = $('#solve').val();
        var para = {
            fid:fid,
            status:status,
            method:'ensure',
            solve:content,
        }
        K.post('/crm2/ajax/ensure_customer_fb.php', para, function () {
            alert('操作成功');
            window.location.reload();
        })
    })
    $('.showAll').on('click',function () {
        var content = $(this).attr('data_all');
        $('#real_content').html(content);
    })
})
