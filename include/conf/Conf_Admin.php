<?php

/**
 * 后台配置
 * 权限和岗位是两码事。 但是目前权限是基于岗位的。 岗位粒度为主，然后再根据人的粒度微调。
 */
class Conf_Admin
{
	/**
	 * 管理员账号：自动管理员
	 */
	const ADMINOR_AUTO = 999;
    
    /**
     * HC员工最大ID, 加盟商（>=100000）.
     */
    const SELF_STAFF_SUID = 99999;

	/**
	 * 角色定义
	 */
	const ROLE_ADMIN = 1,   //系统管理员(所有权限都有)
		ROLE_OP = 2,        //运营人员
		ROLE_SALES = 3,     //销售人员
		ROLE_WAREHOUSE = 4, //库房人员
		ROLE_DRIVER = 5,    //司机
		ROLE_LM = 6,        //物流管理
		ROLE_BUYER = 7,     //采购
		ROLE_FINANCE = 8,   //财务
		ROLE_CS = 9,        //客服
		ROLE_CITY_ADMIN = 10,       //城市经理
		ROLE_YUNNIAO = 11,  //云鸟调度
		ROLE_ASSIS_SALER = 12,  //销售助理
		ROLE_AFTER_SALE = 13,  //售后
		ROLE_EDITOR = 14;  //商品编辑
    
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
    
    
    const ROLE_CITY_ADMIN_NEW = 'CSJL1';                        //城市经理
    const ROLE_SALES_DIRECTOR = 'XSZJ1';                        //销售总监
    const ROLE_SALES_MANAGER  = 'DXJL1';                        //销售经理
    
    const ROLE_SALES_KA = 'PTKA1';                              //销售KA
    
    
    const ROLE_STATISTICS_OVERVIEW = 'statistics_overview';     //查看数据概览

    public static function baseRkeyOfSalesLeader()
    {
        return array('DXZZ1', 'QDTZZG1', 'KAZG1');  //电销主管，渠道主管，ka主管
    }
    
	const LEVEL_0 = 0,  //普通
		LEVEL_1 = 1,    //经理,组长
		LEVEL_2 = 2;    //总监
    
	public static $Role_Descs = array(
		self::ROLE_ADMIN => '管理员',
		self::ROLE_OP => '运营',
		self::ROLE_SALES => '销售',
		self::ROLE_WAREHOUSE => '库管',
		self::ROLE_DRIVER => '司机',
		self::ROLE_LM => '调度',
		self::ROLE_BUYER => '采购',
		self::ROLE_FINANCE => '财务',
		self::ROLE_CS => '客服',
        self::ROLE_CITY_ADMIN => '城市经理',
        self::ROLE_YUNNIAO => '云鸟调度',
		self::ROLE_ASSIS_SALER => '销售助理',
		self::ROLE_AFTER_SALE => '售后',
		self::ROLE_EDITOR => '商品编辑',
	);

    /**
     * 所有的销售组长的suid,用于修改销售人员的leaders_suid
     */
    public static $LEADERS_SUID = array(
        1463 => '测试销售组长',
        1068 => '郭亚翔',
        1118 => '黄行',
        1131 => '李现里',
        1122 => '张明良',
        1156 => '马奇',
        1094 => '殷存和',
        1527 => '龚相颖',
        1526 => '刘学峰',
        1525 => '王琳娜',
        1008 => '刘江涛',
        1039 => '汪先平',
        1454 => '肖亮东',
        1426 => '奉铁钢',
        1073 => '王建伟',
        1600 => '吕圆梦廊坊',
        1128 => '马金轩',
        1575 => '冯德安',
        1559 => '程乐',
        1547 => '李双双',
        1595 => '杨静',
        1532 => '杨磊',
        1540 => '王小红',
    );
	public static $SALE_GROUP_NAME = array(
		1068 => array('name' => '麒麟队', 'goal' => 300),
		1076 => array('name' => '至尊队', 'goal' => 300),
		1139 => array('name' => '火狼队', 'goal' => 400),
		1118 => array('name' => '无敌队', 'goal' => 400),
        1156 => array('name' => '拓疆队', 'goal' => 400),
        1122 => array('name' => 'KA', 'goal' => 400),
        1128 => array('name' => '骑士', 'goal' => 150),
		1131 => array('name' => '津门虎', 'goal' => 150),
	);
	/**
	 * 职位类型
	 */
	const JOB_KIND_BDS_SALE = 1, JOB_KIND_PARTTIME = 2, JOB_KIND_TELE_SALE = 3, JOB_KIND_KA_SALE = 4, JOB_KIND_BMS_SALE = 5,

		JOB_KIND_SYS = 99;

	public static $JOB_KIND_DESC = array(
		//self::JOB_KIND_BDS_SALE => 'BDS',
		self::JOB_KIND_PARTTIME => '兼职',
		self::JOB_KIND_TELE_SALE => '电销',
		self::JOB_KIND_KA_SALE => 'KA',
		self::JOB_KIND_BMS_SALE => 'BMS',
		self::JOB_KIND_SYS => '管理员',
	);

	//特殊的销售角色：不仅可以看自己的客户，也能看别人的客户
	public static $SUPER_SALES = array(
		1001,
		1002,
		1003,
		1004,
		1029,
		1066,
		1067,
		1078
	);

	/**
	 * 角色名称
	 */
	private static $ROLES_NAME = array(
		self::ROLE_ADMIN => '系统管理员',
		self::ROLE_OP => '运营',
		self::ROLE_SALES => '销售',
		self::ROLE_WAREHOUSE => '库房',
		self::ROLE_DRIVER => '司机',
		self::ROLE_LM => '物流',
		self::ROLE_BUYER => '采购',
		self::ROLE_FINANCE => '财务',
		self::ROLE_CS => '客服',
		self::ROLE_CITY_ADMIN => '城市经理',
		self::ROLE_YUNNIAO => '云鸟调度',
		self::ROLE_ASSIS_SALER => '销售助理',
		self::ROLE_AFTER_SALE => '售后',
		self::ROLE_EDITOR => '产品编辑',
	);

	public static function getRoles()
	{
		return self::$ROLES_NAME;
	}

    public static function getLeaders()
    {
        return self::$LEADERS_SUID;
    }

	public static function getFuncAuth()
	{
		return self::$FUNCS;
	}


	private static $ROLE_MODULE_PAGES = array(
		Conf_Admin::ROLE_SALES_NEW => array(//销售
			0 => array( //LEVEL_0
                'aftersale' => array('*'),
				'order' => array('order_list', 'refund_list', 'order_list_not_pay', 'customer_list_cs', 'quick_order_list'),
				'user' => array('*'),
				'crm' => array('*'),
                
//				'shop' => array('product_list', 'product_search'),
//				'crm2' => array(
//					'customer_list',
//					'performance',
//					'new_customer',
//					'customer_tracking_list',
//					'apply_coupon_list',
//					'coupon_list',
//					'need_tracking_customers',
//				),
//				'statistics' => array('receivables'),
			),
//			Conf_Admin::LEVEL_2 => array(
//                'aftersale' => array('*'),
//				'order' => array('*'),
//				'shop' => array('product_list', 'product_search'),
//				'crm2' => array('*'),
//				'user' => array('*'),
//			),
		),
//		Conf_Admin::ROLE_ASSIS_SALER_NEW => array(//销售助理
//			Conf_Admin::LEVEL_0 => array(
//                'aftersale' => array('*'),
//				'crm2' => array('customer_list','performance'),
//                'finance' => array('customer_list'),
//			),
//		),
//		Conf_Admin::ROLE_OP_NEW => array(//运营人员
//            Conf_Admin::LEVEL_0 => array(
//                'aftersale' => array('*'),
//                'order' => array('*'),
//                'shop' => array('*'),
//				'statistics' => array('*'),
//                'activity' => array('*'),
//                'user' => array('*'),
//            ),
//			Conf_Admin::LEVEL_1 => array(
//				'crm2' => array('*'),
//				'order' => array('*'),
//				'warehouse' => array('*'),
//				'finance' => array('*'),
//				'shop' => array('*'),
//				'statistics' => array('*'),
//				'logistics' => array('*'),
//				'user' => array('*'),
//				//'cs' => array('*'),
//				'activity' => array('*'),
//				'aftersale' => array('*'),
//			)
//		),
//		Conf_Admin::ROLE_WAREHOUSE_NEW => array(//库房人员
//			Conf_Admin::LEVEL_0 => array(
//                'aftersale' => array('*'),
//				'order' => array('order_list', 'refund_list', 'delivery_community', 'picking_list'),
//				'warehouse' => array('supplier_list', 'stock_list', 'location_list', 'location_export', 'in_order_list', 'stock_in_lists', 'stockin_refund_list', 'stock_alert', 'stock_history', 'stock_shift_list', 'stock_warning'),
//				'shop' => array('product_search', 'product_list', 'sku_list', 'model_list', 'brand_list'),
//                'user' => array('*'),
//			)
//		),
//		Conf_Admin::ROLE_CS_NEW => array(//客服
//			Conf_Admin::LEVEL_0 => array(
//                'aftersale' => array('*'),
//				//				'crm2' => array('coupon_list'),
//				'order' => array('order_list', 'refund_list', 'customer_list_cs', 'admin_task_list', 'delivery_community', 'customer_construction_list', 'order_action_log', 'coupon_list', 'quick_order_list'),
//				'shop' => array('*'),
//				'invalid_product' => array('*'),
//				'user' => array('*'),
//				'cs' => array('*'),
//				'crm2' => array('customer_fb_list'),
//			)
//		),
//		Conf_Admin::ROLE_LM_NEW => array( //物流管理
//			Conf_Admin::LEVEL_0 => array(
//                'aftersale' => array('*'),
//				'order' => array('order_list', 'picking_list', 'driver_order_list', 'carrier_order_list', 'delivery_community', 'driver_order_statement'),
//				'shop' => array('product_search', 'product_list'),
//				'user' => array('*'),
//				'logistics' => array('*'),
//				'finance' => array('coopworker_bill_list'),
//				'statistics' => array('export'),
//			)
//		),
//		Conf_Admin::ROLE_BUYER_NEW => array(//采购
//			Conf_Admin::LEVEL_0 => array(
//                'aftersale' => array('*'),
//				'order' => array('order_list', 'refund_list', 'sale_product_summary'),
//				'shop' => array('*'),
//				'warehouse' => array('*'),
//				'invalid_product' => array('*'),
//				'statistics' => array('stock_in', 'sku_in_out', 'hot', 'cate', 'stock', 'export', 'stock_sku'),
//				'user' => array('*'),
//				'purchase' => array('*'),
//			),
//		),
//		Conf_Admin::ROLE_FINANCE_NEW => array(  //财务
//			Conf_Admin::LEVEL_0 => array(
//                'aftersale' => array('*'),
//				'crm2' => array('coupon_list'),
//				'order' => array(
//					'order_list',
//					'refund_list',
//					'sale_detail',
//					'special_cate_account',
//					'customer_list_cs',
//					'driver_order_list',
//                    'driver_order_statement',
//					'carrier_order_list',
//                    'check_statement',
//                    'auto_receipt',
//				),
//				'shop' => array('product_search', 'product_list'),
//				'warehouse' => array('stock_list', 'supplier_list', 'in_order_list', 'stock_in_lists','purchase_for_finance', 'stock_history'),
//				'finance' => array('*'),
//				'logistics' => array('*'),
//				'user' => array('*'),
//			)
//		),
//		Conf_Admin::ROLE_CITY_ADMIN => array(  //城市经理
//			Conf_Admin::LEVEL_0 => array(
//                'aftersale' => array('*'),
//				'order' => array('order_list', 'refund_list', 'driver_order_list', 'carrier_order_list'),
//				'shop' => array('sku_list', 'product_list'),
//				'warehouse' => array('stock_list', 'supplier_list', 'in_order_list', 'stock_in_lists', 'stockin_refund_list', 'stock_history'),
//				'logistics' => array('driver', 'carrier', 'need_send_order_list'),
//				'user' => array('*'),
//			)
//		),
//		Conf_Admin::ROLE_YUNNIAO => array(  //云鸟调度
//			Conf_Admin::LEVEL_0 => array(
//                'aftersale' => array('*'),
//				'logistics' => array('driver', 'driver_order_list'),
//				'user' => array('chgpwd'),
//				'order' => array('driver_order_list'),
//			)
//		),
//		Conf_Admin::ROLE_AFTER_SALE_NEW => array(//售后
//			Conf_Admin::LEVEL_0 => array(
//				'aftersale' => array('*'),
//				'order' => array('order_list', 'refund_list', 'exchanged_list', 'customer_list_cs', 'quick_order_list', 'order_action_log'),
//                'shop' => array('*'),
//				'user' => array('*'),
//                'crm2' => array('customer_fb_list'),
//			),
//             Conf_Admin::LEVEL_1 => array(
//                 'statistics' => array('export'),
//             )
//		),
//		Conf_Admin::ROLE_EDITOR_NEW => array(//商品编辑
//			Conf_Admin::LEVEL_0 => array(
//                'aftersale' => array('*'),
//				'order' => array('order_list', 'delivery_community'),
//				'shop' => array('*'),
//				'invalid_product' => array('*')
//			),
//		),
	);

//	private static $ROLE_FUNCS = array(
//		Conf_Admin::ROLE_SALES => array(//销售
//			Conf_Admin::LEVEL_0 => array()
//		),
//		Conf_Admin::ROLE_WAREHOUSE => array(//库房人员
//			Conf_Admin::LEVEL_0 => array()
//		),
//		Conf_Admin::ROLE_CS => array(//客服
//			Conf_Admin::LEVEL_0 => array(
//				'order:order_list:delete_product',
//			)
//		),
//		Conf_Admin::ROLE_LM => array( //物流管理
//			Conf_Admin::LEVEL_0 => array()
//		),
//		Conf_Admin::ROLE_BUYER => array(//采购
//			Conf_Admin::LEVEL_0 => array()
//		),
//		Conf_Admin::ROLE_FINANCE => array(  //财务
//			Conf_Admin::LEVEL_0 => array()
//		),
//	);


	public static function getMODULES($suid, $suser, $modules)
	{
		//未登录
		if (empty($suid) && empty($suser))
			return array();
        
		// 获取该角色可以访问的module,page列表
		$roleLevels = Admin_Role_Api::getRoleLevels($suid, $suser);
		$myModulePages = array();
		foreach ($roleLevels as $role => $level)
		{
			$roleConf = self::$ROLE_MODULE_PAGES[$role];
			foreach ($roleConf as $levelTmp => $pagesTmp)
			{
				if ($level < $levelTmp)
					continue;
				foreach ($pagesTmp as $module => $pageTmpArr)
				{
					$myModulePages[$module] = array_merge((array)$myModulePages[$module], $pageTmpArr);
				}
			}
		}

		// 获取module,page的定义
		foreach ($modules as $module => $item)
		{
			if (array_key_exists('extra_uids', $item) && in_array($suid, $item['extra_uids']))
			{
				continue;
			}

			//看不到该模块
			if (!isset($myModulePages[$module]))
			{
				unset($modules[$module]);
				continue;
			}
			//该模块可以看全部页面
			if (in_array('*', $myModulePages[$module]))
			{
				continue;
			}
			//该模块看不到某些页面
			$pages = $item['pages'];
			foreach ($pages as $idx => $pageItem)
			{
				$page = $pageItem['page'];
				if (!in_array($page, $myModulePages[$module]))
				{
					unset($modules[$module]['pages'][$idx]);
				}
			}
			//重置数组下标
			$modules[$module]['pages'] = array_values($modules[$module]['pages']);
		}
        
        // 库房权限：在锁库时库房人员看不到库存和出入库历史
        //锁库判断
        $lockedRet = Conf_Warehouse::isLockedWarehouse($suser['wid']);
        
        if (array_key_exists(Conf_Admin::ROLE_WAREHOUSE, $roleLevels) && $lockedRet['st'])
        {
            $exceptPages = array(
                'warehouse' => array('stock_list', 'location_list', 'stock_history', 'location_export', 'stock_warning'),
            );

            foreach($exceptPages as $_module => $_pages)
            {
                if (!array_key_exists($_module, $modules)) continue;

                foreach($modules[$_module]['pages'] as $k => $info)
                {
                    if (in_array($info['page'], $_pages))
                    {
                        unset($modules[$_module]['pages'][$k]);
                    }
                }
                $modules[$_module]['pages'] = array_values($modules[$_module]['pages']);
            }
        }
        
		return $modules;
	}

	//除了xx和xx，这些id的管理员也会收到后天新订单的提示
	public static $NEW_ORDER_REMIND_SUIDS = array(
		1004
	);

	/**
	 * 库房拣货人员分组.
	 *
	 * 库房人员可能配有系统账号，suid填0
	 */
	public static $PICKING_GROUPS = array(
		Conf_Warehouse::WID_3 => array(
			'A' => array('suid' => '1048', 'sname' => '李富国'),
			'B' => array('suid' => '1079', 'sname' => '于海涛'),
		),
	);

	public static $DEMO_SUIDS = array(
		999
	);

    public static $SUPER_ADMINER = array(
        1029, 1004, 1254,1289,1254,1679
    );

    public static $WARNING_MOBILES = array(
        '18910053781', '13810403104'
    );

    public static $CERTIFICATE_DEAL_SUID = 1254;
    public static function getCertificateDealSuid($cityId)
    {
        $arr = array(
            Conf_City::BEIJING => 1073,
            Conf_City::LANGFANG => 1600,
            Conf_City::TIANJIN => 1118,
            Conf_City::CHONGQING => 1588,
        );

        if (!empty($arr[$cityId]))
        {
            return $arr[$cityId];
        }

        return 1073;
    }
}
