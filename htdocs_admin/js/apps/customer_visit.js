(function () {

    function main() {
        $('#_j_save_customer_visit').click(_onSaveCustomerVisit);
        $('#TextArea1').keyup(words_deal);
        switch ($('#customer_visit_navbar').attr('data-type')) {
            case '1':
                $($('#customer_visit_navbar li')[1]).attr('class','active');
                break;
            case '2':
                $($('#customer_visit_navbar li')[2]).attr('class','active');
                break;
            default:
                $($('#customer_visit_navbar li')[0]).attr('class','active');
                break;
        }
        words_deal();
    }

    // 添加拜访
    function _onSaveCustomerVisit(ev) {

        var para = {
            id: $('input[name=id]').val(),
            cid: $('input[name=cid]').val(),
            visit_time: $('input[name=visit_time]').val(),
            visit_type: $('select[name=visit_type]').val(),
            address: $('input[name=address]').val(),
            content: $('textarea[name=content]').val(),
            pic_ids: $('input[name=pic_ids]').val()
        };

        if(para.id == '')
        {
            para.schedule_id = $('input[name=schedule_id]').val();
        }

        if(para.cid == '')
        {
            alert('客户CID必填！');
            return false;
        }

        if (para.visit_time == ''){
            alert('拜访时间必填！');
            return false;
        }

        if (para.visit_type == 0){
            alert('拜访类型必选！');
            return false;
        }

        if (para.address == ''){
            alert('拜访地址必填！');
            return false;
        }

        var picIdsArr = para.pic_ids.split(',');
        if(picIdsArr.length > 8)
        {
            alert('最多只能上传8张照片！');
            return false;
        }

        $(this).attr('disabled', true);
        K.post('/crm2/ajax/save_customer_visit.php', para, _onSaveCustomerVisitSuccess);
    }

    function _onSaveCustomerVisitSuccess(data) {
        var id = $('input[name=id]').val();
        alert('保存成功');
        window.location.href = '/crm2/customer_visit_list.php';
    }

    function words_deal()
    {
        var curLength=$("#TextArea1").val().length;
        if(curLength>500)
        {
            var num=$("#TextArea1").val().substr(0,500);
            $("#TextArea1").val(num);
            $("#textCount").text(0);
            alert("超过字数限制，多出的字将被截断！" );
        }
        else
        {
            $("#textCount").text(500-$("#TextArea1").val().length);
        }
    }

    main();

})();