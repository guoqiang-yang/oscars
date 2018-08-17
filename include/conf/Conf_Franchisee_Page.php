<?php

/**
 * 页面列表
 */
class Conf_Franchisee_Page
{
	public static $MODULES = array(

//        'crm2' => array(
//            'name' => '客户管理',
//            'pages' => array(
//                array(
//                    'name' => '客户列表',
//                    'url' => '/crm2/customer_list.php',
//                    'page' => 'customer_list',
//                    'key' => '/crm2/customer_list',
//                    'buttons' => array(
//                        array('key' => '/crm2/customer_list', 'name' => '查看列表',),
//                        array('key' => '/crm2/new_customer', 'name' => '添加客户',),
//                        array('key' => '/crm2/customer_detail', 'name' => '详情',),
//                        array('key' => '/crm2/edit_customer', 'name' => '编辑',),
//                    ),
//                ),
//                array(
//                    'name' => '添加客户',
//                    'url' => '/crm2/new_customer.php',
//                    'page' => 'new_customer',
//                    'key' => '/crm2/new_customer',
//                    'buttons' => array(
//                        array('key' => '/crm2/new_customer', 'name' => '查看列表',),
//                    ),
//                ),
//            ),
//        ),
        'order' => array(
            'name' => '订单管理',
            'pages' => array(
                array(
                    'name' => '订单列表',
                    'url' => '/order/order_list.php',
                    'page' => 'order_list',
                    'key' => '/order/order_list',
                    'buttons' => array(
                        array('key' => '/order/order_list', 'name' => '列表'),
                        array('key' => '/order/order_detail', 'name' => '详情'),
                        array('key' => '/order/edit_order', 'name' => '编辑'),
                        array('key' => '/order/order_print', 'name' => '打印'),
                        array('key' => 'hc_order_cs_confirm', 'name' => '客服确认'),
                        array('key' => 'hc_order_delivred', 'name' => '出库'),
                        array('key' => 'hc_order_bill_back', 'name' => '回单'),
                        array('key' => '/finance/ajax/save_money_in', 'name' => '订单收款'),
                        array('key' => '/order/ajax/modify_by_finance', 'name' => '财务调整'),
                        array('key' => '/order/ajax/set_part_paid', 'name' => '设为部分收款'),
                        array('key' => '/order/ajax/refund_and_delete', 'name' => '退款并删除'),
                    ),
                ),
			),
		),
		'shop' => array(
			'name' => '商品管理',
			'pages' => array(
				array(
                    'name' => '商品列表',
                    'url' => '/shop/product_list.php?online=1',
                    'page' => 'product_list',
                    'key' => '/shop/product_list',
                    'buttons' => array(
                        array('key' => '/shop/product_list', 'name' => '查看列表',),
                        array('key' => '/shop/product_search', 'name' => '搜索',),
                        array('key' => '/shop/edit_product', 'name' => '编辑',),
                        array('key' => '/shop/ajax/save_product', 'name' => '编辑保存'),
                        array('key' => '/shop/ajax/offline_product', 'name' => '上架/下架',),
//                        array('key' => '/shop/ajax/delete_product', 'name' => '删除',),
                        array('key' => '/shop/edit_sortby', 'name' => '更改排序',),
                        array('key' => 'edit_shop_product_price', 'name' => '编辑商品价格'),
                    ),
                ),
			)
		),
		'user' => array(
            'display' => 'hidden',
			'name' => '个人中心',
			'pages' => array(
				array(
                    'name' => '修改密码',
                    'url' => '/user/chgpwd.php',
                    'page' => 'chgpwd',
                    'key' => '/user/chgpwd',
                    'buttons' => array(
                        array('key' => '/user/chgpwd', 'name' => '修改密码'),
                    ),
                ),
			)
		),
	);

	public static function getMODULES($suid, $suser)
	{
	    if(empty($suid) || empty($suser))
        {
            return array();
        }
		$modules = self::$MODULES;

		//return Conf_Admin::getMODULES($suid, $suser, $modules);
        return $modules;
	}

	public static function getFirstPage($suid, $suser)
	{
		$url = '/order/order_list.php';
        return $url;
	}

    public static function getModulesForRoleManage()
    {
        $data = array();
        foreach (self::$MODULES as $key => $module)
        {
            $data[$key]['name'] = $module['name'];
            foreach ($module['pages'] as $page)
            {
                $data[$key]['pages'][$page['key']]['name'] = $page['name'];
                foreach ($page['buttons'] as $button)
                {
                    $data[$key]['pages'][$page['key']]['buttons'][$button['key']] = $button['name'];
                }
            }
        }

        return $data;
    }
}
