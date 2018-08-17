(function () {

    function main() {
        //保存picture
        $('#btn_save_picture').click(_onSavePicture);
        $('._j_offline_picture').click(_onOfflinePicture);
        $('._j_online_picture').click(_onOnlinePicture);
        $('.delete_btn').bind('click', deletePicture);
        $('.activity_type').click(choiceActivityType);
    }

    function _onSavePicture(ev) {
        var tgt = $(ev.currentTarget),
            frm = tgt.closest('form');

        var cate1Arr = [];
        $('input[name=mids]:checked').each(function () {
            cate1Arr.push($(this).val());
        });

        var para = {
            id: frm.find('input[name=id]').val(),
            name: frm.find('input[name=name]').val(),
            url: frm.find('input[name=url]').val(),
            display_order: frm.find('input[name=display_order]').val(),
            start_time: frm.find('input[name=start_time]').val(),
            end_time: frm.find('input[name=end_time]').val(),
            pic_tag: frm.find('input[name=pic_tag]').val(),
            type: frm.find('select[name=type]').val(),
            activity_type: frm.find('input[name=activity_type]:checked').val(),
            commodity_sid: frm.find('input[name=commodity_sid]').val(),
        };
        var obj = document.getElementsByName('platform');
        var s = '';
        for (var i = 0; i < obj.length; i++) {
            if (obj[i].checked) {
                s += obj[i].value + ',';
            }
        }

        var city = document.getElementsByName('city_id');
        var city_id = '';
        for (var i = 0; i < city.length; i++) {
            if (city[i].checked) {
                city_id += city[i].value + ',';
            }
        }
        para.city_id = city_id;
        para.platform = s;
        _getPicInfo(para);

        K.post('/activity/ajax/save_picture.php', para, _onSavepictureSucss);
    }

    function _getPicInfo(para) {

        if (K.headAreaSelector) {
            var selection = K.headAreaSelector.getSelection();
            para.x1 = selection.x1;
            para.y1 = selection.y1;
            para.width = selection.width;
            para.height = selection.height;
            para.imgwidth = $('#_j_upload_view_img').width();
            para.imgheight = $('#_j_upload_view_img').height();
        }

    }

    function _onSavepictureSucss(data) {
        window.location.href = "/activity/picture_list.php";
    }

    function _onOfflinePicture(ev) {
        var tgt = $(ev.currentTarget),
            pdom = tgt.closest('._j_pic'),
            id = pdom.data('id');

        if (!confirm("确定要下线该图片吗？")) {
            return false;
        }

        K.post('/activity/ajax/offline_pic.php', {id: id}, _onOfflinePicSucss);
    }

    function _onOfflinePicSucss(data) {
        window.location.reload();
    }

    function _onOnlinePicture(ev) {
        var tgt = $(ev.currentTarget),
            pdom = tgt.closest('._j_pic'),
            id = pdom.data('id');

        if (!confirm("确定要上线该图片吗？\n\n上线只会修改状态，而不会修改开始结束时间。")) {
            return false;
        }

        K.post('/activity/ajax/offline_pic.php?type=online', {id: id}, _onOnlinePicSucss);
    }

    function _onOnlinePicSucss(data) {
        window.location.reload();
    }

    function deletePicture() {
        if (confirm("确定要删除该图片吗？")) {
            var id = $(this).data('id');
            var para = {id: id};

            K.post('/activity/ajax/delete_picture.php', para, _onDeletePictureSucc);
        }
    }
    function _onDeletePictureSucc(data) {
        alert('删除成功！');
        window.location.reload();
    }

    function choiceActivityType() {
        var activity_type = $("input[name='activity_type']:checked").val();

        if(activity_type == 1){
            $('.url').show();
            $('.commodity_sid').hide();
        }else{
            $('.url').hide();
            $('.commodity_sid').show();
        }

    }
    main();

})();