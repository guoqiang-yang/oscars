/**
 * Created by qihua on 17/8/30.
 */

var PHONETYPE = '';
var busy_reasons = {};
var wincall = null;
(function(){
//            wincall.disconnect();
////
//            //注销
//            sockeIsLogin = false;
//            wincall.fn_logout();
//            return false;

    var sockeIsLogin = false;

    // 初始化连接
    var wintel_server_ip = 'socket.icsoc.net';
    var vccCode = '6017082301';
    var wintelapi_url = 'http://m.icsoc.net';
    wincall = new WinCall({
        wintel_server_ip: wintel_server_ip,
        //wintel_server_port: '6050',
        wintelapi_url: wintelapi_url,
        vcc_code: vccCode,
        busy_reasons: busy_reasons,
        debug: true,
        event_listener: eventListener
    });
    wincall.init();
    sockeIsLogin = true;

    var agentNum = $('#ce_agent_num').val();
    var agentPass = $('#ce_agent_pass').val();
    var agentPhone = $('#ce_agent_phone').val();
    if (K.isEmpty(agentNum) || K.isEmpty(agentPass) || K.isEmpty(agentPhone)) {
        alert('请尚未获得呼叫中心权限！');
        return false;
    }

    sockeIsLogin = false;
    wincall.fn_logout();
    wincall.fn_login(agentNum, $.md5(agentPass), agentPhone, 1, 0);

    $('.call_user_phone').bind('click', callPhone);

    function callPhone() {
        if (!wincall.isLogin) {
            alert('坐席未签入');
            return false;
        }

        //获取外呼号码
        var phone = $(this).data('phone');
        //获取外呼技能组ID
        var ag_ques = wincall.fn_get_que();
        var que_id = ag_ques[0];
        //获取主叫号码
        var ag_callers = wincall.fn_get_caller();
        var caller = ag_callers[0];

        wincall.fn_dialouter(phone, caller, que_id);
    }

    //挂断
//            wincall.fn_hangup();

    //挂断
//            wincall.fn_answer();
})();

function eventListener(response) {
    var $message = $('#message');

    // if (response.queStatus) {
    //     $('#queue_state').html(response.queStatus);
    // }
    // $('#obj_content').html(response.msg);
    // $message.append(response.msg+"\r\n");
    //
    // $.each(response.disableActions, function (index, value) {
    //     $('#btn_'+value).attr('disabled', 'disabled');
    // });
    //
    // $.each(response.enableActions, function (index, value) {
    //     $('#btn_'+value).removeAttr('disabled');
    // });
    //
    // switch (response.type) {
    //     case 'login_action':
    //         //设置技能组
    //         var ag_ques = wincall.fn_get_que();
    //         var $groupid = $('#groupid');
    //         $groupid.empty();
    //         $.each(ag_ques, function (index,item) {
    //             $groupid.append($('<option value="'+item+'">'+item+'</option>'))
    //         });
    //         // 设置主叫号码
    //         var ag_callers = wincall.fn_get_caller();
    //         var $callerid = $('#callerid');
    //         $callerid.empty();
    //         $.each(ag_callers, function (index,item) {
    //             $callerid.append($('<option value="'+item+'">'+item+'</option>'))
    //         });
    //         break;
    //     case 'ring_queue_event'://技能组分配来电
    //         var $caller = wincall.fn_getParam('Caller');
    //         var $caller_areacode = wincall.fn_getParam('CallerAreaCode');
    //         var $caller_areaname = wincall.fn_getParam('CallerAreaName');
    //         $message.append($caller+'['+$caller_areacode+'-'+$caller_areaname+ ']'+'来电\r\n');
    //         /**
    //          * 直接调用应答函数
    //          * 需要注意的是来电时有两个需要应答，一个是wincall中需要应答，
    //          * 还有一个是软电话或者PSTN电话也需要应答，那么这样就可能导致操作的不方便，
    //          * 因为wincall中的JS SDK是无法控制软电话和PSTN电话的，所以来电后必须手动点击接听按钮，
    //          * 如此情况下，我们就在来电事件中自动调用wincall中的应答函数，这样就只需要手动点击
    //          * 软电话或PSTN电话接听按钮就可以了，不需要两个都去点击接听
    //          */
    //         wincall.fn_answer();
    //         /**
    //          * 自定义来电的操作，可以实现弹屏的功能，例如弹出一个页面，将主叫的信息传递到页面中
    //          * 实现自定义的查询功能，如根据主叫来电查询对应的客户信息等
    //          */
    //         // 实现代码...
    //         break;
    //     case 'ring_outbound_event'://外呼来电中
    //         var called = wincall.fn_getParam('Called');
    //         var CallerAreaCode = wincall.fn_getParam('CallerAreaCode');
    //         var CallerAreaName = wincall.fn_getParam('CallerAreaName');
    //         $message.append('呼叫外线号码'+called+'['+CallerAreaCode+'-'+CallerAreaName+']\r\n');
    //         break;
    //     case 'call_afterwards_event'://事后处理
    //         var $call_afterwards_secs = wincall.fn_getParam('CallAfterwardsSecs');
    //         $message.append('事后处理时长为['+$call_afterwards_secs+']\r\n');
    //         break;
    //     case 'system_busy_event'://系统置忙
    //         var $busy_reason = wincall.fn_getParam('BusyReason');
    //         $message.append('具体置忙原因为['+$busy_reason+']\r\n');
    //         break;
    //     case 'update_queue_event'://更新技能组
    //         ag_ques = wincall.fn_get_que();
    //         $groupid = $('#groupid');
    //         $groupid.empty();
    //         $.each(ag_ques, function (index,item) {
    //             $groupid.append($('<option value="'+item+'">'+item+'</option>'))
    //         });
    //         break;
    //     case 'update_caller_event'://更新主叫号码
    //         ag_callers = wincall.fn_get_caller();
    //         $callerid = $('#callerid');
    //         $callerid.empty();
    //         $.each(ag_callers, function (index,item) {
    //             $callerid.append($('<option value="'+item+'">'+item+'</option>'))
    //         });
    //         break;
    // }
}