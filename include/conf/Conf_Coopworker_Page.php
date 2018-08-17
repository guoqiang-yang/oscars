<?php

class Conf_Coopworker_Page
{
    private static $MODULES = array(
        
        'user' => array(
            'name' => '个人中心',
            'pages' => array(
                array('name'=>'修改密码', 'url'=>'/user/chgpwd.php', 'page'=>'chgpwd'),
                //array('name'=>'用户信息', 'url'=>'/user/user_info.php', 'page'=>'user_info'),
            ),
        ),
        
        'order' => array(
            'name' => '订单管理',
            'pages' => array(
                array('name'=>'我的订单', 'url'=>'/order/my_order_list.php', 'page'=>'my_order_list'),
                //array('name'=>'领单', 'url'=>'/order/get_order.php', 'page'=>'get_order'),
                array('name'=>'线路订单', 'url'=>'/order/line_detail.php', 'page'=>'line_detail'),
            ),
        ),
        
    );
    
    public static function getModules()
    {
        return self::$MODULES;
    }
}