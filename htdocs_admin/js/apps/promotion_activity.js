/**
 * Created by zouliangwei on 2017/6/23.
 */
var str_html = new Array();
str_html[1] = '<label class="inline">满&emsp;<input type="number" name="conf_man[]" value="" class="form-control" style="padding:6px 0; display: inline-block;width: 70px;"/>，立减&emsp;<input type="number" name="conf_jian[]" value="" class="form-control" style="padding:6px 0; display: inline-block; width: 70px;">&emsp;<a href="javascript:;" onclick="delConfHtml($(this));" class="btn-link" title="点击删除" style="width: 20px; padding: 5px; cursor: pointer; font-size: 20px;">Ｘ</a></label><br/>';
str_html[2] = '<label class="inline">满&emsp;<input type="number" name="conf_man[]" value="" class="form-control" style="padding:6px 0; display: inline-block;width: 70px;"/>，送优惠券&emsp;<input type="number" name="conf_coupon[]" value="" class="form-control" style="padding:6px 0; display: inline-block; width: 70px;">，数量&emsp;<input type="number" name="conf_num[]" value="1" class="form-control" style="padding:6px 0; display: inline-block; width: 70px;">&emsp;<a href="javascript:;" onclick="delConfHtml($(this));" class="btn-link" title="点击删除" style="width: 20px; padding: 5px; cursor: pointer; font-size: 20px;">Ｘ</a></label><br/>';
str_html[3] = '<label class="inline">满&emsp;<input type="number" name="conf_man[]" value="" class="form-control" style="padding:6px 0; display: inline-block;width: 70px;"/>，立减&emsp;<input type="number" name="conf_jian[]" value="" class="form-control" style="padding:6px 0; display: inline-block; width: 70px;">，送优惠券&emsp;<input type="number" name="conf_coupon[]" value="" class="form-control" style="padding:6px 0; display: inline-block; width: 70px;">&emsp;<a href="javascript:;" onclick="delConfHtml($(this));" class="btn-link" title="点击删除" style="width: 20px; padding: 5px; cursor: pointer; font-size: 20px;">Ｘ</a></label><br/>';
str_html[5] = '<lable class="inline">满&emsp;<input type="number" name="conf_man[]" value="" class="form-control" style="padding:6px 0; display: inline-block;width: 70px;"/>，赠送(sid)&emsp;<input type="text" name="conf_sid[]" value="" class="form-control" style="padding:6px 0; display: inline-block; width: 120px;">，数量(num)&emsp;<input type="text" name="conf_num[]" value="" class="form-control" style="padding:6px 0; display: inline-block; width: 120px;">&emsp;<a href="javascript:;" onclick="delConfHtml($(this));" class="btn-link" title="点击删除" style="width: 20px; padding: 5px; cursor: pointer; font-size: 20px;">Ｘ</a></lable><br/>';
str_html[6] = '<lable class="inline">满&emsp;<input type="number" name="conf_man[]" value="" class="form-control" style="padding:6px 0; display: inline-block;width: 70px;"/>，特价(sid)&emsp;<input type="text" name="conf_sid[]" value="" class="form-control" style="padding:6px 0; display: inline-block; width: 120px;">，价格&emsp;<input type="text" name="conf_price[]" value="" class="form-control" style="padding: 6px 0; display: inline-block; width: 120px;">，数量(num)&emsp;<input type="text" name="conf_num[]" value="" class="form-control" style="padding:6px 0; display: inline-block; width: 120px;">&emsp;<a href="javascript:;" onclick="delConfHtml($(this));" class="btn-link" title="点击删除" style="width: 20px; padding: 5px; cursor: pointer; font-size: 20px;">Ｘ</a></lable><br/>';
var str_html2 = new Array();
str_html2[1] = '<label class="inline">满&emsp;<input type="number" name="conf_man[]" value="" class="form-control" style="padding:6px 0; display: inline-block;width: 70px;"/>，立减&emsp;<input type="number" name="conf_jian[]" value="" class="form-control" style="padding:6px 0; display: inline-block; width: 70px;">&emsp;<a href="javascript:;" onclick="addConfHtml();" class="btn-link" title="点击新增" style="width: 20px; padding: 5px; cursor: pointer; font-size: 20px;">+</a></label><br/>';
str_html2[2] = '<label class="inline">满&emsp;<input type="number" name="conf_man[]" value="" class="form-control" style="padding:6px 0; display: inline-block;width: 70px;"/>，送优惠券&emsp;<input type="number" name="conf_coupon[]" value="" class="form-control" style="padding:6px 0; display: inline-block; width: 70px;">，数量&emsp;<input type="number" name="conf_num[]" value="1" class="form-control" style="padding:6px 0; display: inline-block; width: 70px;">&emsp;<a href="javascript:;" onclick="addConfHtml();" class="btn-link" title="点击新增" style="width: 20px; padding: 5px; cursor: pointer; font-size: 20px;">+</a></label><br/>';
str_html2[3] = '<label class="inline">满&emsp;<input type="number" name="conf_man[]" value="" class="form-control" style="padding:6px 0; display: inline-block;width: 70px;"/>，立减&emsp;<input type="number" name="conf_jian[]" value="" class="form-control" style="padding:6px 0; display: inline-block; width: 70px;">，送优惠券&emsp;<input type="number" name="conf_coupon[]" value="" class="form-control" style="padding:6px 0; display: inline-block; width: 70px;">&emsp;<a href="javascript:;" onclick="addConfHtml();" class="btn-link" title="点击新增" style="width: 20px; padding: 5px; cursor: pointer; font-size: 20px;">+</a></label><br/>';
str_html2[4] = '<label class="inline"><input type="radio" name="m_type" checked="checked" value="1" />&emsp;平台折扣，订单满<input type="amount" class="form-control" style="padding: 6px 5px; display: inline-block; width: 70px;" name="conf_amount" value="">元打<input type="number" class="form-control" style="padding: 6px 5px; display: inline-block; width: 70px;" name="conf_man" value="">折&emsp;</label>';
str_html2[5] = '<lable class="inline">满&emsp;<input type="number" name="conf_man[]" value="" class="form-control" style="padding:6px 0; display: inline-block;width: 70px;"/>，赠送(sid)&emsp;<input type="text" name="conf_sid[]" value="" class="form-control" style="padding:6px 0; display: inline-block; width: 120px;">，数量(num)&emsp;<input type="text" name="conf_num[]" value="" class="form-control" style="padding:6px 0; display: inline-block; width: 120px;">&emsp;<a href="javascript:;" onclick="addConfHtml();" class="btn-link" title="点击新增" style="width: 20px; padding: 5px; cursor: pointer; font-size: 20px;">+</a></lable><br/>';
str_html2[6] = '<lable class="inline">满&emsp;<input type="number" name="conf_man[]" value="" class="form-control" style="padding:6px 0; display: inline-block;width: 70px;"/>，特价(sid)&emsp;<input type="text" name="conf_sid[]" value="" class="form-control" style="padding:6px 0; display: inline-block; width: 120px;">，价格&emsp;<input type="text" name="conf_price[]" value="" class="form-control" style="padding: 6px 0; display: inline-block; width: 120px;">，数量(num)&emsp;<input type="text" name="conf_num[]" value="" class="form-control" style="padding:6px 0; display: inline-block; width: 120px;">&emsp;<a href="javascript:;" onclick="addConfHtml();" class="btn-link" title="点击新增" style="width: 20px; padding: 5px; cursor: pointer; font-size: 20px;">+</a></lable><br/>';
var flag = false;
function addConfHtml() {
    var activity_type = $('#activity_type').val();
    $('#conf').append(str_html[activity_type]);
}
function delConfHtml(obj) {
    obj.parent().next().remove();
    obj.parent().remove();
}
function changeType() {
    var type_id = $('#activity_type').val();
    if(flag)
    {
        $('#conf').html(str_html2[type_id]);
    }else{
        flag = true;
    }
    $("input[name='type_ids\[\]']").each(function(index, element) {
        if(type_id == 2){
            $(element).removeAttr("checked");
        }else{
            if ($(element).val() == type_id) {
                $(element).parent().hide();
                $(element).removeAttr("checked");
            } else {
                $(element).parent().show();
            }
        }
    });
    if(type_id == 2){
        $('#is_bear_activitiy').hide();
    }else{
        $('#is_bear_activitiy').show();
    }
}
$(function () {
    changeType();
    $('#activity_type').on('change', changeType);
    $('#user_type').on('change',function () {
        if($(this).val()==1){
            $('#user_type_show').show();
        }else{
            $('#user_type_show').hide();
            $("input[name='user_type_extand\[\]']").each(function(){
                $(this).attr("checked",false);
            });
        }
    });
    $('#goods_type').on('change',function () {
        if($(this).val()==1){
            $('#goods_type_show').show();
            $("input[name='goods_brand_ids']").val('').attr('disabled', false);
        }else if($(this).val()==2){
            $('#goods_type_show').hide();
            $("input[name='goods_brand_ids']").val('').attr('disabled', true);
        }else{
            $("input[name='goods_brand_ids']").val('').attr('disabled', false);
            $('#goods_type_show').hide();
            $("input[name='goods_cate_ids\[\]']").each(function(){
                $(this).attr("checked",false);
            });
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

    function onSaveManjian() {
        if(is_submit){
            alert('请不要重复提交，请等待系统处理结果！');
            return false;
        }
        var data = $('#activity_manjian').serialize();
        var id = $('#id').val();
        var title = $('#title').val();
        var stime = $('#stime').val();
        var etime = $('#etime').val();
        var type = $('#activity_type').val();
        var conf =$('#conf').val();

        if (K.isEmpty(title)) {
            alert('请填写活动名称！');
            return false;
        }
        if (K.isEmpty(stime)) {
            alert('请填写活动开始时间！');
            return false;
        }
        if (K.isEmpty(etime)) {
            alert('请填写活动结束时间！');
            return false;
        }
        var needBreak = false;
        if(type !=4)
        {
            $("input[name='conf_man\[\]']").each(function () {
                if(K.isEmpty($(this).val()))
                {
                    alert('请填写完整额度配置下单额度！');
                    needBreak = true;
                    return false;
                }
            });
        }

        if(needBreak) return false;
        if(type == 1)
        {
            $("input[name='conf_jian\[\]']").each(function () {
                if(K.isEmpty($(this).val()))
                {
                    alert('请填写完整满减配置立减额度！');
                    needBreak = true;
                    return false;
                }
            });
        }
        if(needBreak) return false;
        if(type == 2)
        {
            $("input[name='conf_coupon\[\]']").each(function () {
                if(K.isEmpty($(this).val()))
                {
                    alert('请填写完整额度配置优惠券ID！');
                    needBreak = true;
                    return false;
                }
            });
            $("input[name='conf_num\[\]']").each(function () {
                if(K.isEmpty($(this).val()))
                {
                    alert('请填写完整额度配置优惠券数量！');
                    needBreak = true;
                    return false;
                }
            });
        }
        if(needBreak) return false;
        if(type == 4 && $('#activity_manjian').find($("input[name=m_type]:checked")).val() == 1 && K.isEmpty($('#activity_manjian').find($("input[name=conf_man]")).val()))
        {
            alert('请填写平台折扣打折比例');
            return false;
        }
        if(type == 5)
        {
            $("input[name='conf_sid\[\]']").each(function () {
                if(K.isEmpty($(this).val()))
                {
                    alert('请填写完整额度配置赠送商品sid！');
                    needBreak = true;
                    return false;
                }
            });
            $("input[name='conf_num\[\]']").each(function () {
                if(K.isEmpty($(this).val()))
                {
                    alert('请填写完整额度配置赠送商品数量！');
                    needBreak = true;
                    return false;
                }
            });
        }
        if(type == 6)
        {
            $("input[name='conf_sid\[\]']").each(function () {
                if(K.isEmpty($(this).val()))
                {
                    alert('请填写完整额度配置特价商品sid！');
                    needBreak = true;
                    return false;
                }
            });
            $("input[name='conf_price\[\]']").each(function () {
                if(K.isEmpty($(this).val()))
                {
                    alert('请填写完整额度配置特价商品价格！');
                    needBreak = true;
                    return false;
                }
            });
            $("input[name='conf_num\[\]']").each(function () {
                if(K.isEmpty($(this).val()))
                {
                    alert('请填写完整额度配置特价商品数量！');
                    needBreak = true;
                    return false;
                }
            });

        }
        var falg = 0;
        $("input[name='city_ids\[\]']:checkbox").each(function () {
            if ($(this).is(':checked')) {
                falg += 1;
            }
        });
        if(falg < 1){
            alert('请勾选参加活动的城市！');
            return false;
        }
        if($('#user_type').val()==1)
        {
            falg = 0;
            $("input[name='user_type_extand\[\]']:checkbox").each(function () {
                if ($(this).is(':checked')) {
                    falg += 1;
                }
            });
            if(falg < 1){
                alert('请勾选参加活动的用户类别！');
                return false;
            }
        }
        if($('#goods_type').val()==1)
        {
            falg = 0;
            $("input[name='goods_cate_ids\[\]']:checkbox").each(function () {
                if ($(this).is(':checked')) {
                    falg += 1;
                }
            });
            if(falg < 1){
                alert('请勾选参加活动的商品分类！');
                return false;
            }
        }
        if($('#delivery_time_type').val() == 1)
        {
            if($('#delivery_after_day').val()=='')
            {
                alert('请填写配送日期');
                return false;
            }
            if($('#delivery_stime').val() == '' || $('#delivery_etime').val() == '')
            {
                alert('请填写配送时间段');
                return false;
            }
        }
        is_submit = true;

        K.post('/activity/ajax/save_promotion_manjian.php', data, onSaveSucc, onSaveFail);
    }

    function onSaveSucc(data)
    {
        if(data.errormsg){
            alert(data.errormsg);
        }else{
            alert('保存成功');
            window.location.href = '/activity/promotion_manjian_list.php';
        }
    }

    function onSaveFail(data)
    {
        alert(data.errmsg);
        is_submit = false;
    }

    $('#save_manjian').click(onSaveManjian);
    var id = $('#id').val();
    $(".btn-danger").on('click',function () {
        var action_type = $(this).attr('data-status');
        var tip_info = '';
        if(action_type=='online'){
            tip_info = '上线';
        }else{
            tip_info = '下线';
        }
        if(confirm("确定要"+tip_info+"该活动吗？")){
            var data = 'id='+id+'&action_type='+action_type;
            K.post('/activity/ajax/save_promotion_manjian.php', data, onSaveSucc(tip_info+'成功'), onSaveFail);
        }
    });
})
