<?php
/**
 * 用户配置.
 */

class Conf_User
{	
    /**
     * 用户注册来源
     */
    const 
          CUSTOMER_REG_SALER    = 101,
          CUSTOMER_REG_CS       = 102,
          CUSTOMER_REG_SHOP     = 103,
          CUSTOMER_REG_APP_ANDROID  = 104,
          CUSTOMER_REG_APP_IOS      = 105;
    
    public static function getCustomerSource()
    {
        return array(
            self::CUSTOMER_REG_SALER        => '销售注册',
            self::CUSTOMER_REG_CS           => '客服注册',
            self::CUSTOMER_REG_SHOP         => '微信商城',
            self::CUSTOMER_REG_APP_ANDROID  => 'Android',
            self::CUSTOMER_REG_APP_IOS      => 'IOS',
        );
    }
    
    /**
     * 客户身份.
     */
    const CRM_IDENTITY_PERSONAL     = 1,
          CRM_IDENTITY_COMPANY      = 2;
    
    public static $Crm_Identity = array(
        self::CRM_IDENTITY_PERSONAL     => '工长',
        self::CRM_IDENTITY_COMPANY      => '公司',
    );
    
    /**
     * 客户所处的销售状态.
     */
    
    const CRM_SALE_ST_PRIVATE       = 1,
          CRM_SALE_ST_PUBLIC        = 2,
          CRM_SALE_ST_INNER         = 3,
            
          CRM_SALE_ST_ABANDON       = 99;
    
    public static $Customer_Sale_Status = array(
        self::CRM_SALE_ST_PRIVATE           => '私海',
        self::CRM_SALE_ST_PUBLIC            => '公海',
        self::CRM_SALE_ST_INNER             => '内海',
        
        self::CRM_SALE_ST_ABANDON           => '无效客户',
    );
    
    /**
     * 客户的系统级别
     */
    
    const
          CRM_SYS_LEVEL_VIP         = 5,
          CRM_SYS_LEVEL_BETTER      = 4,
          CRM_SYS_LEVEL_COMMON      = 3,
          CRM_SYS_LEVEL_OBSERVE     = 2,
          CRM_SYS_LEVEL_BAD         = 1;
    
    public static $Customer_Sys_Level_Descs = array(
        self::CRM_SYS_LEVEL_VIP         => 'VIP客户',
        self::CRM_SYS_LEVEL_BETTER      => '优质客户',
        self::CRM_SYS_LEVEL_COMMON      => '普通客户',
        self::CRM_SYS_LEVEL_OBSERVE     => '待观察客户',
        self::CRM_SYS_LEVEL_BAD         => '恶劣客户',
    );
    
}