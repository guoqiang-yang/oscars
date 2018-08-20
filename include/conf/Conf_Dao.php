<?php

/**
 * Database Access Object 配置
 */
class Conf_Dao
{
    private static $_tables = array(
        't_staff_user' => array(
            'pk' => 'suid',
            'fields' => array('*'),//todo: key=>type, 这样还可以检查转换类型
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',   //统一的状态字段, 1-删除。设置这个字段，则没有物理删除
        ),
        
        
        
        't_customer' => array(
            'pk' => 'cid',
            'fields' => array('*'),//todo: key=>type, 这样还可以检查转换类型
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',   //统一的状态字段, 1-删除。设置这个字段，则没有物理删除
        ),
        't_user' => array(
            'pk' => 'uid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        //用户反馈
        't_user_fb' => array(
            'pk' => 'fid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        //用户中心访问时间戳
        't_user_kvstore' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        //用户消息推送
        't_user_msg' => array(
            'pk' => 'mid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        //用户设备号和小米推送regid
        't_user_regid' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        //订单跟踪
        't_customer_tracking' => array(
            'pk' => 'tid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        //余额历史
        't_customer_amount_history' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        //余额历史
        't_money_in_history' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        //供应商付款历史
        't_money_out_history' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        //订单
        't_order' => array(
            'pk' => 'oid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        //快速下单
        't_quick_order' => array(
            'pk' => 'oid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        //订单商品
        't_order_product' => array(
            'pk' => '',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        //订单第三方人员，包括司机，搬运工
        't_coopworker_order' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        //司机、搬运工结算单
        't_coopworker_statement' => array(
            'pk' => 'id',
            'field' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        //订单第三方人员费用
        't_coopworker_money_out_history' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        //司机
        't_driver' => array(
            'pk' => 'did',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_carrier' => array(
            'pk' => 'cid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        //司机地理位置
        't_driver_location' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
        ),
        //退货单
        't_refund' => array(
            'pk' => 'rid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        //优惠券
        't_coupon' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        //优惠券申请
        't_coupon_apply' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        //订单优惠
        't_order_privilege' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        //订单活动商品
        't_order_activity_product' => array(
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        
        //订单日志
        't_order_action_log' => array(
            'pk' => 'lid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        //商品品牌
        't_brand' => array(
            'pk' => 'bid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        //限时抢购商品
        't_flash_sale' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        //限时抢购活动
        't_flash_sale_activity' => array(
            'pk' => 'fid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        //楼层运营活动
        't_floor_activity' => array(
            'pk' => 'fid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        //楼层运营活动商品
        't_floor_sale' => array(
            'pk' => 'sid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        //楼层运营活动商品
        't_article' => array(
            'pk' => 'aid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        //快捷入口
        't_shortcut' => array(
            'pk' => 'sid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        //服务特点表
        't_service_feature' => array(
            'pk' => 'sid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        //品牌所属分类
        't_cate_brand' => array(
            'pk' => '',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        //型号
        't_model' => array(
            'pk' => 'mid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        //图片
        't_picture' => array(
            'pk' => 'pid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        //商品
        't_product' => array(
            'pk' => 'pid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        //SKU
        't_sku' => array(
            'pk' => 'sid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        // 供应商
        't_supplier' => array(
            'pk' => 'sid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        // t_stock_in_product
        't_stock_in_product' => array(
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_stock_in_refund' => array(
            'pk' => 'srid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        // admin_task
        't_admin_task' => array(
            'pk' => 'tid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        // admin_task_history
        't_admin_task_history' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_customer_limit' => array(
            'pk' => 'lid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_cumulative_log' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_user_channel' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_business' => array(
            'pk' => 'bid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_lottery_record' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_community' => array(
            'pk' => 'cmid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_user_weixin_location' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_third_party_sku_mapping' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_community_distance_fee' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_manjian_activity' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_in_order_product' => array(
            'pk' => '',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_in_order' => array(
            'pk' => 'oid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_stock_in' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_sku_2_location' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_sku_location_history' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_stock_history' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_stock' => array(
            'pk' => '',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_after_sale' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_after_sale_log' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_invite_customer' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_invite_reward' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_order_line' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_driver_queue' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_logistics_action_log' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_weixin_coopworker' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_statistics_base_per_day' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_statistics_base_per_month' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_statistics_buyer_per_month' => array(
            'pk' => 'month',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_statistics_sku_per_day' => array(
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_statistics_cate_sku_per_day' => array(
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_statistics_brand_sku_per_day' => array(
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_hot_search' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_user_often_buy' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_statistics_receivables' => array(
            'pk' => '',
            'fields' => array('*')
        ),
        't_promotion_manjian_activity' => array(
            'pk' => 'id',
            'fields' => array('*')
        ),
        't_promotion_coupon' => array(
            'pk' => 'id',
            'fields' => array('*')
        ),
        't_statistics_warehouse_cost' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_message' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_app_version' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_exchanged' => array(
            'pk' => 'eid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_role' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_traps' => array(
            'pk' => 'tid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_weixin_limit' => array(
            'pk' => 'lid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_order_comment' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_order_cancel_reason' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_inventory_plan' => array(
            'pk' => 'pid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_inventory_task' => array(
            'pk' => 'tid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_inventory_products' => array(
            'pk' => '',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_stockin_statements' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_supplier_sku_list' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_invoice_product' => array(
            'pk' => 'pid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_input_invoice' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_input_invoice_product' => array(
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_output_invoice' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_output_invoice_product' => array(
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_other_stock_order' => array(
            'pk' => 'oid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_other_stock_products' => array(
            'pk' => 'oid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_processed_order' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_processed_order_products' => array(
            'pk' => '',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_sale_schedule' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_customer_visit' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_customer_relative' => array(
            'pk' => 'crid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_cart' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_cpoint_product' => array(
            'pk' => 'pid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_cpoint_history' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_cpoint_stock_history' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_cpoint_order' => array(
            'pk' => 'oid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_cpoint_order_product' => array(
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_toc_forman' => array(
            'pk' => 'fid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_toc_banner' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_toc_case' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_toc_wiki' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_toc_appointment' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_out_trade' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_stock_shift' => array(
            'pk' => 'ssid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_stock_shift_product' => array(
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_agent' => array(
            'pk' => 'aid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_agent_amount_history' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_agent_bill_day' => array(
            'pk' => 'bid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_agent_bill_2_order' => array(
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_agent_bill_cashback' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_finance_apply' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_finance_customer_account' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_finance_amount_history' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_statistics_warehouse_fee' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_finance_accrual_history' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_sale_preferential_send_record' => array(
            'pk' => 'oid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            
        ),
        't_statistics_monthly_sales_individual' => array(
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_customer_certification' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_certificate_list' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_user_kjl_design' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_seller_bill' => array(
            'pk' => 'bid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_seller_bill_receipt' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_admin_login_log' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_customer_identity_apply' => array(
            'pk' => 'cid',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_customer_search_plan' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_fifo_cost_queue' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_fifo_cost_history' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_city_brands' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_supplier_amount_history' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_sale_privilege_config' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
        't_kvstore' => array(
            'pk' => 'id',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
            'status' => 'status',
        ),
        't_sales_manpfm' => array(
            'pk' => '',
            'fields' => array('*'),
            'created_time' => 'ctime',
            'modified_time' => 'mtime',
        ),
    );

    public static function get($table)
    {
        assert(!empty(self::$_tables[$table]));

        return self::$_tables[$table];
    }
}