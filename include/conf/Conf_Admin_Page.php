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
                        array('key' => '/crm2/customer_list', 'name' => '查看列表',),
                        array('key' => '/crm2/new_customer', 'name' => '添加客户',),
                        array('key' => 'crm2_update_customer_address', 'name' => '编辑客户地址',),
                        array('key' => 'crm2_update_customer_suser', 'name' => '编辑客户销售和录入人',),
                        array('key' => 'crm2_update_customer_level_for_saler', 'name' => '编辑客户级别',),
                        array('key' => 'crm2_update_customer_detail_info', 'name' => '查看用户认证/详细信息',),
                        array('key' => 'crm2_update_customer_other_info', 'name' => '高级编辑'),
                        array('key' => '/crm2/download', 'name' => '下载',),
                        array('key' => '/crm2/download2', 'name' => '下载无账期用户欠款信息',),
                        array('key' => '/crm2/customer_detail', 'name' => '详情',),
                        array('key' => '/crm2/edit_customer', 'name' => '编辑',),
                        array('key' => '/crm2/coupon_list', 'name' => '优惠券',),
                        array('key' => '/crm2/ajax/send_vip_coupon', 'name' => '发放VIP优惠券',),
                        array('key' => '/crm2/ajax/send_coupon_temporary', 'name' => '临时发放优惠券'),
                        array('key' => '/crm2/ajax/apply_coupon', 'name' => '申请优惠券',),
                        array('key' => '/crm2/ajax/get_customer_tracking', 'name' => '查看回访记录'),
                        array('key' => '/crm2/edit_customer_tracking', 'name' => '编辑回访记录',),
                        array('key' => 'hc_crm2_into_private', 'name' => '收入私海'),
                        array('key' => 'hc_crm2_into_public', 'name' => '放回公海'),
                        array('key' => 'hc_crm2_into_inner', 'name' => '收回内海'),
                        array('key' => 'hc_crm2_into_invalid', 'name' => '标记无效客户'),
                        array('key' => 'hc_crm2_ppii_super', 'name' => '公/私/内超级'),
                        // 下线页面，查看使用
                        array('key' => '/crm2/customer_tracking_list', 'name' => '回访列表'),
                        array('key' => '/crm2/search_plan_history', 'name' => '户型搜索记录',),
                    ),
                ),
                array(
                    'name' => '账期账额',
                    'url' => '/crm2/customer_payment.php',
                    'page' => 'customer_payment',
                    'key' => '/crm2/customer_payment',
                    'buttons' => array(
                        array('key' => '/crm2/customer_payment', 'name' => '查看列表')
                    ),
                ),
                array(
                    'name' => '销售客户转接',
                    'url' => '/crm2/sales_customer_flow.php',
                    'key' => 'sales_customer_flow',
                    'page' => 'sales_customer_flow',
                    'buttons' => array(
                        array('key' => '/crm2/sales_customer_flow', 'name' => '销售客户转接'),
                        array('key' => '/crm2/ajax/sales_customer_flow_update', 'name' => '提交转移客户'),
                        array('key' => '/crm2/ajax/sales_customer_flow_execute', 'name' => '修改转移客户')
                    ),
                ),
				array(
                    'name' => '企业列表',
                    'url' => '/crm2/business_list.php',
                    'page' => 'business_list',
                    'key' => '/crm2/business_list',
                    'buttons' => array(
                        array('key' => '/crm2/business_list', 'name' => '查看列表',),
                        array('key' => '/crm2/add_business', 'name' => '添加企业号',),
                        array('key' => '/crm2/business_detail', 'name' => '详情',),
                        array('key' => '/crm2/edit_business', 'name' => '编辑',),
                        array('key' => 'hc_reset_business_passwd', 'name' => '重置密码'),
                        array('key' => '/crm2/ajax/modify_business', 'name' => '绑/解客户',),
                    ),
                ),
                array(
                    'name' => '添加客户',
                    'url' => '/crm2/new_customer.php',
                    'page' => 'new_customer',
                    'key' => '/crm2/new_customer',
                    'buttons' => array(
                        array('key' => '/crm2/new_customer', 'name' => '查看列表',),
                    ),
                ),
                array(
                    'name' => '合并客户',
                    'url' => '/crm2/merge_customer.php',
                    'page' => 'merge_customer',
                    'key' => '/crm2/merge_customer',
                    'buttons' => array(
                        array('key' => '/crm2/merge_customer', 'name' => '合并客户',),
                    ),
                ),
                array(
                    'name' => '应回访客户',
                    'url' => '/crm2/need_tracking_customers.php',
                    'page' => 'need_tracking_customers',
                    'key' => '/crm2/need_tracking_customers',
                    'buttons' => array(
                        array('key' => '/crm2/need_tracking_customers', 'name' => '客户回访',),
                    ),
                ),
                array(
                    'name' => '优惠审批',
                    'url' => '/crm2/apply_coupon_list.php?status=1',
                    'page' => 'apply_coupon_list',
                    'key' => '/crm2/apply_coupon_list',
                    'buttons' => array(
                        array('key' => '/crm2/apply_coupon_list', 'name' => '优惠审批',),
                    ),
                ),
                array(
                    'name' => '问题反馈记录',
                    'url' => '/crm2/customer_fb_list.php',
                    'page' => 'customer_fb_list',
                    'key' => '/crm2/customer_fb_list',
                    'buttons' => array(
                        array('key' => '/crm2/customer_fb_list', 'name' => '查看列表',),
                        array('key' => '/crm2/ajax/ensure_customer_fb', 'name' => '确认反馈',),
                    ),
                ),
                array(
                    'name' => '预存客户',
                    'url' => '/crm2/prepay_customer.php',
                    'page' => 'prepay_customer',
                    'key' => '/crm2/prepay_customer',
                    'buttons' => array(
                        array('key' => '/crm2/prepay_customer', 'name' => '查看列表',),
                        array('key' => '/crm2/download_prepay_customer', 'name' => '下载',),
                    ),
                ),
                array(
                    'name' => '开票申请',
                    'url' => '/crm2/invoice_list.php',
                    'page' => 'invoice_list',
                    'key' => '/crm2/invoice_list',
                    'buttons' => array(
                        array('key' => '/crm2/invoice_list', 'name' => '查看列表'),
                        array('key' => '/crm2/edit_invoice', 'name' => '编辑'),
                        array('key' => '/crm2/show_invoice', 'name' => '查看'),
                        array('key' => '/crm2/ajax/audit_output_invoice', 'name' => '审核'),
                        array('key' => '/crm2/ajax/rebut_output_invoice', 'name' => '驳回'),
                        array('key' => '/crm2/ajax/delete_output_invoice', 'name' => '删除'),
                    ),
                ),
                array(
                    'name' => '销售日程',
                    'url' => '/crm2/sale_schedule_list.php',
                    'page' => 'sale_schedule_list',
                    'key' => '/crm2/sale_schedule_list',
                    'buttons' => array(
                        array('key' => '/crm2/sale_schedule_list', 'name' => '查看列表'),
                        array('key' => '/crm2/ajax/save_sale_schedule', 'name' => '编辑'),
                    ),
                ),
                array(
                    'name' => '客户拜访',
                    'url' => '/crm2/customer_visit_list.php',
                    'page' => 'customer_visit_list',
                    'key' => '/crm2/customer_visit_list',
                    'buttons' => array(
                        array('key' => '/crm2/customer_visit_list', 'name' => '查看列表'),
                        array('key' => '/crm2/edit_customer_visit', 'name' => '编辑'),
                        array('key' => '/crm2/show_customer_visit', 'name' => '查看'),
                    ),
                ),
                array(
                    'name' => '销售主页',
                    'url' => '/crm2/sales_homepage.php',
                    'page' => 'sales_homepage',
                    'key' => '/crm2/sales_homepage',
                    'buttons' => array(
                        array('key' => '/crm2/sales_homepage', 'name' => '查看销售主页'),
                    ),
                ),
                array(
                    'name' => '我的管理台',
                    'url' => '/crm2/manager_page.php',
                    'key' => 'manager_page',
                    'page' => '/crm2/manager_page',
                    'buttons' => array(
                        array('key' => '/crm2/manager_page', 'name' => '查看管理台'),
                    ),
                ),
                array(
                    'name' =>  '客户积分日志',
                    'url' => '/crm2/customer_point_log_list.php',
                    'page' => 'customer_point_log_list',
                    'key' => '/crm2/customer_point_log_list',
                    'buttons' => array(
                        array('key' => '/crm2/customer_point_log_list', 'name' => '查看列表'),
                        array('key' => '/crm2/edit_user_point', 'name' => '新增积分'),
                    ),
                ),
                array(
                    'name' =>  '户型搜索记录',
                    'url' => '/crm2/search_plan_list.php',
                    'page' => 'search_plan_list',
                    'key' => '/crm2/search_plan_list',
                    'buttons' => array(
                        array('key' => '/crm2/search_plan_list', 'name' => '查看列表'),
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
                        array('key' => '/order/order_list', 'name' => '列表'),
                        array('key' => '/order/order_detail', 'name' => '详情'),
                        array('key' => '/order/edit_order', 'name' => '编辑'),
                        array('key' => '/order/order_print', 'name' => '打印'),
                        array('key' => '/order/ajax/delete_order', 'name' => '删除'),
                        array('key' => '/order/ajax/reset_order', 'name' => '恢复'),
                        array('key' => '/order/ajax/cancel_order', 'name' => '取消'),
                        array('key' => '/order/order_action_log', 'name' => '订单日志'),
                        array('key' => '/order/ajax/chg_saler', 'name' => '改订单销售'),
                        array('key' => '/order/ajax/guaranteed_order', 'name' => '担保订单'),
                        array('key' => '/order/ajax/save_service', 'name' => '详情页改搬运'),
                        array('key' => '/order/edit_community_fee', 'name' => '校验小区距离'),
                        array('key' => '/logistics/ajax/get_driver_carrier', 'name' => '查询司/搬工人'),
                        array('key' => 'hc_order_add_driver', 'name' => '加司机'),
                        array('key' => 'hc_order_del_driver', 'name' => '删司机'),
                        array('key' => 'hc_order_add_del_carrier', 'name' => '加/删搬运工'),
                        array('key' => 'hc_order_edit_coopworker', 'name' => '编辑司/搬费用'),
                        array('key' => 'hc_order_paid_coopworker', 'name' => '支付司/搬费用'),
                        array('key' => 'hc_order_check_cost', 'name' => '查看商品成本'),
                        array('key' => 'hc_order_check_customer', 'name' => '查看客户信息'),
                        array('key' => 'hc_edit_order_note', 'name' => '编辑备注'),
                        array('key' => 'hc_order_cs_confirm', 'name' => '客服确认'),
                        array('key' => 'hc_order_delivred', 'name' => '出库'),
                        array('key' => 'hc_order_bill_back', 'name' => '回单'),
                        array('key' => '/finance/ajax/save_money_in', 'name' => '订单收款'),
                        array('key' => '/order/ajax/modify_by_finance', 'name' => '运营优惠调整'),
                        array('key' => '/order/ajax/modify_by_operator', 'name' => '运/搬费调整'),
                        array('key' => '/order/supplement_order', 'name' => '创建补单'),
                        array('key' => '/order/ajax/set_order_print', 'name' => '通知库存打印'),
                        array('key' => '/order/ajax/set_part_paid', 'name' => '设为部分收款'),
                        array('key' => '/order/ajax/refund_and_delete', 'name' => '退款并删除'),
                        array('key' => '/order/ajax/transfer_amount_in_order', 'name' => '转余额'),
                        array('key' => '/order/ajax/change_order_city', 'name' => '切换城市'),
                        array('key' => '/order/download', 'name' => '下载'),
                        array('key' => '/order/ajax/get_order_products_warehouse', 'name' => '查看库存明细'),
                        array('key' => 'hc_check_profit', 'name' => '查看毛利毛收入'),
                        array('key' => '/order/ajax/save_order_sale_privilege', 'name' => '销售优惠调整'),
                    ),
                ),
                array(
                    'name' => '客户订单',
                    'url' => '/order/customer_order_list.php',
                    'page' => 'customer_order_list',
                    'key' => '/order/customer_order_list',
                    'buttons' => array(
                        array('key' => '/order/customer_order_list', 'name' => '列表'),
                        array('key' => '/order/order_detail', 'name' => '详情'),
                        array('key' => '/order/edit_order', 'name' => '编辑'),
                        array('key' => '/order/order_print', 'name' => '打印'),
                        array('key' => '/order/ajax/delete_order', 'name' => '删除'),
                        array('key' => '/order/ajax/reset_order', 'name' => '恢复'),
                        array('key' => '/order/ajax/cancel_order', 'name' => '取消'),
                        array('key' => '/order/order_action_log', 'name' => '订单日志'),
                        array('key' => '/order/ajax/chg_saler', 'name' => '改订单销售'),
                        array('key' => '/order/ajax/guaranteed_order', 'name' => '担保订单'),
                        array('key' => '/order/ajax/save_service', 'name' => '详情页改搬运'),
                        array('key' => '/order/edit_community_fee', 'name' => '校验小区距离'),
                        array('key' => '/logistics/ajax/get_driver_carrier', 'name' => '查询司/搬工人'),
                        array('key' => 'hc_order_add_driver', 'name' => '加司机'),
                        array('key' => 'hc_order_del_driver', 'name' => '删司机'),
                        array('key' => 'hc_order_add_del_carrier', 'name' => '加/删搬运工'),
                        array('key' => 'hc_order_edit_coopworker', 'name' => '编辑司/搬费用'),
                        array('key' => 'hc_order_paid_coopworker', 'name' => '支付司/搬费用'),
                        array('key' => 'hc_order_check_cost', 'name' => '查看商品成本'),
                        array('key' => 'hc_order_check_customer', 'name' => '查看客户信息'),
                        array('key' => 'hc_edit_order_note', 'name' => '编辑备注'),
                        array('key' => 'hc_order_cs_confirm', 'name' => '客服确认'),
                        array('key' => 'hc_order_delivred', 'name' => '出库'),
                        array('key' => 'hc_order_bill_back', 'name' => '回单'),
                        array('key' => '/finance/ajax/save_money_in', 'name' => '订单收款'),
                        array('key' => '/order/ajax/modify_by_finance', 'name' => '运营优惠调整'),
                        array('key' => '/order/ajax/modify_by_operator', 'name' => '运/搬费调整'),
                        array('key' => '/order/supplement_order', 'name' => '创建补单'),
                        array('key' => '/order/ajax/set_order_print', 'name' => '通知库存打印'),
                        array('key' => '/order/ajax/set_part_paid', 'name' => '设为部分收款'),
                        array('key' => '/order/ajax/refund_and_delete', 'name' => '退款并删除'),
                        array('key' => '/order/ajax/transfer_amount_in_order', 'name' => '转余额'),
                        array('key' => '/order/ajax/change_order_city', 'name' => '切换城市'),
                        array('key' => '/order/download', 'name' => '下载'),
                        array('key' => '/order/ajax/get_order_products_warehouse', 'name' => '查看库存明细'),
                        array('key' => 'hc_check_profit', 'name' => '查看毛利毛收入'),
                    ),
                ),
				array(
                    'name' => '拍照下单',
                    'url' => '/order/quick_order_list.php',
                    'page' => 'quick_order_list',
                    'key' => '/order/quick_order_list',
                    'buttons' => array(
                        array('key' => '/order/quick_order_list', 'name' => '查看列表',),
                        array('key' => '/order/ajax/ensure_quick_order', 'name' => '确认',),
                    ),
                ),
				array(
                    'name' => '销售提单', 
                    'url' => '/aftersale/list.php?fb_type=1&type=2&typeid=1&status=2,3', 
                    'page' => 'aftersale_list',
                    'key' => '/aftersale/list',
                    'buttons' => array(
                        array('key' => 'hc_saler_submit_orders', 'name' => '列表'),
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
                        array('key' => '/order/add_order_logistics_h5', 'name' => '手机下单'),
                        array('key' => '/crm2/ajax/reset_pass', 'name' => '重置密码'),
                        array('key' => '/crm2/ajax/save_customer_high', 'name' => '高级编辑'),
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
                array(
                    'name' => '城市优惠',
                    'url' => '/order/city_privilege.php',
                    'page' => '/order/city_privilege',
                    'key' => '/order/city_privilege',
                    'buttons' => array(
                        array('key' => '/order/city_privilege', 'name' => '列表'),
                        array('key' => '/order/city_privilege_show', 'name' => '详情'),
                        array('key' => '/order/ajax/change_city_privilege', 'name' => '修改优惠')
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
                        array('key' => '/shop/ajax/delete_product', 'name' => '删除',),
                        array('key' => '/warehouse/stock_history', 'name' => '库存',),
                        array('key' => '/shop/edit_sortby', 'name' => '更改排序',),
                        array('key' => '/shop/processing_products', 'name' => '加工商品'),
                        array('key' => '/shop/processed_list', 'name' => '加工列表'),
                        array('key' => 'edit_shop_product_price', 'name' => '编辑商品价格'),
                        array('key' => 'show_product_cost', 'name' => '商品成本'),
                        array('key' => '/shop/ajax/purchase_type', 'name' => '采购类型'),
                        array('key' => '/shop/ajax/save_pick_note', 'name' => '修改包装含量'),
                    ),
                ),
				array(
                    'name' => 'sku列表',
                    'url' => '/shop/sku_list.php',
                    'page' => 'sku_list',
                    'key' => '/shop/sku_list.php',
                    'buttons' => array(
                        array('key' => '/shop/sku_list', 'name' => '查看列表',),
                        array('key' => '/shop/sku_search', 'name' => '搜索',),
                        array('key' => '/shop/edit_sku', 'name' => '编辑',),
                        array('key' => '/shop/ajax/add_sku_4_relation', 'name'=>'编辑sku间关系'),
                        array('key' => 'shop_check_sku_sale_detail', 'name' => '查看销售明细',),
                        array('key' => '/shop/add_product', 'name' => '添加/查看商品',),
                    ),
                ),
				array(
                    'name' => '添加sku',
                    'url' => '/shop/add_sku.php',
                    'page' => 'add_sku',
                    'key' => '/shop/add_sku',
                    'buttons' => array(
                        array('key' => '/shop/add_sku', 'name' => '添加sku',),
                    ),
                ),
				array(
                    'name' => '型号管理',
                    'url' => '/shop/model_list.php',
                    'page' => 'model_list',
                    'key' => '/shop/model_list',
                    'buttons' => array(
                        array('key' => '/shop/model_list', 'name' => '列表'),
                        array('key' => '/shop/ajax/save_model', 'name' => '编辑',),
                        array('key' => '/shop/ajax/delete_model', 'name' => '删除',),
                    ),
                ),
				array(
                    'name' => '品牌管理',
                    'url' => '/shop/brand_list.php',
                    'page' => 'brand_list',
                    'key' => '/shop/brand_list',
                    'buttons' => array(
                        array('key' => '/shop/brand_list', 'name' => '列表'),
                        array('key' => '/shop/edit_brand', 'name' => '编辑',),
                        array('key' => '/shop/ajax/delete_brand', 'name' => '删除',),
                    ),
                ),
                array (
                    'name' => '成本队列',
                    'url' => '/shop/cost_queue.php',
                    'page' => 'cost_queue',
                    'key' => '/shop/cost_queue',
                    'buttons' => array(
                        array('key' => '/shop/cost_queue', 'name' => '成本队列')
                    ),
                ),
			)
		),
        'invalid_product' => array(
            'name' => '可能有问题的商品',
            'display' => 'hidden',
            'pages' => array(
                array(
                    'name' => '没图片',
                    'url' => '/invalid_product/no_picture.php',
                    'page' => 'no_picture',
                    'key' => '/invalid_product/no_picture',
                    'buttons' => array(
                        array('key' => '/invalid_product/no_picture', 'name' => '查看列表',),
                    ),
                ),
                array(
                    'name' => '没成本',
                    'url' => '/invalid_product/no_cost.php',
                    'page' => 'no_cost',
                    'key' => '/invalid_product/no_cost',
                    'buttons' => array(
                        array('key' => '/invalid_product/no_cost', 'name' => '查看列表',),
                    ),
                ),
                array(
                    'name' => '没品牌',
                    'url' => '/invalid_product/no_brand.php',
                    'page' => 'no_brand',
                    'key' => '/invalid_product/no_brand',
                    'buttons' => array(
                        array('key' => '/invalid_product/no_brand', 'name' => '查看列表',),
                    ),
                ),
                array(
                    'name' => '没型号',
                    'url' => '/invalid_product/no_model.php',
                    'page' => 'no_model',
                    'key' => '/invalid_product/no_model',
                    'buttons' => array(
                        array('key' => '/invalid_product/no_model', 'name' => '查看列表',),
                    ),
                ),
                array(
                    'name' => '名字重复',
                    'url' => '/invalid_product/duplicate_name.php',
                    'page' => 'duplicate_name',
                    'key' => '/invalid_product/duplicate_name',
                    'buttons' => array(
                        array('key' => '/invalid_product/duplicate_name', 'name' => '查看列表',),
                    ),
                ),
                array(
                    'name' => '最新',
                    'url' => '/invalid_product/new.php',
                    'page' => 'new',
                    'key' => '/invalid_product/new',
                    'buttons' => array(
                        array('key' => '/invalid_product/new', 'name' => '查看列表',),
                    ),
                ),
            ),
        ),
        'logistics' => array(
            'name' => '物流管理',
            'pages' => array(
                array(
                    'name' => '排线地图',
                    'url' => '/logistics/order_line.php',
                    'page' => 'order_line',
                    'key' => '/logistics/order_line',
                    'buttons' => array(
                        array('key' => '/logistics/order_line', 'name' => '查看',),
                        array('key' => '/logistics/ajax/save_order_line', 'name' => '确认排线',),
                    ),
                ),
                array(
                    'name' => '排线列表',
                    'url' => '/logistics/order_line_list.php',
                    'page' => 'order_line_list',
                    'key' => '/logistics/order_line_list',
                    'buttons' => array(
                        array('key' => '/logistics/order_line_list', 'name' => '查看',),
                        array('key' => '/logistics/ajax/change_order_line', 'name' => '编辑排线',),
                        array('key' => '/logistics/ajax/get_order_line_info', 'name' => '送达',),
                        array('key' => '/logistics/logistics_track', 'name' => '物流轨迹',),
                    ),
                ),
                array(
                    'name' => '司机队列',
                    'url' => '/logistics/driver_queue.php',
                    'page' => 'driver_queue',
                    'key' => '/logistics/driver_queue',
                    'buttons' => array(
                        array('key' => '/logistics/driver_queue', 'name' => '查看队列',),
                        array('key' => '/logistics/ajax/clear_queue_status', 'name' => '重置签到',),
                        array('key' => '/logistics/ajax/clear_refuse_num', 'name' => '清除拒单',),
                    ),
                ),
                array(
                    'name' => '排线日志',
                    'url' => '/logistics/action_log.php',
                    'page' => 'action_log',
                    'key' => '/logistics/action_log',
                    'buttons' => array(
                        array('key' => '/logistics/action_log', 'name' => '查看排线日志',),
                    ),
                ),
                array(
                    'name' => '司机地图',
                    'url' => '/logistics/drivers_location.php',
                    'page' => 'drivers_location',
                    'key' => '/logistics/drivers_location',
                    'buttons' => array(
                        array('key' => '/logistics/drivers_location', 'name' => '查看地图',),
                    ),
                ),
                array(
                    'name' => 'APP签到二维码',
                    'url' => '/logistics/print_qrcode_app.php',
                    'page' => 'print_qrcode_app',
                    'key' => '/logistics/print_qrcode_app',
                    'buttons' => array(
                        array('key' => '/logistics/print_qrcode_app', 'name' => '查看签到二维码',),
                    ),
                ),
                array(
                    'name' => '司机账号',
                    'url' => '/logistics/driver.php',
                    'page' => 'driver',
                    'key' => '/logistics/driver',
                    'buttons' => array(
                        array('key' => '/logistics/driver', 'name' => '查看列表',),
                        array('key' => '/logistics/add_driver', 'name' => '查看详情',),
                        array('key' => '/logistics/ajax/save_driver', 'name' => '添加编辑司机',),
                        array('key' => 'hc_advanced_edit_driver', 'name' => '修改司机车型、仓库',),
                        array('key' => '/logistics/ajax/delete_driver', 'name' => '删除司机',),
                        array('key' => '/logistics/ajax/modify_driver', 'name' => '重置密码',),
                    ),
                ),
                array(
                    'name' => '搬运工账号',
                    'url' => '/logistics/carrier.php',
                    'page' => 'carrier',
                    'key' => '/logistics/carrier',
                    'buttons' => array(
                        array('key' => '/logistics/carrier', 'name' => '查看列表',),
                        array('key' => '/logistics/add_carrier', 'name' => '添加编辑搬运工',),
                        array('key' => 'hc_advanced_edit_carrier', 'name' => '修改搬运工仓库',),
                        array('key' => '/logistics/ajax/delete_carrier', 'name' => '删除搬运工',),
                    ),
                ),
                 array(
                    'name' => '司机订单',
                    'url' => '/logistics/driver_order_list.php',
                    'page' => 'driver_order_list',
                    'key' => '/order/driver_order_list',
                    'buttons' => array(
                        array('key' => '/order/driver_order_list', 'name' => '查看列表',),
                        array('key' => 'hc_order_paid_coopworker', 'name' => '支付',),
                        array('key' => '/order/ajax/generate_statement', 'name' => '生成结算单',),
                        array('key' => '/order/coopworker_order_print', 'name' => '打印',),
                    ),
                ),
                array(
                    'name' => '搬运工订单',
                    'url' => '/logistics/carrier_order_list.php',
                    'page' => 'carrier_order_list',
                    'key' => '/order/carrier_order_list',
                    'buttons' => array(
                        array('key' => '/order/carrier_order_list', 'name' => '查看列表',),
                        array('key' => 'hc_order_paid_coopworker', 'name' => '支付',),
                        array('key' => '/order/ajax/generate_statement', 'name' => '生成结算单',),
                        array('key' => '/order/coopworker_order_print', 'name' => '打印',),
                    ),
                ),
                array(
                    'name' => '结算单列表',
                    'url' => '/logistics/driver_order_statement.php',
                    'page' => 'driver_order_statement',
                    'key' => '/order/driver_order_statement',
                    'buttons' => array(
                        array('key' => '/order/driver_order_statement', 'name' => '查看列表',),
                        array('key' => '/order/ajax/sure_statement', 'name' => '确认',),
                        array('key' => '/order/statement_detail', 'name' => '详情',),
                        array('key' => '/order/coopworker_order_print', 'name' => '打印',),
                        array('key' => '/order/ajax/cancel_statement', 'name' => '撤销',),
                        array('key' => 'hc_order_paid_coopworker_franchisee', 'name' => '支付',),
                    ),
                ),
                array(
                    'name' => '拣货单列表',
                    'url' => '/logistics/picking_list.php',
                    'page' => 'picking_list',
                    'key' => '/order/picking_list',
                    'buttons' => array(
                        array('key' => '/order/picking_list', 'name' => '列表'),
                        array('key' => '/order/picking_detail', 'name' => '详情'),
                        array('key' => '/order/picking_print', 'name' => '打印'),
                        array('key' => '/order/ajax/refresh_picking_product', 'name' => '刷新'),
                        array('key' => '/order/ajax/mark_vnum_flag', 'name' => '标为外采'),
                        array('key' => '/order/ajax/get_occupied_product_by_order', 'name' => '查占用'),
                        array('key' => '/order/ajax/update_vnum', 'name' => '更新缺货'),
                        array('key' => '/order/ajax/refresh_force', 'name' => '强制刷新'),
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
                        array('key' => '/warehouse/stock_list', 'name' => '列表'),
                        array('key' => '/warehouse/edit_stock', 'name' => '编辑', ),
                        array('key' => '/warehouse/stock_history', 'name' => '历史'),
                        array('key' => '/warehouse/stock_search', 'name' => '搜索'),
                        array('key' => 'hc_show_purchase_cost_price', 'name' => '显示价钱s'),
                        array('key' => '/warehouse/save_stock_cost', 'name' => '编辑成本'),
                        array('key' => '/warehouse/ajax/clear_err_occupied', 'name'=>'清错占用'),
                        array('key' => '/warehouse/ajax/save_fring_cost', 'name' => '附加成本'),
                    ),
                ),
				array(
                    'name' => '货位库存', 
                    'url' => '/warehouse/location_list.php', 
                    'page'=>'location_list',
                    'key' => '/warehouse/location_list',
                    'buttons' => array(
                        array('key' => '/warehouse/location_list', 'name' => '列表'),
                        array('key' => '/warehouse/sku_locations_list', 'name' => '单sku多货架'),
                        array('key' => '/warehouse/location_skus_list', 'name' => '单sku多货架'),
                        array('key' => '/warehouse/ajax/add_location', 'name' => '添加货位'),
                        array('key' => '/warehouse/ajax/search_unshelved_bills', 'name' => '查看未上架'),
                        array('key' => 'hc_inventory_profit', 'name' => '盘盈'),
                        array('key' => 'hc_inventory_loss', 'name' => '盘亏'),
                        array('key' => '/warehouse/ajax/save_shift_location_stock', 'name' => '移架'),
                        array('key' => '/warehouse/ajax/del_sku_location', 'name' => '删除'),
                    ),
                ),
				array(
                    'name' => '盘库导出', 
                    'url' => '/warehouse/location_export.php', 
                    'page'=>'location_export',
                    'key' => '/warehouse/location_export',
                    'buttons' => array(
                        array('key' => '/warehouse/location_export', 'name' => '列表'),
                        array('key' => '/warehouse/download', 'name' => '导出')
                    ),
                ),
				array(
                    'name' => '供应商', 
                    'url' => '/warehouse/supplier_list.php', 
                    'page' => 'supplier_list',
                    'key' => '/warehouse/supplier_list',
                    'buttons' => array(
                        array('key' => '/warehouse/supplier_list', 'name' => '列表'),
                        array('key' => '/warehouse/add_supplier', 'name' => '添加供应商'),
                        array('key' => '/warehouse/edit_supplier', 'name' => '编辑'),
                        array('key' => '/warehouse/ajax/change_supplier_status', 'name' => '停用&审核'),
                        array('key' => '/warehouse/add_in_order', 'name' => '添加采购单'),
                        array('key' => 'hc_supplier_finance_data', 'name' => '供应商财务数据'),
                        array('key' => '/warehouse/supplier_sku_list', 'name' => '供应商的商品列表'),
                        array('key' => '/warehouse/ajax/save_supplier_purchase_price', 'name' => '维护采购价'),
                        array('key' => '/warehouse/ajax/del_supplier_sku', 'name' => '删除供应商sku'),
                        array('key' => '/warehouse/ajax/add_sku_4_supplier', 'name' => '添加供应商sku'),
                        array('key' => '/warehouse/ajax/create_inorder_4_supplier', 'name' => '创建采购单'),
                        array('key' => 'hc_edit_supplier_refund_price', 'name' => '编辑退货价格'),
                        array('key' => '/warehouse/ajax/get_supplier_info', 'name' => '供应商银行账户'),
                    ),
                ),
				array(
                    'name' => '采购单', 
                    'url' => '/warehouse/in_order_list.php', 
                    'page' => 'in_order_list',
                    'key' => '/warehouse/in_order_list',
                    'buttons' => array(
                        array('key' => '/warehouse/in_order_list', 'name' => '列表'),
                        array('key' => '/warehouse/edit_in_order', 'name' => '编辑'),
                        array('key' => '/warehouse/ajax/delete_order', 'name' => '删除采购单'),
                        array('key' => '/warehouse/detail_in_order', 'name' => '详情'),
                        array('key' => '/warehouse/in_order_print', 'name' => '打印'),
                        array('key' => '/warehouse/in_order_contract', 'name' => '下载合同'),
                        array('key' => '/warehouse/ajax/complete_receiver', 'name' => '完全收货'),
                        array('key' => '/warehouse/ajax/delete_product', 'name' => '删除商品'),
                        array('key' => '/warehouse/ajax/modify_inorder_product', 'name' => '改价改数量'),
                        array('key' => '/warehouse/ajax/complate_tmp_inorder', 'name' => '临采完成'),
                        array('key' => '/warehouse/ajax/dlg_supplier_list', 'name' => '修改供应商'),
                        array('key' => 'hc_in_order_audit', 'name' => '采购单审核'),
                    ),
                ),
                array(
                    'name' => '入库单',
                    'url' => '/warehouse/stock_in_lists.php',
                    'page' => 'stock_in_lists',
                    'key' => '/warehouse/stock_in_lists',
                    'buttons' => array(
                        array('key' => '/warehouse/stock_in_lists', 'name' => '列表'),
                        array('key' => '/warehouse/edit_stock_in', 'name' => '详情'),
                        array('key' => '/warehouse/ajax/save_stock_in', 'name' => '入库'),
                        array('key' => 'hc_show_inorder_product_price', 'name' => '显示商品单价'),
                        array('key' => '/warehouse/ajax/delete_stockIn', 'name' => '删除入库单&商品'),
                        array('key' => '/warehouse/shelved_detail', 'name' => '上架'),
                        array('key' => '/warehouse/ajax/confirm_paid', 'name' => '付款'),
                        array('key' => '/warehouse/stock_in_print', 'name' => '打印'),
                        array('key' => '/warehouse/ajax/create_stockin_statements', 'name' => '生成结算单'),
                    ),
                ),
                array(
                    'name' => '入库退货单',
                    'url' => '/warehouse/stockin_refund_list.php',
                    'page' => 'stockin_refund_list',
                    'key' => '/warehouse/stockin_refund_list',
                    'buttons' => array(
                        array('key' => '/warehouse/stockin_refund_list', 'name' => '列表'),
                        array('key' => '/warehouse/stockin_refund_detail', 'name' => '详情'),
                        array('key' => '/warehouse/ajax/refund_stockin', 'name' => '创建'),
                        array('key' => '/warehouse/ajax/confirm_refund_stockin', 'name' => '退货出库'),
                        array('key' => '/warehouse/ajax/del_stockin_refund', 'name' => '删除退货单'),
                        array('key' => '/warehouse/ajax/update_refund_product_price', 'name' => '改退货商品单价')
                    ),
                ),
                array(
                    'name' => '临时采购单',
                    'url' => '/warehouse/tmp_purchase2.php', 
                    'page'=>'tmp_purchase2',
                    'key' => '/warehouse/tmp_purchase2', 
                    'buttons' => array(
                        array('key' => '/warehouse/tmp_purchase2', 'name' => '列表'),
                        array('key' => '/warehouse/ajax/create_tmp_2_inorder', 'name' => '创建'),
                    ),
                ),
                array(
                    'name' => '外包临采单',
                    'url' => '/warehouse/tmp_outsourcer_purchase.php',
                    'page'=>'tmp_outsourcer_purchase',
                    'key' => '/warehouse/tmp_outsourcer_purchase',
                    'buttons' => array(
                        array('key' => '/warehouse/tmp_outsourcer_purchase', 'name' => '列表'),
                        array('key' => '/warehouse/ajax/create_tmp_outsourcer_inorder', 'name' => '创建'),
                    ),
                ),
                array(
                    'name' => '普采缺货',
                    'url' => '/warehouse/ordinary_purchase_stockout.php',
                    'page'=>'ordinary_purchase_stockout',
                    'key' => '/warehouse/ordinary_purchase_stockout',
                    'buttons' => array(
                        array('key' => '/warehouse/ordinary_purchase_stockout', 'name' => '列表'),
                        array('key' => '/warehouse/ajax/mark_stockout', 'name' => '标记缺货类型'),
                    ),
                ),
                array(
                    'name' => '库存预警',
                    'url'=>'/warehouse/stock_warning.php',
                    'page'=>'stock_warning',
                    'key' => '/warehouse/stock_warning',
                    'buttons' => array(
                        array('key' => '/warehouse/stock_warning', 'name' => '列表'),
                    ),
                ),
                array(
                    'name' => '调拨单',
                    'url' => '/warehouse/stock_shift_list.php',
                    'page' => 'stock_shift_list',
                    'key' => '/warehouse/stock_shift_list',
                    'buttons' => array(
                        array('key' => '/warehouse/stock_shift_list', 'name' => '列表'),
                        array('key' => '/warehouse/stock_shift_detail', 'name' => '详情'),
                        array('key' => '/warehouse/stock_shift', 'name' => '创建'),
                        array('key' => '/warehouse/ajax/save_stock_shift', 'name' => '出入库'),
                        array('key' => '/warehouse/stock_shift_print', 'name' => '打印'),
                        array('key' => '/warehouse/ajax/refresh_vnum_force', 'name' => '强制刷新'),
                        array('key' => '/warehouse/ajax/stock_shift_apply', 'name' => '发起调拔申请'),
                        array('key' => '/warehouse/ajax/stock_shift_rebut', 'name' => '驳回'),
                        array('key' => '/warehouse/ajax/deal_stock_shift_abnormal', 'name' => '差异处理'),
                    ),
                ),
                array(
                    'name' => '销售明细',
                    'url' => '/warehouse/sale_detail.php',
                    'page' => 'sale_detail',
                    'key' => '/order/sale_detail',
                    'buttons' => array(
                        array('key' => '/order/sale_detail', 'name' => '列表')
                    ),
                ),
                array(
                    'name' => '销售汇总',
                    'url' => '/warehouse/sale_product_summary.php',
                    'page' => 'sale_product_summary',
                    'key' => '/order/sale_product_summary',
                    'buttons' => array(
                        array('key' => '/order/sale_product_summary', 'name' => '列表'),
                    ),
                ),
				array(
                    'name' => '出入库历史', 
                    'url' => '/warehouse/stock_history.php', 
                    'page' => 'stock_history',
                    'key' => '/warehouse/stock_warning',
                    'buttons' => array(
                        array('key' => '/warehouse/stock_history', 'name' => '列表'),
                    ),
                ),
                array(
                    'name' => '盘点计划',
                    'url' => '/warehouse/plan_list.php',
                    'page' => 'plan_list',
                    'key' => '/warehouse/plan_list',
                    'buttons' => array(
                        array('key' => '/warehouse/plan_list', 'name' => '列表'),
                        array('key' => 'hc_add_inventory_plan', 'name' => '创建计划'),
                        array('key' => 'hc_edit_inventory_plan', 'name' => '编辑计划'),
                        array('key' => 'hc_del_inventory_plan', 'name' => '删除计划'),
                        array('key' => '/warehouse/plan_detail', 'name' => '任务详情'),
                        array('key' => '/warehouse/ajax/add_inventory_plan_products', 'name' => '确认盘点商品'),
                        array('key' => '/warehouse/ajax/create_inventory_task', 'name' => '创建盘点任务'),
                        array('key' => '/warehouse/ajax/save_inventory_task', 'name' => '分配盘点任务'),
                        array('key' => '/warehouse/ajax/get_inventory_plan_degree', 'name' => '查看进度'),
                        array('key' => '/warehouse/deal_diff_products', 'name' => '盘点差异'),
                        array('key' => '/warehouse/download_inventory_report', 'name' => '下载盘点报告'),
                        array('key' => '/warehouse/ajax/save_inventory_location_stock', 'name' => '盘点确认'),
                        array('key' => '/warehouse/ajax/update_diff_num', 'name' => '差异处理'),
                        array('key' => 'hc_deal_diff_show_stock_num', 'name' => '显示库存'),
                        array('key' => '/warehouse/inventory_product_list', 'name'=>'盘点商品表'),
                    ),
                ),
                array(
                    'name' => '其他出库',
                    'url' => '/warehouse/other_stock_out_order.php',
                    'page' => 'other_stock_out_order',
                    'key' => '/warehouse/other_stock_out_order',
                    'buttons' => array(
                        array('key' => '/warehouse/other_stock_out_order', 'name' => '列表'),
                        array('key' => '/warehouse/ajax/save_other_stock_out_order', 'name' => '创建&编辑'),
                        array('key' => 'hc_del_other_stock_out_order', 'name' => '删除'),
                        array('key' => 'hc_un_audit_other_stock_out_order', 'name' => '驳回'),
                        array('key' => 'hc_audit_other_stock_out_order', 'name' => '审核'),
                        array('key' => 'hc_finish_other_stock_out_order', 'name' => '出库/报损'),
                    ),
                ),
                array(
                    'name' => '其他入库',
                    'url' => '/warehouse/other_stock_in_order.php',
                    'page' => 'other_stock_in_order',
                    'key' => '/warehouse/other_stock_in_order',
                    'buttons' => array(
                        array('key' => '/warehouse/other_stock_in_order', 'name' => '列表'),
                        array('key' => '/warehouse/ajax/save_other_stock_in_order', 'name' => '创建&编辑'),
                        array('key' => 'hc_del_other_stock_in_order', 'name' => '删除'),
                        array('key' => 'hc_un_audit_other_stock_in_order', 'name' => '驳回'),
                        array('key' => 'hc_audit_other_stock_in_order', 'name' => '审核'),
                        array('key' => 'hc_shelved_other_stock_in_product', 'name' => '上架'),
                    ),
                ),
			)
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
                        array('key' => '/finance/customer_list', 'name' => '查看列表'),
                    ),
                ),
				array(
                    'name' => '应收明细（客户）',
                    'url' => '/finance/customer_bill_list.php',
                    'page' => 'customer_bill_list',
                    'key' => '/finance/customer_bill_list',
                    'buttons' => array(
                        array('key' => '/finance/customer_bill_list', 'name' => '查看列表'),
                        array('key' => '/finance/ajax/save_single_moneyIn_modify', 'name' => '修改明细'),
                        array('key' => '/finance/ajax/balance_to_amount', 'name' => '转入余额'),
                        array('key' => '/finance/ajax/save_money_in', 'name' => '财务收款'),
                    ),
                ),
                array(
                    'name' => '每日账单明细（客户）',
                    'url' => '/finance/bill_list_for_day.php',
                    'page' => 'bill_list_for_day',
                    'key' => '/finance/bill_list_for_day',
                    'buttons' => array(
                        array('key' => '/finance/bill_list_for_day', 'name' => '查看列表'),
                    ),
                ),
				array(
                    'name' => '账户余额明细（客户）',
                    'url' => '/finance/customer_amount_list.php',
                    'page' => 'customer_amount_list',
                    'key' => '/finance/customer_amount_list',
                    'buttons' => array(
                        array('key' => '/finance/customer_amount_list', 'name' => '查看列表'),
                        array('key' => '/finance/ajax/save_customer_amount', 'name' => '财务调账'),
                        array('key' => '/finance/ajax/save_customer_payment_type', 'name' => '修改支付类型'),
                    ),
                ),
				array(
                    'name' => '应付汇总（供应商）',
                    'url' => '/finance/supplier_list.php',
                    'page' => 'supplier_list',
                    'key' => '/finance/supplier_list',
                    'buttons' => array(
                        array('key' => '/finance/supplier_list', 'name' => '查看列表'),
                    ),
                ),
				array(
                    'name' => '应付明细（供应商）',
                    'url' => '/finance/supplier_bill_list.php',
                    'page' => 'supplier_bill_list',
                    'key' => '/finance/supplier_bill_list',
                    'buttons' => array(
                        array('key' => '/finance/supplier_bill_list', 'name' => '查看列表'),
                        array('key' => '/finance/ajax/modify_money_out', 'name' => '修改明细'),
                        array('key' => '/finance/ajax/save_money_out', 'name' => '调账/返现'),
                        array('key' => '/finance/download_pay_detail', 'name' => '下载账务清单'),
                    ),
                ),
                array(
                    'name' => '每日账单明细（供应商）',
                    'url' => '/finance/supplier_bill_list_for_day.php',
                    'page' => 'supplier_bill_list_for_day',
                    'key' => '/finance/supplier_bill_list_for_day',
                    'buttons' => array(
                        array('key' => '/finance/supplier_bill_list_for_day', 'name' => '查看列表'),
                    ),
                ),
                array(
                    'name' => '账户余额明细（供应商）',
                    'url' => '/finance/supplier_amount_list.php',
                    'page' => 'supplier_amount_list',
                    'key' => '/finance/supplier_amount_list',
                    'buttons' => array(
                        array('key' => '/finance/supplier_amount_list', 'name' => '查看列表'),
                        array('key' => '/finance/ajax/add_supplier_prepay', 'name' => '供应商预付'),
                    ),
                ),
				array(
                    'name' => '支付明细（第三方工人）',
                    'url' => '/finance/coopworker_bill_list.php',
                    'page' => 'coopworker_bill_list',
                    'key' => '/finance/coopworker_bill_list',
                    'buttons' => array(
                        array('key' => '/finance/coopworker_bill_list', 'name' => '查看列表'),
                    ),
                ),
				array(
                    'name' => '待付明细（第三方工人）',
                    'url' => '/finance/coopworker_willpay_list.php',
                    'page' => 'coopworker_willpay_list',
                    'key' => '/finance/coopworker_willpay_list',
                    'buttons' => array(
                        array('key' => '/finance/coopworker_willpay_list', 'name' => '查看列表'),
                    ),
                ),
                 array(
                    'name' => '工人兑账',
                    'url' => '/finance/check_statement.php',
                    'page' => 'check_statement',
                    'key' => '/order/check_statement',
                    'buttons' => array(
                        array('key' => '/order/check_statement', 'name' => '查看列表',),
                        array('key' => '/order/ajax/check_statement', 'name' => '审核',),
                        array('key' => '/order/download_statement', 'name' => '导出',),
                        array('key' => 'hc_order_paid_coopworker', 'name' => '支付',),
                        array('key' => '/order/print_statement_finance', 'name' => '打印',),
                        array('key' => '/order/ajax/generate_batch', 'name' => '生成批次号',),
                    ),
                ),
                array(
                    'name' => '采购单兑账',
                    'url' => '/finance/purchase_for_finance.php',
                    'page' => 'purchase_for_finance',
                    'key' => '/warehouse/purchase_for_finance',
                    'buttons' => array(
                        array('key' => '/warehouse/purchase_for_finance', 'name' => '列表'),
                        array('key' => '/warehouse/ajax/check_account', 'name' => '兑账'),
                    ),
                ),
                array(
                    'name' => '入库结算单',
                    'url' => '/finance/stock_in_statements.php',
                    'page' => 'stock_in_statements',
                    'key' => '/finance/stock_in_statements',
                    'buttons' => array(
                        array('key' => '/finance/stock_in_statements', 'name' => '列表'),
                        array('key' => '/finance/stockin_statement_detail', 'name' => '查看'),
                        array('key' => '/finance/stockin_statement_print', 'name' => '打印'),
                        array('key' => '/finance/ajax/recall_stockin', 'name' => '撤回'),
                        array('key' => '/warehouse/ajax/stockin_statement_paid', 'name' => '支付')
                    ),
                ),
//                array(
//                    'name' => '供应商批量付款',
//                    'url' => '/finance/supplier_bulk_payment.php',
//                    'page' => 'supplier_bulk_payment',
//                    'key' => '/finance/supplier_bulk_payment',
//                    'buttons' => array(
//                        array('key' => '/finance/supplier_bulk_payment', 'name' => '查看列表'),
//                        array('key' => '/warehouse/ajax/bulk_paid', 'name' => '批量付款'),
//                    ),
//                ),
//                array(
//                    'name' => '沙石砖结账',
//                    'url' => '/finance/special_cate_account.php',
//                    'page' => 'special_cate_account',
//                    'key' => '/order/special_cate_account',
//                    'buttons' => array(
//                        array('key' => '/order/special_cate_account', 'name' => '列表'),
//                    ),
//                ),
                array(
                    'name' => '批量结账（客户）',
                    'url' => '/finance/customer_account_pay.php',
                    'page' => 'customer_account_pay',
                    'key' => '/finance/customer_account_pay',
                    'buttons' => array(
                        array('key' => '/finance/customer_account_pay', 'name' => '结账'),
                    ),
                ),
//                array(
//                    'name' => '平台结款',
//                    'url' => '/finance/platform_debit.php',
//                    'page' => 'platform_debit',
//                    'key' => '/finance/platform_debit',
//                    'buttons' => array(
//                        array('key' => '/finance/platform_debit', 'name' => '结款'),
//                    ),
//                ),
                array(
                    'name' => '自动回单',
                    'url' => '/finance/auto_receipt.php',
                    'page' => 'auto_receipt',
                    'key' => '/order/auto_receipt',
                    'buttons' => array(
                        array('key' => '/order/auto_receipt', 'name' => '查看列表',),
                        array('key' => '/finance/ajax/save_money_in', 'name' => '收款',),
                        array('key' => '/order/ajax/auto_bluk_receipt', 'name' => '批量回单',),
                    ),
                ),
                array(
                    'name' => '财务商品',
                    'url' => '/finance/finance_product_list.php',
                    'page' => 'finance_product_list',
                    'key' => '/finance/finance_product_list',
                    'buttons' => array(
                        array('key' => '/finance/finance_product_list', 'name' => '查看列表',),
                        array('key' => '/finance/ajax/save_product', 'name' => '编辑'),
                        array('key' => '/finance/ajax/change_product', 'name' => '还原/删除',),
                    ),
                ),
                array(
                    'name' => '采购发票',
                    'url' => '/finance/input_invoice_list.php',
                    'page' => 'input_invoice_list',
                    'key' => '/finance/input_invoice_list',
                    'buttons' => array(
                        array('key' => '/finance/input_invoice_list', 'name' => '查看列表'),
                        array('key' => '/finance/edit_input_invoice', 'name' => '编辑'),
                        array('key' => '/finance/show_input_invoice', 'name' => '详情'),
                        array('key' => '/finance/ajax/change_input_invoice', 'name' => '确认/完成'),
                        array('key' => '/finance/ajax/delete_input_invoice', 'name' => '删除'),
                        array('key' => 'hc_input_invoice_statement_edit', 'name' => '编辑开票单据'),
                    ),
                ),
                array(
                    'name' => '客户发票',
                    'url' => '/finance/output_customer_list.php',
                    'page' => 'output_customer_list',
                    'key' => '/finance/output_customer_list',
                    'buttons' => array(
                        array('key' => '/finance/output_customer_list', 'name' => '查看列表'),
                    ),
                ),
                array(
                    'name' => '销售发票',
                    'url' => '/finance/output_invoice_list.php',
                    'page' => 'output_invoice_list',
                    'key' => '/finance/output_invoice_list',
                    'buttons' => array(
                        array('key' => '/finance/output_invoice_list', 'name' => '查看列表'),
                        array('key' => '/finance/edit_output_invoice', 'name' => '编辑'),
                        array('key' => '/finance/show_output_invoice', 'name' => '详情'),
                        array('key' => '/crm2/ajax/rebut_output_invoice', 'name' => '驳回'),
                        array('key' => '/finance/ajax/confirm_output_invoice', 'name' => '确认'),
                        array('key' => '/finance/ajax/finished_output_invoice', 'name' => '完成'),
                        array('key' => '/finance/print_output_invoice', 'name' => '打印'),
                    ),
                ),
                array(
                    'name' => '经销商日结款',
                    'url' => '/finance/agent_bill_day_list.php',
                    'page' => 'agent_bill_day_list',
                    'key' => '/finance/agent_bill_day_list',
                    'buttons' => array(
                        array('key' => '/finance/agent_bill_day_list', 'name' => '查看列表'),
                        array('key' => '/finance/agent_bill_day_detail', 'name' => '详情'),
                    ),
                ),
                array(
                    'name' => '经销商返点',
                    'url' => '/finance/agent_bill_cashback_list.php',
                    'page' => 'agent_bill_cashback_list',
                    'key' => '/finance/agent_bill_cashback_list',
                    'buttons' => array(
                        array('key' => '/finance/agent_bill_cashback_list', 'name' => '查看列表'),
                        array('key' => '/finance/agent_bill_cashback_detail', 'name' => '详情'),
                        array('key' => '/finance/ajax/agent_bill_cashback_pay', 'name' => '付款'),
                    ),
                ),
                array(
                    'name' => '经销商流水',
                    'url' => '/finance/agent_flow_list.php',
                    'page' => 'agent_flow_list',
                    'key' => '/finance/agent_flow_list',
                    'buttons' => array(
                        array('key' => '/finance/agent_flow_list', 'name' => '查看列表'),
                        array('key' => '/finance/ajax/save_agent_amount', 'name' => '预存/提现'),
                    ),
                ),
                array(
                    'name' => '金融账户流水',
                    'url' => '/finance/credit_history_list.php',
                    'page' => 'credit_history_list',
                    'key' => '/finance/credit_history_list',
                    'buttons' => array(
                        array('key' => '/finance/credit_history_list', 'name' => '查看列表'),
                    ),
                ),
                array(
                    'name' =>  '金融贴息列表',
                    'url' => '/finance/credit_accrual_list.php',
                    'page' => 'credit_accrual_list',
                    'key' => '/finance/credit_accrual_list',
                    'buttons' => array(
                        array('key' => '/finance/credit_accrual_list', 'name' => '查看列表'),
                    ),
                ),
                array(
                    'name' => '商家结算',
                    'url' => '/finance/seller_bill_list.php',
                    'page' => 'seller_bill_list',
                    'key' => '/finance/seller_bill_list',
                    'buttons' => array(
                        array('key' => '/finance/seller_bill_list', 'name' => '查看列表'),
                        array('key' => '/finance/seller_bill_detail', 'name' => '详情'),
                        array('key' => '/finance/ajax/seller_bill', 'name' => '结算'),
                        array('key' => '/finance/ajax/seller_bill_download', 'name' => '下载明细'),
                    ),
                ),
			)
		),
		'statistics' => array(
			'name' => '数据统计',
			'pages' => array(
                array(
                    'name' => '数据概览',
                    'url' => '/statistics/overview.php',
                    'page' => 'overview',
                    'key' => '/statistics/overview',
                    'display' => 'hidden',
                    'buttons' => array(
                        array('key' => '/statistics/overview', 'name' => '查看'),
                    ),
                ),
                array(
                    'name' => '热力图',
                    'url' => '/statistics/thermodynamic.php',
                    'page' => 'thermodynamic',
                    'key' => '/statistics/thermodynamic',
                    'buttons' => array(
                        array('key' => '/statistics/thermodynamic', 'name' => '查看'),
                    ),
                ),
				array(
                    'name' => '用户购买',
                    'url' => '/statistics/user_purchase.php',
                    'page' => 'user_purchase',
                    'key' => '/statistics/user_purchase',
                    'buttons' => array(
                        array('key' => '/statistics/user_purchase', 'name' => '查看'),
                    ),
                ),
				array(
                    'name' => '用户复购',
                    'url' => '/statistics/user_re_purchase.php',
                    'page' => 'user_re_purchase',
                    'key' => '/statistics/user_re_purchase',
                    'buttons' => array(
                        array('key' => '/statistics/user_re_purchase', 'name' => '查看'),
                    ),
                ),
                array(
                    'name' => '客户欠款统计',
                    'url' => '/statistics/receivables.php',
                    'page' => 'receivables',
                    'key' => '/statistics/receivables',
                    'buttons' => array(
                        array('key' => '/statistics/receivables', 'name' => '查看'),
                        array('key' => '/statistics/ajax/get_salers_amount', 'name' => '明细'),
                    ),
                ),
				array(
                    'name' => '库存日报',
                    'url' => '/statistics/sku_in_out.php',
                    'page' => 'sku_in_out',
                    'key' => '/statistics/sku_in_out',
                    'buttons' => array(
                        array('key' => '/statistics/sku_in_out', 'name' => '查看'),
                    ),
                ),
                array(
                    'name' => '热卖排行',
                    'url' => '/statistics/hot.php?days=30',
                    'page' => 'hot',
                    'key' => '/statistics/hot',
                    'buttons' => array(
                        array('key' => '/statistics/hot', 'name' => '查看'),
                    ),
                ),
				array(
                    'name' => '订单费用明细',
                    'url' => '/statistics/order_fee_list.php',
                    'page' => 'order_fee_list',
                    'key' => '/statistics/order_fee_list',
                    'buttons' => array(
                        array('key' => '/statistics/order_fee_list', 'name' => '查看'),
                    ),
                ),
//				array(
//                    'name' => '运费&搬运费（按库房）',
//                    'url' => '/statistics/logistics_fee_by_wid.php',
//                    'page' => 'logistics_fee_by_wid',
//                    'key' => '/statistics/logistics_fee_by_wid',
//                    'buttons' => array(
//                        array('key' => '/statistics/logistics_fee_by_wid', 'name' => '查看'),
//                    ),
//                ),
				array(
                    'name' => '毛利&毛收入',
                    'url' => '/statistics/count.php',
                    'page' => 'count',
                    'key' => '/statistics/count',
                    'buttons' => array(
                        array('key' => '/statistics/count', 'name' => '查看'),
                    ),
                ),
/*                array(
                    'name' => '自营&联营统计',
                    'url' => '/statistics/products_statistics.php',
                    'page' => 'products_statistics',
                    'key' => '/statistics/products_statistics',
                    'buttons' => array(
                        array('key' => '/statistics/products_statistics', 'name' => '查看'),
                    ),
                ),*/
                array(
                    'name' => '库房物流费比',
                    'url' => '/statistics/warehouse_fee_scale.php',
                    'page' => 'warehouse_fee_scale',
                    'key' => '/statistics/warehouse_fee_scale',
                    'buttons' => array(
                        array('key' => '/statistics/warehouse_fee_scale', 'name' => '查看'),
                        array('key' => 'hc_edit_warehouse_fee', 'name' => '编辑库房费用'),
                    ),
                ),
                array(
                    'name' => '订单金额区间',
                    'url' => '/statistics/price.php',
                    'page' => 'price',
                    'key' => '/statistics/price',
                    'buttons' => array(
                        array('key' => '/statistics/price', 'name' => '查看'),
                    ),
                ),
				array(
                    'name' => '库存统计',
                    'url' => '/statistics/stock.php',
                    'page' => 'stock',
                    'key' => '/statistics/stock',
                    'buttons' => array(
                        array('key' => '/statistics/stock', 'name' => '查看'),
                    ),
                ),
				array(
                    'name' => '库存sku排行',
                    'url' => '/statistics/stock_sku.php',
                    'page' => 'stock_sku',
                    'key' => '/statistics/stock_sku',
                    'buttons' => array(
                        array('key' => '/statistics/stock_sku', 'name' => '查看'),
                        array('key' => 'hc_download_stock_sku', 'name' => '下载'),
                    ),
                ),
				array(
                    'name' => '入库单排行',
                    'url' => '/statistics/stock_in.php',
                    'page' => 'stock_in',
                    'key' => '/statistics/stock_in',
                    'buttons' => array(
                        array('key' => '/statistics/stock_in', 'name' => '查看'),
                    ),
                ),

                array(
                    'name' => '数据导出',
                    'url' => '/statistics/export.php',
                    'page' => 'export',
                    'key' => '/statistics/export',
                    'buttons' => array(
                        array('key' => '/statistics/export', 'name' => '查看'),
                        array('key' => '/statistics/export_order_logistics_time', 'name' => '订单物流时间'),
                        array('key' => '/statistics/export_order_logistics_money', 'name' => '订单物流费用'),
                        array('key' => '/statistics/export_sku_sale_detail', 'name' => 'sku销售明细'),
                        array('key' => '/statistics/export_sku_stock_in_detail', 'name' => 'sku采购入库明细'),
                        array('key' => '/statistics/export_sku_shift_detail', 'name' => 'sku调拨明细'),
                        array('key' => '/statistics/export_sku_inventory_detail', 'name' => 'sku盘库明细'),
                        array('key' => '/statistics/export_product_info_city', 'name' => '商品信息(按城市)'),
                        array('key' => '/statistics/export_product_info_warehouse', 'name' => '商品信息(按仓库)'),
                        array('key' => '/statistics/export_order_arrears', 'name' => '欠款订单'),
                        array('key' => '/statistics/export_order_first', 'name' => '首单数据'),
                        array('key' => '/statistics/export_safety_stock', 'name' => '安全库存'),
                        array('key' => '/statistics/export_ka_sales_performance', 'name' => 'KA组销售业绩'),
                        array('key' => '/statistics/export_personal_detail', 'name' => '自用明细'),
                        array('key' => '/statistics/export_reported_loss_detail', 'name' => '报损明细'),
                        array('key' => '/statistics/export_no_receipt_statistics', 'name' => '未回单统计'),
                        array('key' => '/statistics/export_user_point', 'name' => '用户积分'),
                        array('key' => '/statistics/export_user_point_detail', 'name' => '用户积分明细'),
                        array('key' => '/statistics/export_sku_refund_detail', 'name' => 'sku退货明细'),
                        array('key' => '/statistics/export_other_stock_product', 'name' => '其他出入库商品'),
                        array('key' => '/statistics/export_stock_in_refund_product', 'name' => '入库退货单商品'),
                        array('key' => '/statistics/export_no_stock_sku', 'name' => '无库存sku'),
                        array('key' => '/statistics/export_warehouse_in_out_difference', 'name' => '库房进出货差异'),
                        array('key' => '/statistics/export_ka_customer_info', 'name' => 'ka客户信息'),
                        array('key' => '/statistics/export_sku_last_purchase_info', 'name' => 'sku最后采购信息'),
                        array('key' => '/statistics/export_north_south_product_out_detail', 'name' => '南北库商品出库明细')
                    ),
                ),
                array(
                    'name' => '销售业绩',
                    'url' => '/statistics/performance.php',
                    'page' => 'performance',
                    'key' => '/crm2/performance',
                    'buttons' => array(
                        array('key' => '/crm2/performance', 'name' => '查看列表',),
                        array('key' => '/crm2/download_performance', 'name' => '下载',),
                        array('key' => 'crm2_check_other_group', 'name' => '查看分组',),
                    ),
                ),
                array(
                    'name' => '销售工资计算',
                    'url' => '/statistics/sales_salary.php',
                    'page' => 'sales_salary',
                    'key' => '/statistics/sales_salary',
                    'buttons' => array(
                        array('key' => '/statistics/sales_salary', 'name' => '查看'),
                        array('key' => '/statistics/ajax/modify_amount_target', 'name' => '编辑目标'),
                    ),
                ),
                array(
                    'name' => '销售组业绩',
                    'url' => '/statistics/sales_group_performance.php',
                    'page' => 'sales_group_performance',
                    'key' => '/statistics/sales_group_performance',
                    'buttons' => array(
                        array('key' => '/statistics/sales_group_performance', 'name' => '查看'),
                        array('key' => '/statistics/ajax/modify_amount_target', 'name' => '编辑目标'),
                    ),
                ),
                array(
                    'name' => '客服业绩',
                    'url' => '/statistics/achievement.php',
                    'page' => 'achievement',
                    'key' => '/cs/achievement',
                    'buttons' => array(
                        array('key' => '/cs/achievement', 'name' => '查看'),
                        array('key' => 'cs_check_all_achievement', 'name' => '查看所有客服业绩'),
                    ),
                ),
                array(
                    'name' => '退款统计',
                    'url' => '/statistics/refund_statistics.php',
                    'page' => 'refund_statistics',
                    'key' => '/statistics/refund_statistics',
                    'buttons' => array(
                        array('key' => '/statistics/refund_statistics', 'name' => '查看'),
                    ),
                ),
//                array(
//                    'name' => '订单取消原因统计',
//                    'url' => '/statistics/cancel_statistics.php',
//                    'page' => 'cancel_statistics',
//                    'key' => '/statistics/cancel_statistics',
//                    'buttons' => array(
//                        array('key' => '/statistics/cancel_statistics', 'name' => '查看'),
//                    ),
//                ),
			)
		),
		'activity' => array(
			'name' => '运营管理',
			'pages' => array(
                array(
                    'name' => '城市运营概览',
                    'url' => '/activity/city_config_list.php',
                    'page' => 'city_config_list',
                    'key' => '/activity/city_config_list',
                    'buttons' => array(
                    ),
                ),
                array(
                    'name' => '推荐品牌',
                    'url' => '/activity/city_brand_list.php',
                    'page' => 'city_brand_list',
                    'key' => '/activity/city_brand_list',
                    'buttons' => array(
                        array('key' => '/activity/city_brand_list', 'name' => '查看列表'),
                        array('key' => '/activity/ajax/save_city_brand', 'name' => '创建&编辑')
                    ),
                ),
                array(
                    'name' => '优惠券&现金券',
                    'url' => '/activity/coupon_list.php',
                    'page' => 'coupon_list',
                    'key' => '/activity/coupon_list',
                    'buttons' => array(
                        array('key' => '/activity/coupon_list', 'name' => '查看列表'),
                        array('key' => '/activity/coupon_update', 'name' => '编辑优惠券'),
                        array('key' => '/activity/coupon_customer_list', 'name' => '查看发放记录'),
                        array('key' => '/activity/coupon_order_list', 'name' => '查看使用订单'),
                        array('key' => '/activity/download_coupon_orders', 'name' => '下载使用订单'),
                    ),
                ),
                array(
                    'name' => '促销活动',
                    'url' => '/activity/promotion_manjian_list.php',
                    'page' => 'promotion_manjian_list',
                    'key' => '/activity/promotion_manjian_list',
                    'buttons' => array(
                        array('key' => '/activity/promotion_manjian_list', 'name' => '查看列表'),
                        array('key' => '/activity/promotion_manjian_update', 'name' => '编辑促销'),
                    ),
                ),
				array(
                    'name' => '活动图片管理',
                    'url' => '/activity/picture_list.php',
                    'page' => 'picture_list',
                    'key' => '/activity/picture_list',
                    'buttons' => array(
                        array('key' => '/activity/picture_list', 'name' => '查看列表'),
                        array('key' => '/activity/add_picture', 'name' => '编辑'),
                    ),
                ),
				array(
                    'name' => '楼层活动管理',
                    'url' => '/activity/floor_activity_list.php',
                    'page' => 'floor_activity_list',
                    'key' => '/activity/floor_activity_list',
                    'buttons' => array(
                        array('key' => '/activity/floor_activity_list', 'name' => '查看列表'),
                        array('key' => '/activity/floor_sale_list', 'name' => '查看详情'),
                        array('key' => '/activity/add_floor_activity', 'name' => '编辑楼层'),
                        array('key' => '/activity/add_floor_sale', 'name' => '编辑楼层活动'),
                    ),
                ),
				array(
                    'name' => '限时抢购',
                    'url' => '/activity/flash_activity_list.php',
                    'page' => 'flash_activity_list',
                    'key' => '/activity/flash_activity_list',
                    'buttons' => array(
                        array('key' => '/activity/flash_activity_list', 'name' => '查看列表'),
                        array('key' => '/activity/flash_sale_list', 'name' => '查看商品'),
                        array('key' => '/activity/add_activity_flash', 'name' => '编辑抢购'),
                    ),
                ),
				array(
                    'name' => '文章管理',
                    'url' => '/activity/article_list.php',
                    'page' => 'article_list',
                    'key' => '/activity/article_list',
                    'buttons' => array(
                        array('key' => '/activity/article_list', 'name' => '查看列表'),
                        array('key' => '/activity/article_detail', 'name' => '查看详情'),
                        array('key' => '/activity/add_article', 'name' => '编辑文章'),
                    ),
                ),
                array(
                    'name' => 'app版本列表',
                    'url' => '/activity/version_list.php',
                    'page' => 'version_list',
                    'key' => '/admin/version_list',
                    'buttons' => array(
                        array('key' => '/admin/version_list', 'name' => '查看列表'),
                    ),
                ),
                array(
                    'name' => '新增app版本',
                    'url' => '/activity/edit_version.php',
                    'page' => 'edit_version',
                    'key' => '/admin/edit_version',
                    'buttons' => array(
                        array('key' => '/admin/edit_version', 'name' => '新增版本'),
                    ),
                ),
                array(
                    'name' => '抽奖结果',
                    'url' => '/activity/lottery_res.php',
                    'page' => 'lottery_res',
                    'key' => '/activity/lottery_res',
                    'buttons' => array(
                        array('key' => '/activity/lottery_res', 'name' => '查看列表'),
                    ),
                ),
                array(
                    'name' => '客户评价',
                    'url' => '/activity/customer_comment.php',
                    'page' => 'customer_comment',
                    'key' => '/activity/customer_comment',
                    'buttons' => array(
                        array('key' => '/activity/customer_comment', 'name' => '查看列表'),
                    ),
                ),
                array(
                    'name' => '兑换商品管理',
                    'url' => '/activity/customer_point_product.php',
                    'page' => 'customer_point_product',
                    'key' => '/activity/customer_point_product',
                    'buttons' => array(
                        array('key' => '/activity/customer_point_product', 'name' => '查看列表'),
                        array('key' => '/activity/edit_customer_point_product', 'name' => '创建&编辑'),
                        array('key' => '/activity/ajax/save_cpoint_product_stock_history', 'name' => '修改库存'),
                        array('key' => '/activity/show_customer_point_product', 'name' => '查看详情'),
                    ),
                ),
                array(
                    'name' =>  '商品兑换记录',
                    'url' => '/activity/cpoint_product_exchange_record.php',
                    'page' => 'cpoint_product_exchange_record',
                    'key' => '/activity/cpoint_product_exchange_record',
                    'buttons' => array(
                        array('key' => '/activity/cpoint_product_exchange_record', 'name' => '查看列表'),
                    ),
                ),
                array(
                    'name' =>  '贷款审批列表',
                    'url' => '/activity/finance_apply_list.php',
                    'page' => 'finance_apply_list',
                    'key' => '/activity/finance_apply_list',
                    'buttons' => array(
                        array('key' => '/activity/finance_apply_list', 'name' => '查看列表'),
                        array('key' => '/activity/edit_finance_apply', 'name' => '处理申请'),
                    ),
                ),
                array(
                    'name' => '销售优惠发放记录',
                    'url' => '/activity/sale_preferential_send_record.php',
                    'page' => 'sale_preferential_send_record',
                    'key' => '/activity/sale_preferential_send_record',
                    'buttons' => array(
                        array('key' => '/activity/sale_preferential_send_record', 'name' => '查看列表'),
                    )
                ),
//                array(
//                    'name' =>  '案例管理',
//                    'url' => '/activity/case_list.php',
//                    'page' => 'case_list',
//                    'key' => '/activity/case_list',
//                    'buttons' => array(
//                        array('key' => '/activity/case_list', 'name' => '查看列表'),
//                        array('key' => '/activity/edit_case', 'name' => '添加/编辑案例'),
//                    ),
//                ),
//                array(
//                    'name' =>  '装修百科',
//                    'url' => '/activity/wiki_list.php',
//                    'page' => 'wiki_list',
//                    'key' => '/activity/wiki_list',
//                    'buttons' => array(
//                        array('key' => '/activity/wiki_list', 'name' => '查看列表'),
//                        array('key' => '/activity/edit_wiki', 'name' => '添加/编辑百科'),
//                    ),
//                ),
//                array(
//                    'name' =>  '工长管理',
//                    'url' => '/activity/forman_list.php',
//                    'page' => 'forman_list',
//                    'key' => '/activity/forman_list',
//                    'buttons' => array(
//                        array('key' => '/activity/forman_list
//                        ', 'name' => '查看列表'),
//                        array('key' => '/activity/edit_forman', 'name' => '添加/编辑工长'),
//                    ),
//                ),
			)
		),
		'aftersale' => array(
			'name' => '工单管理',
			'pages' => array(
				array(
                    'name' => '工单列表',
                    'url' => '/aftersale/list.php?exec_status=2,3',
                    'page' => 'list',
                    'key' => '/aftersale/list',
                    'buttons' => array(
                        array('key' => '/aftersale/list', 'name' => '查看列表'),
                        array('key' => '/aftersale/detailLog', 'name' => '查看详情'),
                        array('key' => '/aftersale/edit', 'name' => '编辑工单'),
                        array('key' => '/aftersale/deal', 'name' => '处理工单'),
                    ),
                ),
                array(
                    'name' => '退货单管理',
                    'url' => '/aftersale/refund_list.php',
                    'page' => 'refund_list',
                    'key' => '/order/refund_list',
                    'buttons' => array(
                        array('key' => '/order/refund_list', 'name' => '列表'),
                        array('key' => '/order/edit_refund_new', 'name' => '创建&编辑'),
                        array('key' => 'hc_refund_show_detail', 'name' => '详情'),
                        array('key' => '/order/ajax/delete_refund_order', 'name' => '删除'),
                        array('key' => 'hc_refund_finance_unconfirm', 'name' => '财务未确认'),
                        array('key' => 'hc_refund_audit', 'name' => '审核退货单'),
                        array('key' => 'hc_complate_virtual_refund', 'name' => '空退完成'),
                        array('key' => 'hc_refund_into_stock', 'name' => '退货入库'),
                        array('key' => 'hc_refund_final_audit', 'name' => '提交财务'),
                        array('key' => 'hc_refund_finance_paid', 'name' => '财务退款'),
                        array('key' => '/order/refund_print', 'name' => '打印'),
                        array('key' => '/order/ajax/rebut_refund_order', 'name' => '驳回'),
                        array('key' => 'hc_aftersale_refund_product_export', 'name' => '导出退货商品')
                    ),
                ),
                array(
                    'name' => '换货单管理',
                    'url' => '/aftersale/exchanged_list.php',
                    'page' => 'exchanged_list',
                    'key' => '/order/exchanged_list',
                    'buttons' => array(
                        array('key' => '/order/exchanged_list', 'name' => '列表'),
                        array('key' => '/order/edit_exchanged', 'name' => '编辑'),
                        array('key' => '/order/exchanged_print', 'name' => '打印'),
                        array('key' => '/order/ajax/change_exchanged', 'name' => '审核&删除'),
                    ),
                ),
                array(
                    'name' => '补漏单管理',
                    'url' => '/aftersale/traps_list.php',
                    'page' => 'traps_list',
                    'key' => '/order/traps_list',
                    'buttons' => array(
                        array('key' => '/order/traps_list', 'name' => '列表'),
                        array('key' => '/order/edit_traps', 'name' => '编辑'),
                        array('key' => '/order/ajax/change_traps', 'name' => '审核&删除'),
                    ),
                ),
//                array(
//                    'name' =>  '预约管理',
//                    'url' => '/aftersale/appointment_list.php',
//                    'page' => 'appointment_list',
//                    'key' => '/aftersale/appointment_list',
//                    'buttons' => array(
//                        array('key' => '/aftersale/appointment_list', 'name' => '查看列表'),
//                        array('key' => '/aftersale/edit_appointment', 'name' => '添加/编辑预约'),
//                    ),
//                ),
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
                        array('key' => '/admin/staff_list', 'name' => '列表'),
                        array('key' => '/admin/edit_staff', 'name' => '编辑/添加账号'),
                        array('key' => '/admin/edit_staff_role', 'name' => '编辑角色'),
                        array('key' => '/admin/reset_staff_password', 'name' => '重置密码'),
                        array('key' => '/admin/reset_staff_login_times', 'name' => '重置登录次数'),
                        array('key' => '/admin/update_staff_ding', 'name' => '同步钉钉UserId'),
                    ),
                ),
                array(
                    'name' => '角色管理',
                    'url' => '/admin/role_list.php',
                    'page' => 'role_list',
                    'key' => '/admin/role_list',
                    'buttons' => array(
                        array('key' => '/admin/role_list', 'name' => '列表'),
                        array('key' => '/admin/edit_role', 'name' => '编辑/添加角色'),
                        array('key' => '/admin/edit_permission', 'name' => '编辑权限'),
                    ),
                ),
                array(
                    'name' => '操作日志',
                    'url' => '/admin/admin_log_list.php',
                    'page' => 'admin_log_list',
                    'key' => '/admin/admin_log_list',
                    'buttons' => array(
                        array('key' => '/admin/admin_log_list', 'name' => '查看'),
                    ),
                ),
                array(
                    'name' => '登录日志',
                    'url' => '/admin/admin_login_log.php',
                    'page' => 'admin_login_log',
                    'key' => '/admin/admin_login_log',
                    'buttons' => array(
                        array('key' => '/admin/admin_login_log', 'name' => '查看'),
                    ),
                ),
                array(
                    'name' => 'apache错误日志',
                    'url' => '/admin/apache_error_log.php',
                    'page' => 'apache_error_log',
                    'key' => '/admin/apache_error_log',
                    'buttons' => array(
                        array('key' => '/admin/apache_error_log', 'name' => '查看'),
                    ),
                ),
                array(
                    'name' => 'debug日志',
                    'url' => '/admin/debug_log.php',
                    'page' => 'debug_log',
                    'key' => '/admin/debug_log',
                    'buttons' => array(
                        array('key' => '/admin/debug_log', 'name' => '查看'),
                    ),
                ),
                array(
                    'name' => 'assert日志',
                    'url' => '/admin/assert_log.php',
                    'page' => 'assert_log',
                    'key' => '/admin/assert_log',
                    'buttons' => array(
                        array('key' => '/admin/assert_log', 'name' => '查看'),
                    ),
                ),
            )
        ),
		'user' => array(
            'display' => 'hidden',
			'name' => '个人中心',
			'pages' => array(
                array(
                    'name' => '消息',
                    'url' => '/user/message.php',
                    'page' => 'message',
                    'key' => '/user/message',
                    'buttons' => array(
                        array('key' => '/user/message', 'name' => '查看列表'),
                    ),
                ),
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
		$modules = self::$MODULES;

		//return Conf_Admin::getMODULES($suid, $suser, $modules);
        return Conf_Permission::getModules($suser, $modules);
	}

	public static function getFirstPage($suid, $suser)
	{
		$url = '/user/message.php';
        $user = Admin_Api::getStaff($suid);
        if (Admin_Role_Api::hasRole($user, Conf_Admin::ROLE_STATISTICS_OVERVIEW))
        {
            $url = '/statistics/overview.php';
        }
        if($user['department'] == Conf_Permission::DEPARTMENT_SELL)
        {
            $url = '/crm2/sales_homepage.php';
            $url = '/user/message.php';
        }
        return $url;



		$roleLevels = Admin_Role_Api::getRoleLevels($suid, $suser);
		if (isset($roleLevels[Conf_Admin::ROLE_ADMIN_NEW]))
		{
			$url = '/order/order_list.php';
		}
		elseif (isset($roleLevels[Conf_Admin::ROLE_SALES_NEW]))
		{
			$url = '/crm2/customer_list.php';
		}
		elseif (isset($roleLevels[Conf_Admin::ROLE_BUYER]))
		{
			$url = '/warehouse/in_order_list.php';
		}
		elseif (isset($roleLevels[Conf_Admin::ROLE_LM]))
		{
			$url = '/order/order_list.php';
		}
		elseif (isset($roleLevels[Conf_Admin::ROLE_OP]))
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
			$url = '/finance/customer_list.php';
		}
		elseif (isset($roleLevels[Conf_Admin::ROLE_YUNNIAO]))
		{
			$url = '/logistics/driver.php';
		}
		elseif (isset($roleLevels[Conf_Admin::ROLE_CITY_ADMIN]))
		{
			$url = '/shop/product_list.php';
		}
		elseif (isset($roleLevels[Conf_Admin::ROLE_AFTER_SALE]))
		{
			$url = '/aftersale/list.php';
		}
		elseif (isset($roleLevels[Conf_Admin::ROLE_EDITOR]))
		{
			$url = '/shop/sku_list.php';
		}
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
