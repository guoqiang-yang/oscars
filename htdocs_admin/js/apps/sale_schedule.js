(function () {

    function main() {
        $('#_js_add_sale_schedule').click(_onAddSaleSchedule);
        $('#_js_edit_sale_schedule').click(_onEditSaleSchedule);
        $('.addSaleSchedule').click(_onShowAddSaleSchedule);
        $('.editSaleSchedule').click(_onShowEditSaleSchedule);
        switch ($('#sale_schedule_navbar').attr('data-type')) {
            case '1':
                $($('#sale_schedule_navbar li')[1]).attr('class','active');
                break;
            case '2':
                $($('#sale_schedule_navbar li')[2]).attr('class','active');
                break;
            default:
                $($('#sale_schedule_navbar li')[0]).attr('class','active');
                break;
        }
        $('#TextArea1').keyup(words_deal);
        words_deal();
    }

    function _onShowAddSaleSchedule(ev) {
        var cid = $(this).data('cid');
        $('#addSaleScheduleModal input[name=schedule_cid]').val(cid);
        $('#addSaleScheduleModal').modal('show');
    }

    function _onShowEditSaleSchedule(ev) {
        $('#editSaleScheduleModal input[name=schedule_id]').val($(this).data('id'));
        $('#editSaleScheduleModal input[name=schedule_cid]').val($(this).data('cid'));
        $('#editSaleScheduleModal input[name=schedule_time]').val($(this).data('time'));
        $('#editSaleScheduleModal select[name=remind_tag]').val($(this).data('tag'));
        $('#editSaleScheduleModal textarea[name=schedule_content]').val($(this).data('content'));
        $('#editSaleScheduleModal').modal('show');
    }

    // 添加日程
    function _onAddSaleSchedule(ev) {

        var para = {
            cid: $('#addSaleScheduleModal input[name=schedule_cid]').val(),
            schedule_time: $('#addSaleScheduleModal input[name=schedule_time]').val(),
            remind_tag: $('#addSaleScheduleModal select[name=remind_tag]').val(),
            content: $('#addSaleScheduleModal textarea[name=schedule_content]').val(),
        };

        if(para.schedule_time == '')
        {
            alert('开始时间必填！');
            return false;
        }

        if (para.content == ''){
            alert('日程内容必填！');
            return false;
        }

        $(this).attr('disabled', true);
        K.post('/crm2/ajax/save_sale_schedule.php', para, _onSaveSaleScheduleSuccess, _onSaveSaleScheduleFail);
    }

    // 编辑日程
    function _onEditSaleSchedule(ev) {

        var para = {
            id: $('#editSaleScheduleModal input[name=schedule_id]').val(),
            cid: $('#editSaleScheduleModal input[name=schedule_cid]').val(),
            schedule_time: $('#editSaleScheduleModal input[name=schedule_time]').val(),
            remind_tag: $('#editSaleScheduleModal select[name=remind_tag]').val(),
            content: $('#editSaleScheduleModal textarea[name=schedule_content]').val(),
        };

        if(para.schedule_time == '')
        {
            alert('开始时间必填！');
            return false;
        }

        if (para.content == ''){
            alert('日程内容必填！');
            return false;
        }

        $(this).attr('disabled', true);
        K.post('/crm2/ajax/save_sale_schedule.php', para, _onSaveSaleScheduleSuccess, _onSaveSaleScheduleFail);
    }

    function _onSaveSaleScheduleSuccess(data)
    {
        alert('保存成功');
        window.location.href='/crm2/sale_schedule_list.php';
    }

    function _onSaveSaleScheduleFail(data)
    {
        alert(data.errmsg);
        $('#_js_add_sale_schedule').attr('disabled', false);
        $('#_js_edit_sale_schedule').attr('disabled', false);
    }

    function words_deal()
    {
        var curLength=$("#TextArea1").val().length;
        if(curLength>100)
        {
            var num=$("#TextArea1").val().substr(0,100);
            $("#TextArea1").val(num);
            $("#textCount").text(0);
            alert("超过字数限制，多出的字将被截断！" );
        }
        else
        {
            $("#textCount").text(100-$("#TextArea1").val().length);
        }
    }

    main();

})();