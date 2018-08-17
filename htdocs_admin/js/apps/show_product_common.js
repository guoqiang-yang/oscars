var add_product_dlg =  {

    add_paras: {},
    obj_id: null,
    obj_type: null,
    dialog: null,
    init: function(_add_params){
        var _this = this;
        var show_box = $('#show_product_common');
        _this.dialog = $('#add_product_modal_common');
        _this.obj_type = show_box.attr('data-objtype');
        _this.obj_id =   show_box.attr('data-objid');
        _this.add_paras = _add_params;
        
        // 注册事件
        show_box.on('click', function (evt) {
            _this.show_dlg(evt);
        });
        _this.dialog.on('hidden.bs.modal', function () {
            window.location.reload();
        });

        _this.dialog.on('click', function (evt) {
            var para={};
            para.obj_id = _this.obj_id;
            para.obj_type = _this.obj_type;

            if ($(evt.target).hasClass('search_product_common') || $(evt.target).hasClass('change_product_list_page')) {

                //点击查询和翻页
                para.keyword = _this.dialog.find('input[name=keyword]').val();
                para.start = $(evt.target).attr('data-start');

                K.post('/common/ajax/get_product_list_for_stock.php', para, function (ret) {
                    _this.dialog.find('.modal-body').html(ret.html);
                });
            }else if ($(evt.target).hasClass('add_product')) {

                //点击添加商品
                $.each(_this.add_paras,function (i, item) {
                    para[item] = $(evt.target).closest('tr').find('input[name=' + item + ']').val();
                });

                K.post('/common/ajax/add_product_for_stock.php', para, function(){
                    alert('添加成功！');
                    $.each(_this.add_paras,function (i, item) {
                        $(evt.target).closest('tr').find('input[name=' + item + ']').attr('disabled', true);
                    });
                    $(evt.target).parent().html('<label class="control-label">已添加</label>');
                });
            }
        });

    },
    show_dlg: function(evt){
        var dialog = $('#add_product_modal_common');
        var obj_type = $(evt.target).attr('data-objtype');
        var obj_id = $(evt.target).attr('data-objid');
        dialog.attr('data-objtype', obj_type);
        dialog.attr('data-objid', obj_id);
        $('#add_product_modal_common').modal();
    }
};