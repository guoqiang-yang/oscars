<?php
/**
 * 页面列表
 */
class Conf_Admin_Page_H5
{
	private static $MODULES = array(
        'crm' => array(
			'name' => '客户管理',
			'pages' => array(
				array('name'=>'我的客户', 'url'=>'/crm/my_customers.php', 'page' => 'my_customers'),
				array('name'=>'添加客户', 'url'=>'/crm/new_customer.php', 'page' => 'new_customer'),
				//array('name'=>'回访客户', 'url'=>'/crm/need_tracking_customers.php', 'page'=>'need_tracking_customers'),
				//array('name'=>'优惠券', 'url'=>'/crm/coupon_list.php', 'page'=>'coupon_list'),
                array('name'=>'待进公海', 'url'=>'/crm/will_in_public_customer.php', 'page'=>'will_in_public_customer'),
                array('name'=>'公海', 'url'=>'/crm/public_customer.php', 'page'=>'public_customer'),
				array('name'=>'客户查询', 'url'=>'/crm/customer_list.php', 'page' => 'customer_list'),
				array('name'=>'欠款客户', 'url'=>'/crm/my_customers.php?order=account_balance', 'page'=>'owe_list'),
				array('name'=>'优惠审批', 'url'=>'/crm/apply_coupon_list.php?status=1', 'page' => 'apply_coupon_list'),
			)
		),
		'order' => array(
			'name' => '订单管理',
			'pages' => array(
				array('name'=>'订单', 'url'=>'/order/order_list.php', 'page' => 'order_list'),
				array('name'=>'退款单', 'url'=>'/order/refund_list.php', 'page' => 'refund_list'),
				array('name'=>'销售明细', 'url'=>'/order/sale_detail.php', 'page'=>'sale_detail'),
				array('name'=>'欠款订单', 'url'=>'/order/order_list_not_pay.php', 'page'=>'order_list_not_pay'),
			)
		),
		'warehouse' => array(
			'name' => '仓库管理',
			'pages' => array(
				array('name'=>'库存', 'url'=>'/warehouse/stock_list.php', 'page' => 'stock_list'),
				array('name'=>'供应商', 'url'=>'/warehouse/supplier_list.php', 'page' => 'supplier_list'),
				array('name'=>'采购订单', 'url'=>'/warehouse/in_order_list.php', 'page' => 'in_order_list'),
				array('name'=>'入库单', 'url'=>'/warehouse/stock_in_lists.php', 'page' => 'stock_in_lists'),
				array('name'=>'库存预警', 'url'=>'/warehouse/stock_alert.php', 'page' => 'stock_alert'),
				array('name'=>'出入库历史', 'url'=>'/warehouse/stock_history.php', 'page'=>'stock_history'),
			)
		),
        'purchase' => array(
            'name' => '采购',
            'pages' => array(
                array('name'=>'待采[临]', 'url'=> '/purchase/temp_wait_list.php', 'page'=>'temp_wait_list'),
                array('name'=>'已采[临]', 'url'=> '/purchase/temp_bought_list.php', 'page'=>'temp_bought_list'),
                array('name'=>'预警[临]', 'url'=> '/purchase/temp_alert_list.php', 'page'=>'temp_alert_list'),
            ),
        ),
        'aftersale' => array(
            'name' => '工单管理',
            'pages' => array(
                array('name'=>'工单列表', 'url'=> '/aftersale/list.php', 'page'=>'aftersale_list'),
                array('name'=>'新建工单', 'url'=> '/aftersale/edit.php', 'page'=>'aftersale_edit'),
            ),
        ),
		'user' => array(
			'name' => '个人中心',
			'pages' => array(
                array('name'=>'任务列表', 'url'=>'/user/admin_task_list.php', 'page'=>'admin_task_list'),
                array('name'=>'新建任务', 'url'=>'/user/create_task.php', 'page'=>'create_task'),
				array('name'=>'修改密码', 'url'=>'/user/chgpwd.php', 'page' => 'chgpwd'),
			)
		),
        /*
        'finance' => array(
            'name' => '财务管理',
			'pages' => array(
				array('name'=>'应收汇总', 'url'=> '/finance/customer_list.php', 'page'=>'customer_list'),
				//array('name'=>'应收明细', 'url'=>'/finance/customer_bill_list.php', 'page'=>'customer_bill_list'),
			)
        ),
         */
	);

	public static function getMODULES($suid, $suser)
	{
		$modules = self::$MODULES;
		$modules = Conf_Admin::getMODULES($suid, $suser, $modules);
		$roleLevels = Admin_Role_Api::getRoleLevels($suid, $suser);

		if (isset($roleLevels[Conf_Admin::ROLE_ADMIN]))
		{
			foreach ($modules['crm']['pages'] as $idx=>$item)
			{
				if ($item['page'] == 'new_customer')
				{
					unset($modules['crm']['pages'][$idx]);
				}
			}
		}
		elseif (!empty($suser))
		{
			foreach ($modules['crm']['pages'] as $idx=>$item)
			{
				if ($item['page'] == 'apply_coupon_list')
				{
					unset($modules['crm']['pages'][$idx]);
				}
			}
		}
		return $modules;
	}


	public static function getFirstPage($suid, $suser)
	{
		$url = '';
		$roleLevels = Admin_Role_Api::getRoleLevels($suid, $suser);
		if (isset($roleLevels[Conf_Admin::ROLE_ADMIN_NEW]))
		{
			$url = '/order/order_list.php';
		}
		elseif (isset($roleLevels[Conf_Admin::ROLE_SALES_NEW]))
		{
			$url = '/crm/my_customers.php';
		}
		elseif (isset($roleLevels[Conf_Admin::ROLE_BUYER]))
		{
			$url = '/order/sale_detail.php?btype=2';
		}
		elseif (isset($roleLevels[Conf_Admin::ROLE_LM]))
		{
			$url = '/order/order_list.php';
		}
		elseif (isset($roleLevels[Conf_Admin::ROLE_CS]))
		{
			$url = '/order/order_list.php';
		}
		elseif (isset($roleLevels[Conf_Admin::ROLE_WAREHOUSE]))
		{
			$url = '/order/order_list.php';
		}
		elseif (isset($roleLevels[Conf_Admin::ROLE_FINANCE]))
		{
			$url = '/crm/customer_list.php';
		}
        elseif (isset($roleLevels[Conf_Admin::ROLE_AFTER_SALE_NEW]))
        {
            $url = '/aftersale/list.php';
        }
        else
        {
		    $url = '/user/chgpwd.php';
        }
        
        // h5的老角色问题导致，暂且直接跳工单页面
        $url = '/aftersale/list.php';

		return $url;
	}
}
