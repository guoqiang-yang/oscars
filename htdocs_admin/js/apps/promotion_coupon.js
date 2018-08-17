/**
 * Created by zouliangwei on 2017/12/11.
 */
$(function () {
    function changeType() {
        var type_id = parseInt($('#coupon_type').val()) +6;
        if(type_id == 9)
        {
            $('#coupon_share').hide();
            $('#coupon_more_reason'). hide();
        }else{
            $('#coupon_share').show();
            $('#coupon_more_reason'). show();
        }
        $("input[name='type_ids\[\]']").each(function(index, element) {
            if($(element).val() == type_id)
            {
                $(element).parent().hide();
                $(element).removeAttr("checked");
            }else{
                $(element).parent().show();
            }
        });
    }
    $('#validity_type').on('change',function () {
        if($(this).val()==1){
            $('#validity_type_show').show();
            $('#validity_type_show2').hide();
        }else{
            $('#validity_type_show2').show();
            $('#validity_type_show').hide();
        }
    });
    $('#delivery_time_type').on('change',function () {
        if($(this).val()==1){
            $('#delivery_time_type_show').show();
        }else{
            $('#delivery_time_type_show').hide();
        }
    });

    var is_submit = false;

    function onSaveCoupon() {
        if(is_submit){
            alert('请不要重复提交，请等待系统处理结果！');
            return false;
        }
        var id = $('#id').val();
        var title = $('#title').val();
        var validity_type = $('#validity_type').val();

        var conf =$('#conf').val();

        if (K.isEmpty(title)) {
            alert('请填写优惠券名称！');
            return false;
        }
        if(validity_type == 1){
            var stime = $('#stime').val();
            var etime = $('#etime').val();
            if(K.isEmpty(stime)){
                alert('请填写有效期起始时间！');
                return false;
            }
            if(K.isEmpty(etime)){
                alert('请填写有效期结束时间！');
                return false;
            }
        }else{
            var lastdate = $('#lastdate').val();
            if(K.isEmpty(lastdate)){
                alert('请填写有效期天数！');
                return false;
            }
        }

        if (K.isEmpty(conf)) {
            alert('请填写额度配置！');
            return false;
        }
        var data = $('#activity_coupon').serialize();

        K.post('/activity/ajax/save_coupon.php', data, onSaveSucc, onSaveFail);
    }

    function onSaveSucc(data)
    {
        if(data.errormsg){
            alert(data.errormsg);
        }else{
            alert('保存成功');
            window.location.href = '/activity/coupon_list.php';
        }
    }

    function onSaveFail(data)
    {
        alert(data.errmsg);
        is_submit = false;
    }
    changeType();
    $('#coupon_type').on('change', changeType);
    $('#save_coupon').click(onSaveCoupon);
})