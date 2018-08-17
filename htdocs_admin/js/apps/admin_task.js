(function(){
    function main(){
        
        $('._j_show_admin_task').on('click', showAdminTask);
        
        $('.modify_task_status').on('click', modifyTaskStatus);
        
        $('._j_modify_exec_suid').on('click', modifyExecSuid);
        
        $('.get_task_tome').on('click', getTaskTome);
        
        $('.save_note').on('click', saveNote);
    }
    
    // 创建任务
    function createAdminTask(){
        var _dialog = $('#_j_dislog_createAdminTask');
        var para = {
            objid: $(this).attr('data-objid'),
            objtype: _dialog.find('select[name=objtype]').val(),
            short_desc: _dialog.find('select[name=short_desc]').val(),
            exec_role: _dialog.find('select[name=exec_role]').val(),
            exec_suid: _dialog.find('select[name=exec_suid]').val(),
            title: _dialog.find('input[name=title]').val(),
            content: _dialog.find('textarea[name=content]').val()
        };
        
        if (para.exec_suid=='0' || para.exec_role=='0'){
            alert('请选择执行人！'); return;
        }
        if (para.objtype=='0' || para.short_desc=='0'){
            alert('请选择类别'); return;
        }
        if (para.title.length >30){
            alert('标题长度过长，请修改！'); return;
        }
        if(para.content.length >200 || para.content.length==0){
            alert('内容长度过长或为空，请修改！'); return;
        }
        
        $(this).attr('disabled', true);
        K.post('/user/ajax/save_task.php', para, function(ret){
            if (ret.st==0){
                alert('添加成功！');
                $('#_j_dislog_createAdminTask').modal('hide');
            } else {
                alert(ret.msg);
            }
            $('#_j_create_adminTask').attr('disabled', false);
        });
    }
    
    // 显示创建任务的对话框
    function showAdminTask(){
        var para = {
            objtype: $(this).attr('data-objtype'),
            objid: $(this).attr('data-objid')
        };
        
        if ($('#_j_dislog_createAdminTask').length==0){
        
            $(this).attr('disabled', true);
            K.post('/user/ajax/show_task.php', para, function(ret){
                $('body').append(ret.html);
                
                var _dialog = $('#_j_dislog_createAdminTask');
                
                // 注册事件
                _registerEvents(_dialog);
                
                // 初始化
                var objtype = _dialog.find('select[name=objtype]').val();
                if (objtype != 0){
                    _dialog.find('select[name=short_desc]').html(_changeCateOfShortDesc(objtype));
                }
                
                _dialog.find('#_j_create_adminTask').attr('data-objid', para.objid);
                _dialog.modal();
                
                $('._j_show_admin_task').attr('disabled', false);
                
            });
        } else {
            
            var _dialog = $('#_j_dislog_createAdminTask');
            _dialog.find('select[name=objtype]').val(para.objtype);
            _dialog.find('select[name=short_desc]').val(0);
            _dialog.find('#_adtask_change_role').val(0);
            _dialog.find('select[name=exec_suid]').val(0);
            _dialog.find('input[name=title]').val('');
            _dialog.find('textarea[name=content]').val('');
            _dialog.find('#_j_create_adminTask').attr('data-objid', para.objid);
            $('#_j_dislog_createAdminTask').modal();
            
            $('._j_show_admin_task').attr('disabled', false);
        }
    }
    
    
    function _registerEvents(_dialog){
        
        // staff 角色切换
        _dialog.find('#_adtask_change_role').on('change', function(){
            var role = $(this).val();
            _dialog.find('select[name=exec_suid]').html(_changeRole(role));
        });

        // 分类切换
        _dialog.find('#_adtask_change_objtype').on('change', function(){
            var objtype = $(this).val();
            _dialog.find('select[name=short_desc]').html(_changeCateOfShortDesc(objtype));
        });
            
        // 计数器
        _dialog.find('textarea[name=content]').on('input', function(){
            _calculateInputLength(this);
        });
        
        _dialog.find('input[name=title]').on('input', function(){
            _calculateInputLength(this);
        });
        
        
        _dialog.find('#_j_create_adminTask').on('click', createAdminTask);
    }
    
    function _changeRole(role){
    
        var staffsHtml = '<option value="0">请选择</option>';
        var allStaffs = eval('(' + $('input[name=all_staffs]').val() + ')');
        
        for(var _role in allStaffs){
            if (_role == role){
                for(var i in allStaffs[_role]){
                    staffsHtml += '<option value="'+allStaffs[_role][i].suid+'">'+allStaffs[_role][i].name+'</option>'
                }
            }
        }
        
        return staffsHtml;
    }
    
    function _changeCateOfShortDesc(objtype){
        var shortDescHtml = '<option value="0">请选择</option>';
        var allShortDescs = eval('(' + $('input[name=all_short_descs]').val() + ')');
        
        for (var _objtype in allShortDescs){
            if (_objtype == objtype){
                for (var i in allShortDescs[_objtype]){
                    shortDescHtml += '<option value="'+i+'">'+allShortDescs[_objtype][i]+'</option>';
                }
            }
        }
        
        return shortDescHtml;
    }
    
    function _calculateInputLength(obj){
        var len = $(obj).val().length;
        var hCounter = $(obj).parent().find('._j_counter');
        var maxLength = $(obj).parent().find('._j_maxLength').html();
        hCounter.html(len);

        if (maxLength < len){
            hCounter.css('color', 'red');
        } else {
            hCounter.css('color', 'black');
        }
    };
    
    
    //////////////////// 修改任务 ///////////////////////
    
    function modifyTaskStatus(){
        var  para = {
            tid: $(this).attr('data-tid'),
            exec_status: $(this).attr('data-exec_status'),
            note: $(this).closest('form').find('textarea[name=note]').val(),
            otype: 'modify_exec_status'
        };
        
        if(!confirm('确认进行该操作？')){
            return false;
        }
        
        K.post('/user/ajax/modify_task.php', para, function(){
            alert('修改成功！');
            window.location.reload();
        });
    }
    
    function modifyExecSuid(){
        var para = {
            tid: $(this).attr('data-tid'),
            exec_suid: $(this).closest('#editExecSuid').find('select[name=exec_suid]').val(),
            old_exec_suid: $(this).closest('#editExecSuid').find('input[name=old_exec_suid]').val(),
            note: $(this).closest('#editExecSuid').find('textarea[name=note]').val(),
            otype: 'modify_exec_suid'
        };
        
        if (para.exec_suid == para.old_exec_suid){
            alert('没有修改执行人！');return;
        }
        K.post('/user/ajax/modify_task.php', para, function(){
            alert('修改成功！');
            window.location.reload();
        });
    }
    
    //认领任务
    function getTaskTome(){
        var para = {
            tid: $(this).attr('data-tid'),
            otype: 'get_task_tome'
        };
        
        K.post('/user/ajax/modify_task.php', para, function(ret){
            window.location.href='/user/admin_task_detail.php?tid='+ret.data.tid;
        });
    }
    
    function saveNote(){
        var para = {
            tid: $(this).attr('data-tid'),
            note: $(this).closest('form').find('textarea[name=note]').val(),
            otype: 'save_note'
        };
        if (para.note.length==0){
            alert('备注不能为空！');return;
        }
        K.post('/user/ajax/modify_task.php', para, function(){
            alert('修改成功！');
            window.location.reload();
        });
    }
    
    main();
})();