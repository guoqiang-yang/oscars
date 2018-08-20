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
        1029, 1004, 1254,1289,1254,1679
    );

    
    //角色定义-新（根据新的权限管理系统来获取）
    const ROLE_ADMIN_NEW        = 'admin';                      //管理员
    const ROLE_BUYER_NEW        = 'LCZY';                       //基础采购
    const ROLE_FINANCE_NEW      = 'JCCW1';                      //基础财务
    const ROLE_WAREHOUSE_NEW    = 'PTCC';                       //基础仓储  
    const ROLE_SALES_NEW        = 'PTXS1';                      //基础销售
    const ROLE_CS_NEW           = 'PTKF';                       //基础客服
    const ROLE_AFTER_SALE_NEW   = 'PTSH';                       //基础售后
    const ROLE_LM_NEW           = 'PTWL';                       //基础物流
    const ROLE_ASSIS_SALER_NEW  = 'XSZL1';                      //销售助理    
    const ROLE_OP_NEW           = 'YYZY1';                      //基础运营
    
    
    
}
