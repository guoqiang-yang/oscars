(function () {

    function main() {
        $('#save').click(onSaveRole);
        $(':checkbox').on('click', selCheckBox);
        $('#save_permission').on('click', savePermission);
        $('#select_department').on('change', selDepartment);
        $('.department_item').on('click', selDepartmentRoles);
        $('#save_roles').on('click', saveRoles);
        $('.del_rel_role').on('click', delRelRole);
    }

    function saveRoles() {
        var roles = [];
        $('.role_item').each(function () {
            if ($(this).is(':checked')) {
                roles.push($(this).val());
            }
        });

        if (roles.length <= 0) {
            alert('请先选择一些角色吧！');
            return false;
        }

        var suid = $('#suid').val();
        var para = {suid: suid, roles: roles};
        K.post('/admin/ajax/save_roles.php', para, saveRolesSucc);
    }

    function saveRolesSucc(data) {
        window.location.href = "/admin/staff_list.php";
    }

    function selDepartmentRoles() {
        var checked = $(this).is(':checked');
        if (checked) {
            var curVal = $(this).val();
            $(':checkbox').each(function (index) {
                if ($(this).data("department") == curVal) {
                    $(this).prop('checked', "true");
                }
            });
        } else {
            var curVal = $(this).val();
            $(':checkbox').each(function (index) {
                if ($(this).data("department") == curVal) {
                    $(this).removeProp('checked');
                }
            });
        }
    }

    function selDepartment() {
        var did = $(this).val();

        $('.department_list').each(function (index) {
            if (did > 0) {
                if ($(this).data("did") == did) {
                    $(this).css('display', '');
                } else {
                    $(this).css('display', 'none');
                }
            } else {
                $(this).css('display', '');
            }
        });
    }

    function savePermission() {
        var permissionList = {};
        var roleId = $('#role_id').val();

        $('.permission_item').each(function (index) {
            if ($(this).is(':checked')) {
                var srole = $(this).data('srole');
                if (permissionList[srole] == undefined) {
                    permissionList[srole] = [];
                }
                permissionList[srole].push($(this).val());
            }
        });

        if (permissionList.length <= 0) {
            alert('请先选择一些权限吧！');
            return false;
        }

        var para = {role_id: roleId, permission_list: JSON.stringify(permissionList)};
        K.post('/admin/ajax/save_permission.php', para, savePermissionSucc);
    }

    function savePermissionSucc(data) {
        window.location.href = "/admin/role_list.php";
    }

    function selCheckBox() {
        var checked = $(this).is(':checked');
        if ($(this).hasClass("parent_menu")) {
            if (checked) {
                var curVal = $(this).val();
                $(':checkbox').each(function (index) {
                    if ($(this).data("frole") == curVal) {
                        $(this).prop('checked', "true");
                    } else if ($(this).data("srole") == curVal) {
                        $(this).prop('checked', "true");
                    }
                });
            } else {
                var curVal = $(this).val();
                $(':checkbox').each(function (index) {
                    if ($(this).data("frole") == curVal) {
                        $(this).removeProp('checked');
                    } else if ($(this).data("srole") == curVal) {
                        $(this).removeProp('checked');
                    }
                });
            }
        }
    }

    function onSaveRole() {
        var id = $('input[name=id]').val();
        var role = $('input[name=role]').val();
        var rkey = $('input[name=rkey]').val();
        var department = $('select[name=department]').val();
        var relRoles = $('textarea[name=rel_role]').val();

        if (K.isEmpty(role)) {
            alert('请填写角色名！');
            return false;
        }
        if (K.isEmpty(rkey)) {
            alert('请填写标识符！');
            return false;
        }
        if (K.isEmpty(department)) {
            alert('请选择部门！');
            return false;
        }

        var para = {id: id, role: role, rkey: rkey, department: department, rel_role: relRoles};
        K.post('/admin/ajax/save_role.php', para, _onSaveRoleSuccess);
    }

    function _onSaveRoleSuccess(data) {
        if (data.is_new) {
            window.location.href = '/admin/edit_permission.php?id=' + data.id + "&is_new=1";
        } else {
            //window.location.href = "/admin/role_list.php";
            window.location.reload();
        }
    }
    
    function delRelRole(){
        var para = {
            id: $('input[name=id]').val(),
            rel_id: $(this).attr('data-id')
        };
        
        K.post('/admin/ajax/del_rel_role.php', para, function(){
            alert('删除成功！');
            window.location.reload();
        });
    }

    main();

})();