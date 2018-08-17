<?php

class Conf_Api_Exception
{
    
    public static function getMessage($code)
    {
        
    }
    
    private static $_EXCEPTION_MAPPING = array(
        '100'   => 'c_inner',   //通用：内部
        '110'   => 'crm',       //客户
        '120'   => 'oms',       //后台订单
        '130'   => 'wms',       //仓库
        '140'   => 'tms',       //调度
        '150'   => 'fms',       //财务
        '160'   => 'supm',      //供应商
        '170'   => 'pms',       //采购
        
        '200'   => 'c_outer',   //通用：外部
        '210'   => 'c_order',   //c端订单
        
        '999'   => 'null',      //勿动，默认错误
    );
    
    
    
    private static $_EXCEPTION_MSGS = array(
        
        '0'         => '成功',
        '100000'    => '对不起，操作失败！',
        
        
    );
    
    private static $_EXCEPTION_MSGS_COMMON = array(
        
    );
    
    private static $_EXP_MSG_COMMON = array(
        
    );
}
