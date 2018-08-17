
/**
 * 全局使用的，在html文档末尾加载的JS.
 */

(function(){
    
    function main(){
        auto_show_admin_log();
        
        $('#show_hccommon_admin_log').on('click', show_more_admin_log);
    }
    
    function auto_show_admin_log(pageStart){
        
        var dom = $('#show_hccommon_admin_log');
        pageStart = pageStart||0;

        if (dom.length == 0) return;

        var para = {
            obj_id: dom.attr('data-objid'),
            obj_type: dom.attr('data-objtype'),
            action_type: dom.attr('data-actiontype'),
            start: pageStart,
            city_id: 0,     //保留字段
            wid: 0          //保留字段
        };
        K.post('/common/ajax/admin_log.php', para, function(data){
            dom.html(data.html);
        }); 
    }
    
    function show_more_admin_log(evt){
        
        var target = $(evt.target);
        if (target.hasClass('hccommon_admin_log_pagetruning')){
            var pageStart = target.attr('data-start');

            auto_show_admin_log(pageStart);
        }        
    }
//    // 自动加载后台日志
//    (function(){
//        var dom = $('#show_hccommon_admin_log');
//
//        if (dom.length == 0) return;
//
//        var para = {
//            obj_id: dom.attr('data-objid'),
//            obj_type: dom.attr('data-objtype'),
//            action_type: dom.attr('data-actiontype'),
//            start: 0,
//            city_id: 0,     //保留字段
//            wid: 0          //保留字段
//        };
//        K.post('/common/ajax/admin_log.php', para, function(data){
//            dom.html(data.html);
//        });
//    })();
    
    main();
})();