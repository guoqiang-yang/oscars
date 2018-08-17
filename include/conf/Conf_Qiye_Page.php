<?php

class Conf_Qiye_Page
{
    private static $MODULES = array(
        
        'worker' => array(
            'name' => '工长管理',
            'pages' => array(
                array('name'=>'工长列表', 'url'=>'/worker/worker_list.php', 'page'=>'worker_list'),
                array('name'=>'订单列表', 'url'=>'/worker/order_list.php', 'page'=>'order_list'),
                array('name'=>'数据统计', 'url'=>'/worker/data_count.php', 'page'=>'data_count'),
            ),
        ),
        
        'user' => array(
            'name' => '个人中心',
            'pages' => array(
                array('name'=>'企业信息', 'url'=>'/user/detail.php', 'page'=>'detail'),
                array('name'=>'修改密码', 'url'=>'/user/chgpwd.php', 'page'=>'chgpwd'),
            ),
        ),
        
    );
    
    public static function getModules($uid, $user)
    {
        if (empty($uid) || empty($user))
        {
            return array();
        }
        
        return self::$MODULES;
    }
}