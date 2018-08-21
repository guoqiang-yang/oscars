<?php

/**
 * 后台配置
 * 权限和岗位是两码事。 但是目前权限是基于岗位的。 岗位粒度为主，然后再根据人的粒度微调。
 */
class Conf_Admin
{
	/**
	 * 管理员账号：自动管理员.
	 */
	const ADMINOR_AUTO = 999;
    
    /**
     * 超级管理员.
     */
    public static $SUPER_ADMINER = array(
        
    );

    
    //角色定义-新（根据新的权限管理系统来获取）
    const ROLE_ADMIN_NEW        = 'admin';                      //管理员
    const ROLE_SALES_NEW        = 'sales';                      //销售
    
    
    
}
