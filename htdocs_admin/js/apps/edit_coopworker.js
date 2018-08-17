(function () {
    function main() {
        //编辑运费、搬运费
        if ($('.edit_coopworker_price').length > 0) {
            $('.edit_coopworker_price').on('click', showEditCoopworkerDlg);
        }
        if ($('#edit_coopworker_dlg').length > 0)
        {
            $('#edit_coopworker_dlg').on('click', clickEditCoopworkerDlg);
        }
        $('#edit_coopworker_dlg').on('change', '.driver_times', changeDriverTimes);
    }

    //显示编辑运费、搬运费的弹框
    function showEditCoopworkerDlg(evt) {
        var para = {
            id:$(evt.target).closest('.coopworker_info').attr('data-id')
        };

        K.post('/common/ajax/show_edit_coopworker.php', para, function (ret) {
            $('#edit_coopworker_dlg').find('.modal-body').html(ret.html);
            $('#edit_coopworker_dlg').modal();
        });
    }

    //点击编辑运费、搬运费弹框内部事件
    function clickEditCoopworkerDlg(evt) {
        if ($(evt.target).hasClass('save_coopworker_price')) {
            var dialog = $('#edit_coopworker_dlg');
            var params = dialog.find('.edit_coopworker_price_param');
            var info = {};
            $.each(params, function () {
                info[$(this).attr('name')] = $(this).val();
            });

            var para = {
                info: JSON.stringify(info)
            };
            K.post('/common/ajax/save_edit_coopworker_price.php', para, function () {
                alert('保存成功！');
                window.location.reload();
            });
        }else if($(evt.target).hasClass('count_total_driver_price')) {
            var dialog = $('#edit_coopworker_dlg');
            var params = dialog.find('.edit_coopworker_price_param');
            var info = {};
            $.each(params, function () {
                var _val = $(this).val();
                if (_val == '')
                {
                    _val = 0;
                }
                info[$(this).attr('name')] = _val;
            });

            var total = parseFloat(info.refer_price) + parseFloat(info.trash_price) +
                parseFloat(info.second_ring_road_price) + parseFloat(info.reward_price) - parseFloat(info.fine_price) +
                parseFloat(info.other_price);
            dialog.find('.total_price').html('￥' + total + '元');
        }else if($(evt.target).hasClass('count_total_carrier_price')) {
            var dialog = $('#edit_coopworker_dlg');
            var params = dialog.find('.edit_coopworker_price_param');
            var info = {};
            $.each(params, function () {
                info[$(this).attr('name')] = $(this).val();
            });


            var total = parseFloat(info.base_price) + parseFloat(info.reward_price) - parseFloat(info.fine_price) +
                parseFloat(info.other_price);
            dialog.find('.total_price').html('￥' + total + '元');
        }
    }
    
    function changeDriverTimes() {
        var para = {
            id: $('#edit_coopworker_dlg').find('input[name=id]').val(),
            times: $(this).val()
        };

        K.post('/common/ajax/get_refer_price.php', para, function (ret) {
            $('#edit_coopworker_dlg').find('.refer_price').html(ret.refer_price/100 + '元');
            $('#edit_coopworker_dlg').find('input[name=refer_price]').val(ret.refer_price/100);
        });
    }

    main();
    
})();