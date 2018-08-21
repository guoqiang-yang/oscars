<?php

/**
 * 页面列表
 */
class Conf_Admin_Page
{
	public static $MODULES = array(

        'crm2' => array(
            'name' => '客户管理',
            'pages' => array(
                array(
                    'name' => '客户列表',
                    'url' => '/crm2/customer_list.php',
                    'page' => 'customer_list',
                    'key' => '/crm2/customer_list',
                    'buttons' => array(
                        array('key' => '/crm2/customer_list',   'name' => '查看列表',),
                        array('key' => '/crm2/customer_detail', 'name' => '详情',),
                        array('key' => '/crm2/edit_customer',   'name' => '编辑',),
                    ),
                ),
                array(
                    'name' => '添加客户',
                    'url' => '/crm2/new_customer.php',
                    'page' => 'new_customer',
                    'key' => '/crm2/new_customer',
                    'buttons' => array(
                        array('key' => '/crm2/new_customer',    'name' => '查看列表',),
                    ),
                ),
            ),
        ),
        
        'order' => array(
            'name' => '订单管理',
            'pages' => array(
                array(
                    'name' => '订单列表',
                    'url' => '/order/order_list.php',
                    'page' => 'order_list',
                    'key' => '/order/order_list',
                    'buttons' => array(
                        array('key' => '/order/order_list',         'name' => '列表'),
                        array('key' => '/order/order_detail',       'name' => '详情'),
                        array('key' => '/order/edit_order',         'name' => '编辑'),
                        array('key' => '/order/order_print',        'name' => '打印'),
                        array('key' => '/order/ajax/reset_order',   'name' => '恢复'),
                        array('key' => '/order/ajax/cancel_order',  'name' => '取消'),
                        array('key' => 'hc_order_cs_confirm',       'name' => '客服确认'),
                        array('key' => 'hc_order_delivred',         'name' => '出库'),
                        array('key' => 'hc_order_bill_back',        'name' => '回单'),
                        array('key' => '/finance/ajax/save_money_in',       'name' => '订单收款'),
                        array('key' => '/order/ajax/modify_by_finance',     'name' => '优惠调整'),
                        array('key' => '/order/ajax/modify_by_operator',    'name' => '运/搬费调整'),
                    ),
                ),
                
                array(
                    'name' => '用户查询',
                    'url' => '/order/customer_list_cs.php',
                    'page' => 'customer_list_cs',
                    'key' => '/order/customer_list_cs',
                    'buttons' => array(
                        array('key' => '/order/customer_list_cs', 'name' => '列表'),
                        array('key' => '/order/add_order2', 'name' => '一步下单'),
                        array('key' => '/crm2/ajax/reset_pass', 'name' => '重置密码'),
                    ),
                ),
                array(
                    'name' => '小区管理',
                    'url' => '/order/delivery_community.php',
                    'page' => 'delivery_community',
                    'key' => '/order/delivery_community',
                    'buttons' => array(
                        array('key' => '/order/delivery_community', 'name' => '列表'),
                        array('key' => '/order/ajax/edit_community', 'name' => '编辑'),
                        array('key' => '/order/ajax/merge_community', 'name' => '合并'),
                        array('key' => 'order_check_community_orders', 'name' => '查看订单'),
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
                        array('key' => '/shop/product_list',        'name' => '查看列表',),
                        array('key' => '/shop/product_search',      'name' => '搜索',),
                        array('key' => '/shop/edit_product',        'name' => '编辑',),
                        array('key' => '/shop/ajax/save_product',   'name' => '编辑保存'),
                        array('key' => '/shop/ajax/offline_product','name' => '上架/下架',),
                        array('key' => '/shop/ajax/delete_product', 'name' => '删除',),
                    ),
                ),
				array(
                    'name' => 'sku列表',
                    'url' => '/shop/sku_list.php',
                    'page' => 'sku_list',
                    'key' => '/shop/sku_list.php',
                    'buttons' => array(
                        array('key' => '/shop/sku_list',            'name' => '查看列表',),
                        array('key' => '/shop/sku_search',          'name' => '搜索',),
                        array('key' => '/shop/edit_sku',            'name' => '编辑',),
                        array('key' => '/shop/add_product',         'name' => '添加/查看商品',),
                    ),
                ),
				array(
                    'name' => '添加sku',
                    'url' => '/shop/add_sku.php',
                    'page' => 'add_sku',
                    'key' => '/shop/add_sku',
                    'buttons' => array(
                        array('key' => '/shop/add_sku',             'name' => '添加sku',),
                    ),
                ),
				array(
                    'name' => '品牌管理',
                    'url' => '/shop/brand_list.php',
                    'page' => 'brand_list',
                    'key' => '/shop/brand_list',
                    'buttons' => array(
                        array('key' => '/shop/brand_list',          'name' => '列表'),
                        array('key' => '/shop/edit_brand',          'name' => '编辑',),
                        array('key' => '/shop/ajax/delete_brand',   'name' => '删除',),
                    ),
                ),
			),
		),
        'logistics' => array(
            'name' => '物流管理',
            'pages' => array(
                array(
                    'name' => '司机账号',
                    'url' => '/logistics/driver.php',
                    'page' => 'driver',
                    'key' => '/logistics/driver',
                    'buttons' => array(
                        array('key' => '/logistics/driver',             'name' => '查看列表',),
                        array('key' => '/logistics/add_driver',         'name' => '查看详情',),
                        array('key' => '/logistics/ajax/save_driver',   'name' => '添加/编辑司机',),
                        array('key' => '/logistics/ajax/delete_driver', 'name' => '删除司机',),
                    ),
                ),
                array(
                    'name' => '搬运工账号',
                    'url' => '/logistics/carrier.php',
                    'page' => 'carrier',
                    'key' => '/logistics/carrier',
                    'buttons' => array(
                        array('key' => '/logistics/carrier',            'name' => '查看列表',),
                        array('key' => '/logistics/add_carrier',        'name' => '添加/编辑搬运工',),
                        array('key' => '/logistics/ajax/delete_carrier','name' => '删除搬运工',),
                    ),
                ),
            ),
        ),
		'warehouse' => array(
			'name' => '仓库管理',
			'pages' => array(
				array(
                    'name' => '库存', 
                    'url' => '/warehouse/stock_list.php', 
                    'page' => 'stock_list',
                    'key' => '/warehouse/stock_list',
                    'buttons' => array(
                        array('key' => '/warehouse/stock_list',             'name' => '列表'),
                        array('key' => '/warehouse/edit_stock',             'name' => '编辑'),
                        array('key' => '/warehouse/stock_search',           'name' => '搜索'),
                        array('key' => '/warehouse/ajax/clear_err_occupied','name' => '清错占用'),
                    ),
                ),
				array(
                    'name' => '货位库存', 
                    'url' => '/warehouse/location_list.php', 
                    'page'=>'location_list',
                    'key' => '/warehouse/location_list',
                    'buttons' => array(
                        array('key' => '/warehouse/location_list',          'name' => '列表'),
                        array('key' => '/warehouse/ajax/add_location',      'name' => '添加货位'),
                        array('key' => 'hc_inventory_profit',               'name' => '盘盈'),
                        array('key' => 'hc_inventory_loss',                 'name' => '盘亏'),
                        array('key' => '/warehouse/ajax/save_shift_location_stock', 'name' => '移架'),
                        array('key' => '/warehouse/ajax/del_sku_location',          'name' => '删除'),
                        array('key' => '/warehouse/ajax/search_unshelved_bills',    'name' => '查看未上架'),
                    ),
                ),
				array(
                    'name' => '出入库历史', 
                    'url' => '/warehouse/stock_history.php', 
                    'page' => 'stock_history',
                    'key' => '/warehouse/stock_warning',
                    'buttons' => array(
                        array('key' => '/warehouse/stock_history',          'name' => '列表'),
                    ),
                ),
			)
		),
        'purchase' => array(
            'name' => '采购管理',
            'pages' => array(
                array(
                    'name' => '供应商', 
                    'url' => '/warehouse/supplier_list.php', 
                    'page' => 'supplier_list',
                    'key' => '/warehouse/supplier_list',
                    'buttons' => array(
                        array('key' => '/warehouse/supplier_list',                      'name' => '列表'),
                        array('key' => '/warehouse/add_supplier',                       'name' => '添加供应商'),
                        array('key' => '/warehouse/edit_supplier',                      'name' => '编辑'),
                        array('key' => '/warehouse/ajax/change_supplier_status',        'name' => '停用&审核'),
                        array('key' => '/warehouse/supplier_sku_list',                  'name' => '供应商的商品列表'),
                        array('key' => '/warehouse/ajax/save_supplier_purchase_price',  'name' => '维护采购价'),
                        array('key' => '/warehouse/ajax/del_supplier_sku',              'name' => '删除供应商sku'),
                        array('key' => '/warehouse/ajax/add_sku_4_supplier',            'name' => '添加供应商sku'),
                        array('key' => '/warehouse/ajax/create_inorder_4_supplier',     'name' => '创建采购单'),
                    ),
                ),
				array(
                    'name' => '采购单', 
                    'url' => '/warehouse/in_order_list.php', 
                    'page' => 'in_order_list',
                    'key' => '/warehouse/in_order_list',
                    'buttons' => array(
                        array('key' => '/warehouse/in_order_list',                      'name' => '列表'),
                        array('key' => '/warehouse/edit_in_order',                      'name' => '编辑'),
                        array('key' => '/warehouse/detail_in_order',                    'name' => '详情'),
                        array('key' => '/warehouse/in_order_print',                     'name' => '打印'),
                        array('key' => '/warehouse/add_in_order',                       'name' => '添加采购单'),
                        array('key' => '/warehouse/ajax/delete_order',                  'name' => '删除采购单'),
                        array('key' => '/warehouse/ajax/complete_receiver',             'name' => '完全收货'),
                        array('key' => '/warehouse/ajax/delete_product',                'name' => '删除商品'),
                        array('key' => '/warehouse/ajax/modify_inorder_product',        'name' => '改价格/数量'),
                        array('key' => '/warehouse/ajax/complate_tmp_inorder',          'name' => '临采完成'),
                        array('key' => '/warehouse/ajax/dlg_supplier_list',             'name' => '修改供应商'),
                        array('key' => 'hc_in_order_audit',                             'name' => '采购单审核'),
                    ),
                ),
                array(
                    'name' => '入库单',
                    'url' => '/warehouse/stock_in_lists.php',
                    'page' => 'stock_in_lists',
                    'key' => '/warehouse/stock_in_lists',
                    'buttons' => array(
                        array('key' => '/warehouse/stock_in_lists',                     'name' => '列表'),
                        array('key' => '/warehouse/edit_stock_in',                      'name' => '详情'),
                        array('key' => '/warehouse/ajax/save_stock_in',                 'name' => '入库'),
                        array('key' => '/warehouse/shelved_detail',                     'name' => '上架'),
                        array('key' => '/warehouse/ajax/confirm_paid',                  'name' => '付款'),
                        array('key' => '/warehouse/stock_in_print',                     'name' => '打印'),
                    ),
                ),
                array(
                    'name' => '入库退货单',
                    'url' => '/warehouse/stockin_refund_list.php',
                    'page' => 'stockin_refund_list',
                    'key' => '/warehouse/stockin_refund_list',
                    'buttons' => array(
                        array('key' => '/warehouse/stockin_refund_list',                'name' => '列表'),
                        array('key' => '/warehouse/stockin_refund_detail',              'name' => '详情'),
                        array('key' => '/warehouse/ajax/refund_stockin',                'name' => '创建'),
                        array('key' => '/warehouse/ajax/confirm_refund_stockin',        'name' => '退货出库'),
                        array('key' => '/warehouse/ajax/del_stockin_refund',            'name' => '删除退货单'),
                        array('key' => '/warehouse/ajax/update_refund_product_price',   'name' => '改退货商品单价')
                    ),
                ),
                array(
                    'name' => '临时采购单',
                    'url' => '/warehouse/tmp_purchase2.php', 
                    'page'=>'tmp_purchase2',
                    'key' => '/warehouse/tmp_purchase2', 
                    'buttons' => array(
                        array('key' => '/warehouse/tmp_purchase2',                      'name' => '列表'),
                        array('key' => '/warehouse/ajax/create_tmp_2_inorder',          'name' => '创建'),
                    ),
                ),
            ),
        ),
		'finance' => array(
			'name' => '财务管理',
			'pages' => array(
				array(
                    'name' => '应收汇总（客户）',
                    'url' => '/finance/customer_list.php',
                    'page' => 'customer_list',
                    'key' => '/finance/customer_list',
                    'buttons' => array(
                        array('key' => '/finance/customer_list',                        'name' => '查看列表'),
                    ),
                ),
				array(
                    'name' => '应收明细（客户）',
                    'url' => '/finance/customer_bill_list.php',
                    'page' => 'customer_bill_list',
                    'key' => '/finance/customer_bill_list',
                    'buttons' => array(
                        array('key' => '/finance/customer_bill_list',                   'name' => '查看列表'),
                        array('key' => '/finance/ajax/save_single_moneyIn_modify',      'name' => '修改明细'),
                    ),
                ),
				array(
                    'name' => '应付汇总（供应商）',
                    'url' => '/finance/supplier_list.php',
                    'page' => 'supplier_list',
                    'key' => '/finance/supplier_list',
                    'buttons' => array(
                        array('key' => '/finance/supplier_list',                         'name' => '查看列表'),
                    ),
                ),
				array(
                    'name' => '应付明细（供应商）',
                    'url' => '/finance/supplier_bill_list.php',
                    'page' => 'supplier_bill_list',
                    'key' => '/finance/supplier_bill_list',
                    'buttons' => array(
                        array('key' => '/finance/supplier_bill_list',                   'name' => '查看列表'),
                        array('key' => '/finance/ajax/modify_money_out',                'name' => '修改明细'),
                    ),
                ),
			)
		),
        'admin' => array(
            'name' => '后台管理',
            'pages' => array(
                array(
                    'name' => '账号管理',
                    'url' => '/admin/staff_list.php',
                    'page' => 'staff_list',
                    'key' => '/admin/staff_list',
                    'buttons' => array(
                        array('key' => '/admin/staff_list',                             'name' => '列表'),
                        array('key' => '/admin/edit_staff',                             'name' => '编辑/添加账号'),
                        array('key' => '/admin/edit_staff_role',                        'name' => '编辑角色'),
                        array('key' => '/admin/reset_staff_password',                   'name' => '重置密码'),
                    ),
                ),
                array(
                    'name' => '角色管理',
                    'url' => '/admin/role_list.php',
                    'page' => 'role_list',
                    'key' => '/admin/role_list',
                    'buttons' => array(
                        array('key' => '/admin/role_list',                              'name' => '列表'),
                        array('key' => '/admin/edit_role',                              'name' => '编辑/添加角色'),
                        array('key' => '/admin/edit_permission',                        'name' => '编辑权限'),
                    ),
                ),
            ),
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
                        array('key' => '/user/chgpwd',                                  'name' => '修改密码'),
                    ),
                ),
			)
		),
	);

	public static function getMODULES($suid, $suser)
	{
		$modules = self::$MODULES;

        return $modules;
        
		//return Conf_Admin::getMODULES($suid, $suser, $modules);
        return Conf_Permission::getModules($suser, $modules);
	}

	public static function getFirstPage($suid, $suser)
	{
        return '/user/chgpwd.php';
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
