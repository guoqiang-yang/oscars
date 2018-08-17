<?php

/**
 * 财务 - 用友数据常量，模板.
 * 
 * @notice  新建一个仓需要添加的数据（仅加仓库，不改业务）
 *   >>> 销售相关
 *      1 其他业务收入
 *      2 主营业务收入-销售货物
 *      3 主营业务成本
 *      4 收到货款 - 库存现金
 *  >>> 采购相关
 *      1 库存商品
 *      2 
 * 
 *  >>> 其他
 *      1 城市code：见 self::getCityCode    [新开城市需要]
 * 
 * @author yangguoqiang
 * @date 2016-10-31
 */

class Conf_Finance_Yongyou
{
    /**
     * 生成凭证ID的前缀.
     */
    const Voucher_Id_Prefix = 6;
    
    /**
     * 销售单税点.
     */
    const SalesOrder_Tax_Point = 0.04;
    const SalesOrder_Service_Tax_Point = 0.06;  //联营部分的销项税
    
    /**
     * 生产凭证ID的类型.
     */
    public static $VoucherId_Type = array(
        'stock_in' => 1,        //采购入库
        'sales_order' => 2,     //销售单
        'agent' => 3,           //经销商
        'platform' => 4,        //平台模式-合作商
    );
    public static $VoucherId_BaseNum = array(
        'stock_in' => 60,        //采购入库
        'sales_order' => 0,     //销售单
    );
    
    private static $VoucherId_BeginNum = array(
        'sales_order' => 4000,      //销售
        'stock_in'    => 5000,      //采购入库
        'agent'       => 6000,      //经销商
    );
    
    // 银行-辅助核算
    public static $Subject_Code = array(
        'receive_payment'       => '01',    //收回货款
        'agent_order_paid'      => '01',    //收回货款-经销商
        'pf_order_paid'         => '01',    //收回货款-平台
        'prepay'                => '01',    //预存
        'agent_perpay'          => '01',    //预存-经销商
        'agent_order_perpay'    => '01',    //预存-经销商-客户
        'pf_order_perpay'       => '01',    //预存-平台-客户
        'pf_paid_to_seller'     => '01',    //付款-支付给第三方合作商
        'get_cash'              => '07',    //客户提现
        'agent_cash_back'       => '07',    //提现-经销商
        'coopworker_fee'        => '07',    //支付费用-运费/搬运费'
        
        'pay_stockin'           => '04',    //支付货款
    );
    
    /**
     * 凭证摘要 - 销售单.
     */
    public static $Abstract_SalesOrder = array(
        'sales_product' => '销售货物',
        'carry_forward_cost' => '结转成本',
        'receive_payment' => '收回货款',
        'out_business_2_paid' => '客户补偿平账',
        'prepay' => '预存',
        'balance_pay' => '客户余额支付',
        'customer_refund' => '客户退货',
        'refund_in_stock' => '退货入库',
        'into_balance' => '进余额',
        'get_cash' => '提现',
        'out_business' => '客户补偿预付',
        'trans_balance' => 'A到B余额转移',
        'trans_by_finance' => '财务转余额',
        'moling' => '抹零',
        'coopworker_fee' => '运费/搬运费',
        
        //+ 人工转余额
    );
    
    /**
     * 凭证摘要 - 采购出库.
     */
    public static $Abstract_StockIn = array(
        'stockin_product' => '产品入库',
        'pay_stockin' => '支付货款',    
        'refund_stockin' => '入库退货',
        'stock_shift' => '移库',
        'check_loss' => '盘亏',
        'check_gain' => '盘盈',
        
        'joint_stockin_product' => '联营产品入库',
        'joint_pay_stockin' => '联营支付货款',      
        'joint_refund_stockin' => '联营入库退货',
        'joint_stock_shift' => '联营移库',
        'joint_check_loss' => '联营盘亏',
        'joint_check_gain' => '联营盘盈',
    );
    
    /**
     * 凭证摘要 - 经销商（WAT）.
     */
    public static $Abstract_Agent = array(
        'agent_stockin_product'      => '经销商-采购入库-自营入库',
        'agent_stockin_joint_product'=> '经销商-采购入库-联营入库',
        'agent_stockin_pay'          => '经销商-采购入库-自营支付货款',
        'agent_stockin_joint_pay'    => '经销商-采购入库-联营支付货款',
        'agent_stockin_sale_product' => '经销商-采购入库-销售货物',
        'agent_stockin_carryover'    => '经销商-采购入库-结转成本',
        
        'agent_sshift_sale_product'  => '经销商-调拨入库-销售货物',
        'agent_sshift_carryover'     => '经销商-调拨入库-结转成本',
        
        'agent_sshift_refund'        => '经销商-调拨出库-退货',
        'agent_sshift_refund_instock'  => '经销商-调拨出库-退货入库',
        
        'agent_order_sale_product'  => '经销商-客户销售货物',
        'agent_order_paid'          => '经销商-客户收回货款',
        'agent_order_perpay'        => '经销商-客户预付',
        'agent_order_amount_paid'   => '经销商-客户余额支付',
        'agent_order_refund'        => '经销商-客户退货',
        'agent_order_refund_inamount' => '经销商-客户退货进余额',
        'agent_order_moling'        => '经销商-客户抹零',
        
        'agent_perpay'              => '经销商-预付',
        'agent_return_amount'       => '经销商-返还货款',
        'agent_allowance'           => '经销商-补贴',
        'agent_cash_back'           => '经销商-提现',
        'agent_paid_products'       => '经销商-支付货款',
        'agent_refund_products'     => '经销商-退货款',
    );
    
    /**
     * 凭证摘要 - 平台模式.
     */
    public static $Abstract_Platform = array(
        'pf_order_sale_product'     => '合作商-客户销售货物',
        'pf_order_paid'             => '合作商-客户收回货款',
        'pf_order_amount_paid'      => '合作商-客户余额支付',
        'pf_order_perpay'           => '合作商-客户预付',
        'pf_order_refund'           => '合作商-客户退货',
        'pf_order_refund_inamount'  => '合作商-客户退货进余额',
        'pf_order_moling'           => '合作商-客户抹零',
        
        'pf_paid_to_seller'         => '合作商-支付合作商货款',
    );
    
    /**
     * 科目编码 - 销售单.
     */
    public static $AccountCode_SalesOrder = array(
        
        // ### 销售货物
        //'receivable_no_tax'     => '112201',    //应收账款-销售货物-未税
        'receivable_with_tax'   => '112202',    //应收账款-销售货物-已税
        'receivable_agent'      => '112203',    //应收账款-经销商应收款
        'receivable_joint'      => '112204',    //应收账款-联营商应收款
        
        'other_income' => array(                //其他业务收入
            // wid => array('运费', '搬运费', '联营收入'),
            Conf_Warehouse::WID_3 => array('freight'  => '60510102',   'carriage' => '60510101',   'joint_income' => '60510103'),
            Conf_Warehouse::WID_4 => array('freight'  => '60510202',   'carriage' => '60510201',   'joint_income' => '60510203'),
            Conf_Warehouse::WID_5 => array('freight'  => '60510302',   'carriage' => '60510301',   'joint_income' => '60510303'),
            Conf_Warehouse::WID_6 => array('freight'  => '60510502',   'carriage' => '60510501',   'joint_income' => '60510503'),
            Conf_Warehouse::WID_7 => array('freight'  => '60510702',   'carriage' => '60510701',   'joint_income' => ''),
            Conf_Warehouse::WID_8 => array('freight'  => '60511402',   'carriage' => '60511401',   'joint_income' => '60511403'),
            Conf_Warehouse::WID_TJ1 => array('freight'  => '60510402',   'carriage' => '60510401',   'joint_income' => '60510403'),
            Conf_Warehouse::WID_WH1 => array('freight'  => '60510902',   'carriage' => '60510901',   'joint_income' => ''),
            Conf_Warehouse::WID_101 => array('freight'  => '60511102',   'carriage' => '60511101',   'joint_income' => '60511103'),
            Conf_Warehouse::WID_LF1 => array('freight'  => '60511202',   'carriage' => '60511201',   'joint_income' => '60511203'),
            Conf_Warehouse::WID_TJ2 => array('freight'  => '60511302',   'carriage' => '60511301',   'joint_income' => '60511303'),
            Conf_Warehouse::WID_CQ1 => array('freight'  => '60511502',   'carriage' => '60511501',   'joint_income' => '60511503'),
            Conf_Warehouse::WID_CQ_5001 => array('freight'  => '60511602',   'carriage' => '60511601',   'joint_income' => '60511603'),
            Conf_Warehouse::WID_BJ_WJ1  => array('freight'  => '60511702',   'carriage' => '60511701',   'joint_income' => '60511703'),
        ),
        
        'other_rece_receiveable' => 122108,         //其他应收款-应收账款 
        'other_rece_coll_fee'    => 122109,         //其他应收款-代收运费/搬运费'
        'other_pay_with_third'   => 224111,         //其他应付款-第三方往来
        'other_service_fee'      => 605106,         //其他业务输入-平台服务费收入
        
        'sales_product_wid' => array(   //主营业务收入-销售货物
            Conf_Warehouse::WID_3 => '60010101',    //3#北京南库
            Conf_Warehouse::WID_4 => '60010102',    //4#北京北库
            Conf_Warehouse::WID_5 => '60010103',    //5#北京东库
            Conf_Warehouse::WID_6 => '60010105',    //6#北京西库
            Conf_Warehouse::WID_7 => '60010106',    //7#北京西-中转库
            Conf_Warehouse::WID_8 => '60010111',    //8#北京新南库
            Conf_Warehouse::WID_TJ1 => '60010104',  //1#天津库
            Conf_Warehouse::WID_TJ2 => '60010110',  //2#天津津南库
            Conf_Warehouse::WID_WH1 => '60010107',  //1#武汉库
            Conf_Warehouse::WID_101 => '60010108',  //北安河社区店
            Conf_Warehouse::WID_LF1 => '60010109',  //廊坊社区店
            Conf_Warehouse::WID_CQ1 => '60010112',  //重庆总仓
            Conf_Warehouse::WID_BJ_WJ1 => '60010113', //北京五金仓库
        ),
       
        'output_tax'        => '22210101',      //应交税费-应交增值税-销项税
        'output_tax_tmp'    => '2221010101',    //应交税费-应交增值税-销项税-暂估
        
        // ### 结转成本
        'product_cost_wid' => array(    //主营业务成本
            Conf_Warehouse::WID_3 => '64010101',    //3#北京南库
            Conf_Warehouse::WID_4 => '64010102',    //4#北京北库
            Conf_Warehouse::WID_5 => '64010103',    //5#北京东库
            Conf_Warehouse::WID_6 => '64010105',    //6#北京西库
            Conf_Warehouse::WID_7 => '64010106',    //7#北京西-中转库
            Conf_Warehouse::WID_8 => '64010111',    //8#北京新南库
            Conf_Warehouse::WID_TJ1 => '64010104',  //1#天津库
            Conf_Warehouse::WID_TJ2 => '64010110',  //2#天津津南库
            Conf_Warehouse::WID_WH1 => '64010107',  //1#武汉仓库
            Conf_Warehouse::WID_101 => '64010108',  //北安河社区店
            Conf_Warehouse::WID_LF1 => '64010109',  //廊坊社区店
            Conf_Warehouse::WID_CQ1 => '64010112',  //重庆总仓
            Conf_Warehouse::WID_BJ_WJ1 => '64010113', //北京五金仓库
        ),
        
        // ### 收到货款
        'payment_type' => array(
            Conf_Base::PT_CASH => array(
                Conf_Warehouse::WID_3 => '100122',      //3#玉泉营库存现金
                Conf_Warehouse::WID_4 => '100122',      //4#来广营库存现金
                Conf_Warehouse::WID_5 => '100122',      //5#通州库存现金
                Conf_Warehouse::WID_6 => '100122',      //6#北京西库
                Conf_Warehouse::WID_TJ1 => '100122',    //1#天津库存现金
                Conf_Warehouse::WID_WH1 => '100122',    //1#武汉仓库现金
                Conf_Warehouse::WID_101 => '100122',    //北安河社区店
                Conf_Warehouse::WID_LF1 => '100122',    //廊坊社区店
                Conf_Warehouse::WID_TJ2 => '100122',    //2#天津津南库
                Conf_Warehouse::WID_8   => '100122',    //8#北京新南库
                Conf_Warehouse::WID_BJ_WJ1  => '100122',//北京五金仓库
                Conf_Warehouse::WID_CQ1 => '100123',    //重庆总仓
                0 => '100122',                          //库存现金（结算号收款-办公室）
                
                // 按城市
                Conf_City::CHONGQING => '100123',       //重庆
                'default'            => '100122',       //其他：京津冀
            ),
            Conf_Base::PT_TRANSFER => '10010102',               //银行转账   民生3829
            Conf_Base::PT_POS => '10020104',                    //POS机
            Conf_Base::PT_WEIXIN => '10010101',                 //微信转账
            Conf_Base::PT_WEIXIN_ONLINE => '10020101',          //微信在线支付 财付通 (公户民生)
            Conf_Base::PT_ALIPAY => '10020102',                 //支付宝 (公户民生)       
            //Conf_Base::PT_CHEQUE => '100201',                 //支票    民生银行
            Conf_Base::PT_HC_ACCOUNT => '10020103',             //公户转账    公户-民生8567
            Conf_Base::PT_HC_ACCOUNT_CMBC_8567 => '10020103',   //公户-民生8567
            Conf_Base::PT_HC_ACCOUNT_RCB_8850 => '100202',      //公户-农商8850
            Conf_Base::PT_HC_ACCOUNT_BCM_1678 => '10020301',    //公户-交通1678
            Conf_Base::PT_WEIXIN_APP => '10020302',             //app-微信在线支付
            Conf_Base::PT_HEMA       => '10020105',             //河马支付
            
            //Conf_Base::PRIVATE_SPDB_8336 => '100105',           //浦发3829
            //Conf_Base::PRIVATE_CCB_5306 => '100108',            //中信5306
            //Conf_Base::PRIVATE_CCB_1129 => '100107',            //中信1129
            //Conf_Base::PRIVATE_SPDB_0735 => '100106',           //浦发0735
            //Conf_Base::PRIVATE_CMBC_0957 => '100104',           //民生0957
            Conf_Base::PUBLIC_CMBC => '10020103',                 //公户-民生8567
        ),
        
        // ### 预收账款
        'pre_sales_with_tax' => '220301',        //预收账款-预收材料款-已票
        
        // ### 销售费用
        'sales_fee'             => '660113',    //销售费用-广告宣传（预存返现）
        'sales_fee_sina'        => '660131',    //销售费用-广告宣传（抢工长服务费）
        'sales_fee_freight'     => '660129',    //销售费用-运费
        'sales_fee_carriage'    => '660133',    //销售费用-搬运费
        'sales_fee_privilege'   => '660136',    //销售费用-优惠券补贴
        'sales_fee_discount'    => '660142',    //销售费用-折扣与折让-折扣
        'sales_fee_service'     => '660141',    //销售费用-服务费
        'sales_fee_compensate'  => '660151',    //销售费用-客户补偿
        
        // ### 客户补偿
        //'out_buiness_pay' => '6711',    //营业外支出
    );
    
    /**
     * 科目编码 - 采购入库.
     * 
     */
    public static $AccountCode_StockIn = array(
        // ### 商品入库
        'stockin_product' => array(    //库存商品
            // 仓库 => array('cate1'=>'code1')
            //{1:水，2:电，3:木，4:瓦，5:油，6:工具，7:运费，99:虚拟商品}
            Conf_Warehouse::WID_3 => array(1=>'14050101', 2=>'14050102', 3=>'14050103', 4=>'14050104', 
                                           5=>'14050105', 6=>'14050106', 7=>'14050109', 99=>'14050110'),
            Conf_Warehouse::WID_4 => array(1=>'14050201', 2=>'14050202', 3=>'14050203', 4=>'14050204', 
                                           5=>'14050205', 6=>'14050206', 7=>'14050209', 99=>'14050210'),
            Conf_Warehouse::WID_5 => array(1=>'14050301', 2=>'14050302', 3=>'14050303', 4=>'14050304', 
                                           5=>'14050305', 6=>'14050306', 7=>'14050309', 99=>'14050310'),
            Conf_Warehouse::WID_6 => array(1=>'14050501', 2=>'14050502', 3=>'14050503', 4=>'14050504', 
                                           5=>'14050505', 6=>'14050506', 7=>'14050509', 99=>'14050510'),
            Conf_Warehouse::WID_7 => array(1=>'14050601', 2=>'14050602', 3=>'14050603', 4=>'14050604', 
                                           5=>'14050605', 6=>'14050606', 7=>'14050609', 99=>'14050610'),
            Conf_Warehouse::WID_8 => array(1=>'14051201', 2=>'14051202', 3=>'14051203', 4=>'14051204', 
                                           5=>'14051205', 6=>'14051206', 7=>'14051209', 99=>'14051210'),
            Conf_Warehouse::WID_TJ1 => array(1=>'14050401', 2=>'14050402', 3=>'14050403', 4=>'14050404', 
                                             5=>'14050405', 6=>'14050406', 7=>'14050409', 99=>'14050410'),
            Conf_Warehouse::WID_WH1 => array(1=>'14050801', 2=>'14050802', 3=>'14050803', 4=>'14050804', 
                                             5=>'14050805', 6=>'14050806', 7=>'14050809', 99=>'14050810'),
            Conf_Warehouse::WID_101 => array(1=>'14050901', 2=>'14050902', 3=>'14050903', 4=>'14050904', 
                                             5=>'14050905', 6=>'14050906', 7=>'14050909', 99=>'14050910'),
            Conf_Warehouse::WID_LF1 => array(1=>'14051001', 2=>'14051002', 3=>'14051003', 4=>'14051004', 
                                             5=>'14051005', 6=>'14051006', 7=>'14051009', 99=>'14051010'),
            Conf_Warehouse::WID_TJ2 => array(1=>'14051101', 2=>'14051102', 3=>'14051103', 4=>'14051104', 
                                             5=>'14051105', 6=>'14051106', 7=>'14051109', 99=>'14051110'),
            Conf_Warehouse::WID_CQ1 => array(1=>'14051301', 2=>'14051302', 3=>'14051303', 4=>'14051304', 
                                             5=>'14051305', 6=>'14051306', 7=>'14051309', 99=>'14051310'),
            Conf_Warehouse::WID_BJ_WJ1 => array(1=>'14051401', 2=>'14051402', 3=>'14051403', 4=>'14051404', 
                                                5=>'14051405', 6=>'14051406', 7=>'14051409', 99=>'14051410'),
        ),
        
        'consignment' => array(     //寄售商品
            // 仓库 => array('cate1'=>'code1')
            //{1:水，2:电，3:木，4:瓦，5:油，6:工具，7:运费，99:虚拟商品}
            Conf_Warehouse::WID_3  => array(1=>'14060901', 2=>'14060902', 3=>'14060903', 4=>'14060904',
                                            5=>'14060905', 6=>'14060906', 7=>'14060909', 99=>'14060910'),
            Conf_Warehouse::WID_4  => array(1=>'14060101', 2=>'14060102', 3=>'14060103', 4=>'14060104',
                                            5=>'14060105', 6=>'14060106', 7=>'14060109', 99=>'14060110'),
            Conf_Warehouse::WID_6  => array(1=>'14061101', 2=>'14061102', 3=>'14061103', 4=>'14061104',
                                            5=>'14061105', 6=>'14061106', 7=>'14061109', 99=>'14061110'),
            Conf_Warehouse::WID_8  => array(1=>'14060601', 2=>'14060602', 3=>'14060603', 4=>'14060604',
                                            5=>'14060605', 6=>'14060606', 7=>'14060609', 99=>'14060610'),
            Conf_Warehouse::WID_TJ1  => array(1=>'14060201', 2=>'14060202', 3=>'14060203', 4=>'14060204',
                                              5=>'14060205', 6=>'14060206', 7=>'14060209', 99=>'14060210'),
            Conf_Warehouse::WID_TJ2  => array(1=>'14060301', 2=>'14060302', 3=>'14060303', 4=>'14060304',
                                              5=>'14060305', 6=>'14060306', 7=>'14060309', 99=>'14060310'),
            Conf_Warehouse::WID_101  => array(1=>'14060401', 2=>'14060402', 3=>'14060403', 4=>'14060404',
                                              5=>'14060405', 6=>'14060406', 7=>'14060409', 99=>'14060410'),
            Conf_Warehouse::WID_LF1  => array(1=>'14060501', 2=>'14060502', 3=>'14060503', 4=>'14060504',
                                              5=>'14060505', 6=>'14060506', 7=>'14060509', 99=>'14060510'),
            Conf_Warehouse::WID_CQ1  => array(1=>'14060701', 2=>'14060702', 3=>'14060703', 4=>'14060704',
                                              5=>'14060705', 6=>'14060706', 7=>'14060709', 99=>'14060710'),
            Conf_Warehouse::WID_BJ_WJ1 => array(1=>'14061001', 2=>'14061002', 3=>'14061003', 4=>'14061004',
                                                5=>'14061005', 6=>'14061006', 7=>'14061009', 99=>'14061010'),
        ),
        //在途物质
        'on_the_way_product' => array(1=>'140201', 2=>'140202', 3=>'140203', 4=>'140204', 5=>'140205', 6=>'140206', 7=>'140209', 99=>'140210'),
        //寄售的在途物资
        'consignment_on_way_product' => array(
            1=>'14060801', 2=>'14060802', 3=>'14060803', 4=>'14060804', 5=>'14060805', 6=>'14060806', 7=>'14060809', 99=>'14060810',
        ),
        
        'input_tax' => '2221010202',          //应交税费-应交增值税-进项税
        
        // ###支付货款 - 财务支付供应商货款
        'pay_stockin' => '220201',      //应付账款-应付款-供应商
        'pay_to_agent' => '220206',     //应付账款-经销商应付款
        
        'payment_type' => array(              //库存现金
            Conf_Finance::MO_PRIVATE_CMBC_3829 => '10010102',       //民生3829
            Conf_Finance::MO_PRIVATE_CMBC_9940 => '100102',         //民生9940
            //Conf_Finance::MO_PRIVATE_SPDB_8336 => '100105',       //浦发8336
            Conf_Finance::MO_PUBLIC_CMBC       => '10020103',       //公户-民生8567
            Conf_Finance::MO_GENERAL_BRCB      => '100202',         //北京农村商业银行-一般户-8850
            Conf_Finance::MO_GENERAL_CCB       => '10020301',       //交通一般户1678
            Conf_Finance::MO_GENERAL_CMB       => '10020401',       //招商银行一般户0907
            Conf_Finance::MO_CHEQUE            => '10020103',       //支票, 公户-民生8567
            Conf_Finance::MO_RESERVE_FUND      => '122107',         //备用金
            Conf_Finance::MO_PRIVATE_CCB_5306  => '100102',         //中信
            
            //Conf_Finance::MO_CASH              => '122107',       //现金-备用金
        ),
        
        'wait_deal_loss_income' => '1901',       //待处理财产损溢
        //'management_fee'        => '660240',       //管理费用
        
    );
    
    // 采购人员使用现金/备用金的Mapping，辅助核算使用 （自营）
    public static $suid2YYID = array(
        '93' =>  array('dept_id'=>'020101', 'personal_id'=>'48'),           //南库：韩飞飞采购号
        '399' => array('dept_id'=>'020101', 'personal_id'=>'48'),           //南库：韩飞飞采购号-联营
        '177' => array('dept_id'=>'020102', 'personal_id'=>'0011'),         //北库：赵伟采购号
        '398' => array('dept_id'=>'020102', 'personal_id'=>'0011'),         //北库：赵伟采购号-联营
        '262' => array('dept_id'=>'020201', 'personal_id'=>'0039'),         //天津库：席军	
        '229' => array('dept_id'=>'020103', 'personal_id'=>'0018'),         //东库：齐冬升
        '295' => array('dept_id'=>'020201', 'personal_id'=>'0029'),         //天津库：段罡罡
        '272' => array('dept_id'=>'020201', 'personal_id'=>'0019'),         //天津库
        '400' => array('dept_id'=>'020201', 'personal_id'=>'0019'),         //天津库-联营
        '292' => array('dept_id'=>'020104', 'personal_id'=>'0020'),         //西库
        '305' => array('dept_id'=>'020301', 'personal_id'=>'0021'),         //武汉库
        '371' => array('dept_id'=>'020201', 'personal_id'=>'47'),           //天津津南库：马银秋
        '401' => array('dept_id'=>'020201', 'personal_id'=>'47'),           //天津津南库：马银秋-联营
        '432' => array('dept_id'=>'0107',   'personal_id'=>'0026'),         //重庆总仓：李亮亮
        '423' => array('dept_id'=>'0107',   'personal_id'=>'0026'),         //重庆总仓：李亮亮-联营
        '436' => array('dept_id'=>'020602', 'personal_id'=>'67'),           //重庆总仓：汪学成-联营
        '437' => array('dept_id'=>'020602', 'personal_id'=>'67'),           //重庆总仓：汪学成
        '493' => array('dept_id'=>'020602', 'personal_id'=>'68'),           //重庆分仓#01：徐鹏鹏
        '494' => array('dept_id'=>'020602', 'personal_id'=>'68'),           //重庆分仓#01：徐鹏鹏-联营
        '492' => array('dept_id'=>'020602', 'personal_id'=>'76'),           //重庆总仓：邓红军-联营
        '500' => array('dept_id'=>'020602', 'personal_id'=>'76'),           //重庆总仓：邓红军
        '499' => array('dept_id'=>'020602', 'personal_id'=>'77'),           //重庆总仓：陈晓松
        '496' => array('dept_id'=>'020602', 'personal_id'=>'77'),           //重庆总仓：陈晓松-联营
        '511' => array('dept_id'=>'020602', 'personal_id'=>'80'),           //重庆总仓：谭力-自营
        '512' => array('dept_id'=>'020602', 'personal_id'=>'80'),           //重庆总仓：谭力-联营
        '431' => array('dept_id'=>'020401', 'personal_id'=>'78'),           //重庆总仓：吕园梦-联营
        '459' => array('dept_id'=>'020401', 'personal_id'=>'78'),           //重庆总仓：吕园梦-自营
        '508' => array('dept_id'=>'0105',   'personal_id'=>'74'),           //北京五金仓：杨启维
        '552' => array('dept_id'=>'020201', 'personal_id'=>'84'),           //天津河西仓：李富国
        '553' => array('dept_id'=>'020201', 'personal_id'=>'84'),           //天津河西仓：李富国
    );
    
    // 仓库id对应的部门id
//    public static $wid2YYDeptid = array(
//        Conf_Warehouse::WID_3 => '110301',
//        Conf_Warehouse::WID_4 => '110302',
//        Conf_Warehouse::WID_5 => '110303',
//        Conf_Warehouse::WID_6 => '110304',
//        Conf_Warehouse::WID_7 => '110305',
//        Conf_Warehouse::WID_TJ1 => '',
//        Conf_Warehouse::WID_WH1 => '',
//    );
    
    // 部门ID
    public static $baseDeptid = array(
        'saler' => '0105',
        //'logistics' => '1102',
    );
    
    // 部门：项目对应
    public static $department2Subject = array(
        '0105' => array('cate_id'=>'97', 'pro_id'=>'0300101'),
    );
    
    // 部门
    public static $Departments = array(
        'hc' => array(),    //综合管理部门
        Conf_City::CHONGQING => array('sales'=>'020603'), //重庆
    );
    
    // 结算司机/搬运工费用挂的部门/仓库 （财务自己处理数据，脚本不在维护这部分数据）
    public static $wid2Deptid = array(
        Conf_Warehouse::WID_3 => '020101',
        Conf_Warehouse::WID_4 => '020102',
        Conf_Warehouse::WID_5 => '020103',
        Conf_Warehouse::WID_6 => '020104',
        Conf_Warehouse::WID_TJ1 => '020201',
        Conf_Warehouse::WID_WH1 => '020301',
        Conf_Warehouse::WID_LF1 => '020401',
        Conf_Warehouse::WID_101 => '020501',
    );
    
    /**
     * 生成经销id.
     * 
     * 原因：经销商在用友，作为客户对待，与customer客户有碰撞，加一个标识.
     */
    public static function genAgentId($agentId)
    {
        $pre = 'JXS';
        
        return $pre.$agentId;
    }
    
    /**
     * 平台/轻模式id.
     *
     */
    public static function genPlatformId($platformId)
    {
        $pre = 'PLF';
        
        return $pre. $platformId;
    }
    
    /**
     * 生成凭证ID （财务使用，每月唯一）.
     * 
     * @param int $type
     * @param string $date 待整理凭证的时间 yyyy-mm-dd
     * @param int $seqNum 顺序号，两位
     */
    public static function genVoucherId($type, $date, $seqNum)
    {
        if (!array_key_exists($type, self::$VoucherId_Type))
        {
            return -1;
        }
        
        return self::$VoucherId_Type[$type].date('d', strtotime($date))
                .str_pad($seqNum, 2, '0', STR_PAD_LEFT);
        
//        $currentId = self::$VoucherId_BeginNum[$type];
//        self::$VoucherId_BeginNum[$type]++;
//        return $currentId;
        
        //$baseNum = self::$VoucherId_BaseNum[$type] + intval($seqNum);
        //$preNum = 60 + intval(date('d', strtotime($date)));
        
        //return $preNum. str_pad($baseNum, 2, '0', STR_PAD_LEFT);
        //return self::Voucher_Id_Prefix.self::$VoucherId_Type[$type].date('d', strtotime($date));
    }
    
    /**
     * 生产凭证ID (系统使用，唯一）.
     * 
     * 规则：GL + 13位数字
     * 
     * @param int $num 凭证的id 两位整数00-99
     * @param int $type
     * @param string $date 待整理凭证的时间 yyyy-mm-dd
     */
    public static function genSysVoucherId($num, $type, $date)
    {
        $prefix = 'GL000';
        
        if (!array_key_exists($type, self::$VoucherId_Type))
        {
            return -1;
        }
        
        if (!is_numeric($num) || $num>100)
        {
            return -2;
        }
        
        //$_num = $num<10? '0'.intval($num): intval($num);
        $_num = str_pad($num, 2, '0', STR_PAD_LEFT);
        
        return $prefix.self::Voucher_Id_Prefix.self::$VoucherId_Type[$type].date('ymd', strtotime($date)).$_num;
    }

    /**
     * 获取某月的最后一天.
     * 
     * @param day $day  YYYYMMDD
     */
    public static function getMonthLastDay($day)
    {
        $firstDayOfMonth = date('Y-m-01', strtotime($day));
        
        return date('Y-m-d', strtotime("$firstDayOfMonth +1 month -1 day"));
    }

    /**
     * 生产凭证.
     * 
     * @param string $sysVoucherId   系统级凭证的唯一ID
     * @param string $head  凭证的head
     * @param array $entrys  凭证的实体
     */
    public static function genVoucher($sysVoucherId, $head, $entrys)
    {
        $body = '';
        if (is_array($entrys))
        {
            $body = implode('', $entrys);
        }
        else if (is_string($entrys))
        {
            $body = $entrys;
        }
        
        $str = '<?xml version="1.0" encoding="utf-8"?>'.
                    '<ufinterface roottag="voucher" billtype="gl" docid="374950587" receiver="u8" sender="004" proc="query" codeexchanged="N" renewproofno="n" exportneedexch="N" timestamp="0x0000000000416796" lastquerydate="2017-01-10 15:54:23">'.
                        '<voucher id="'.$sysVoucherId.'">'.
                            '<voucher_head>'.$head.'</voucher_head>'.
                            '<voucher_body>'.$body.'</voucher_body>'.
                        '</voucher>'.
                    '</ufinterface>';
        
        return $str;
    }
    
    /**
     * 生产凭证的头.
     * 
     * @param array $params
     */
    public static function genVoucherHead($params)
    {
        
        $str =  '<company></company>'.
                '<voucher_type>记</voucher_type>'.                              //凭证类别
                '<fiscal_year>'.$params['year'].'</fiscal_year>'.               //年份
                '<accounting_period>'.$params['month'].'</accounting_period>'.  //月份
                '<voucher_id>'.$params['voucher_id'].'</voucher_id>'.           //凭证号
                '<attachment_number>-1</attachment_number>'.                    //固定值
                '<date>'.$params['date'].'</date>'.                             //制单日期
                '<auditdate></auditdate>'.                                      //审核日期【不需要】
                '<enter>杨国强</enter>'.                                         //制单人
                '<cashier></cashier>'.
                '<signature></signature>'.
                '<checker></checker>'.                                          //审核人【不需要】
                '<posting_date></posting_date>'.
                '<posting_person></posting_person>'.
                '<voucher_making_system></voucher_making_system>'.
                '<memo1></memo1>'.
                '<memo2></memo2>'.
                '<reserve1></reserve1>'.
                '<reserve2>'.$params['sys_voucher_id'].'</reserve2>'.           //系统级凭证id（唯一）
                '<revokeflag></revokeflag>';
        
        return $str;
    }
    
    public static function cashFlowStatement($params)
    {
        
        $year = substr($params['day'], 0, 4);
        $month = substr($params['day'], 5, 2);
        $yearMonth = $year.$month;
        
        return  '<cash_flow '.
                    ' cash_item="'. $params['subject_code']. '"'.     //项目目录编码
                    ' natural_debit_currency="'.(isset($params['amount_J'])?$params['amount_J']:'0.00').'"'.    //借方金额
                    ' natural_credit_currency="'.(isset($params['amount_D'])?$params['amount_D']:'0.00').'"'.   //贷方金额 
                    ' cCashItem="'. $params['subject_code']. '"'.    //项目目录编码
                    ' md="'.(isset($params['amount_J'])?$params['amount_J']:'0.00').'"'.    //借方金额
                    ' mc="'.(isset($params['amount_D'])?$params['amount_D']:'0.00').'"'.    //贷方金额
                    ' ccode="'. $params['account_code']. '"'.   //会计科目
                    ' md_f="0" mc_f="0"'.   //md_f:借方金额; mc_f:贷方金额 (用友说给固定值0)
                    ' nd_s="0" nc_s="0"'.   //固定值
                    ' cdept_id="" cperson_id="" ccus_id="" csup_id="" citem_class="" citem_id=""'. 
                    ' cDefine1="" cDefine2="" cDefine3="" cDefine4="" cDefine5="" cDefine6="" cDefine7=""'.
                    ' cDefine8="" cDefine9="" cDefine10="" cDefine11="" cDefine12="" cDefine13="" cDefine14="" cDefine15="" cDefine16=""'.
                    ' dbill_date="'.$params['day'].'"'. //凭证日期
                    ' csign="记"'.    //固定值
                    ' iyear="'.$year.'"'.  //年
                    ' iYPeriod="'.$yearMonth.'"'.   //年月
                    ' RowGuid="E503E6DA420AB0800FE23E99E7F448A100000000"'.  //固定值
                    ' cexch_name=""/>';
    }
    
    /**
     * 生产凭证的实体 - 分录.
     * 
     * @param array $params
     * @param string $type {J:借 D:贷}
     */
    public static function genVoucherEntry($params, $type)
    {
        $str =  '<entry>'.
                    '<entry_id>'.$params['entry_id'].'</entry_id>'.             //分录号
                    '<account_code>'.$params['account_code'].'</account_code>'. //借方科目 
                    '<abstract>'.$params['abstract'].'</abstract>'.             //摘要
                    '<settlement></settlement>'.
                    '<document_id></document_id>'.
                    '<document_date></document_date>'.
                    '<currency></currency>'.
                    '<unit_price></unit_price>'.
                    '<exchange_rate1></exchange_rate1>'.
                    '<exchange_rate2>0</exchange_rate2>'.                       //固定值
                    '<debit_quantity>0</debit_quantity>'.                       //固定值
                    '<primary_debit_amount>0</primary_debit_amount>'.           //固定值
                    '<secondary_debit_amount></secondary_debit_amount>'.
                    '<natural_debit_currency>'.($type=='J'?$params['price']:0).'</natural_debit_currency>'. //借方金额
                    '<credit_quantity>0</credit_quantity>'.                     //固定值
                    '<primary_credit_amount>0</primary_credit_amount>'.         //固定值
                    '<secondary_credit_amount></secondary_credit_amount>'.
                    '<natural_credit_currency>'.($type=='D'?$params['price']:0).'</natural_credit_currency>'.//贷方金额
                    '<bill_type></bill_type>'.
                    '<bill_id></bill_id>'.
                    '<bill_date></bill_date>'.
                    '<auxiliary_accounting>'.
                        '<item name="dept_id">'.(!empty($params['dept_id'])? $params['dept_id']: '').'</item>'.  //部门编码
                        '<item name="personnel_id">'.(!empty($params['personal_id'])? $params['personal_id']: '').'</item>'. //个人辅助核算
                        '<item name="cust_id">'.(!empty($params['customer_id'])? $params['customer_id']: '').'</item>'.      //客户ID
                        '<item name="supplier_id">'.(!empty($params['supplier_id'])? $params['supplier_id']: '').'</item>'.  //供应商ID
                        '<item name="item_id">'.(!empty($params['pro_id'])? $params['pro_id']: '').'</item>'.
                        '<item name="item_class">'.(!empty($params['pro_cate_id'])? $params['pro_cate_id']: '').'</item>'.
                        '<item name="operator"></item>'.
                        '<item name="self_define1"></item>'.
                        '<item name="self_define2"></item>'.
                        '<item name="self_define3"></item>'.
                        '<item name="self_define4"></item>'.
                        '<item name="self_define5"></item>'.
                        '<item name="self_define6"></item>'.
                        '<item name="self_define7"></item>'.
                        '<item name="self_define8"></item>'.
                        '<item name="self_define9"></item>'.
                        '<item name="self_define10"></item>'.
                        '<item name="self_define11"></item>'.
                        '<item name="self_define12"></item>'.
                        '<item name="self_define13"></item>'.
                        '<item name="self_define14"></item>'.
                        '<item name="self_define15"></item>'.
                        '<item name="self_define16"></item>'.
                    '</auxiliary_accounting>'.
                    '<detail>'.
                        '<cash_flow_statement>'.(isset($params['case_flow'])? $params['case_flow']: '').'</cash_flow_statement>'.
                        '<code_remark_statement></code_remark_statement>'.
                    '</detail>'.
			'</entry>';
        
        return $str;
    }
    
    
        /**
     * 生产凭证 - 客户/供应商.
     * 
     * @param string $sysVoucherId   系统级凭证的唯一ID
     * @param string $head  凭证的head
     * @param array $entrys  凭证的实体
     */
    public static function genVoucher_personal($type, $entrys)
    {
        $confs = array(
            'customer' => array(
                'sender' => 99,
                'roottag' => 'customer',
                'docid' => '882366359',
                'display' => '客户档案',
            ),
            'supplier' => array(
                'sender' => 55,
                'roottag' => 'vendor',
                'docid' => '27874171',
                'display' => '供应商档案',
            ),
        );
        
        if (!array_key_exists($type, $confs))
        {
            return '';
        }
        
        $conf = $confs[$type];
        
        $body = '';
        if (is_array($entrys))
        {
            $body = implode('', $entrys);
        }
        else if (is_string($entrys))
        {
            $body = $entrys;
        }
        
        $str = '<?xml version="1.0" encoding="utf-8"?>'.
                '<ufinterface sender="'.$conf['sender'].'" receiver="u8" roottag="'.$conf['roottag'].'" docid="'.$conf['docid'].'" proc="query" codeexchanged="N" exportneedexch="N" '. 
                    'paginate="0" display="'.$conf['display'].'" family="基础档案" dynamicdate="11/9/2016" maxdataitems="20000" bignoreextenduserdefines="y" '.
                    'needpage="1" totalpagenum="1" needpaginate="y" timestamp="0x0000000000416796" lastquerydate="2017-01-10 15:46:50">'.
                    $body.
                '</ufinterface>';
        
        
        return $str;
    }
    
    /**
     * 生产凭证的实体 - 客户.
     * 
     * @param array $params
     */
    public static function genVoucherEntry_Customer($params)
    {
        $time = date('Y-m-d H:i:s');
        $str = '<customer>'.
                    '<code>'. $params['cid'].'</code>'.
                    '<name>'. $params['name'].'</name>'.
                    '<abbrname>'. $params['name'].'</abbrname>'.
                    '<cCusMnemCode/>'.
                    '<sort_code>'. $params['city_code'].'</sort_code>'. // 01:北京 02:天津
                    '<domain_code/>'.
                    '<industry/>'.
                    '<address/>'.
                    '<postcode/>'.
                    '<tax_reg_code/>'.
                    '<bank_open></bank_open>'.
                    '<bank_acc_number/>'.
                    '<seed_date>'. $params['ctime'].'</seed_date>'.
                    '<legal_man/>'.
                    '<email/>'.
                    '<contact/>'.
                    '<phone/>'.
                    '<fax/>'.
                    '<bp/>'.
                    '<mobile/>'.
                    '<spec_operator/>'.
                    '<discount_rate>0</discount_rate>'.
                    '<credit_rank/>'.
                    '<credit_amount>0</credit_amount>'.
                    '<credit_deadline>0</credit_deadline>'.
                    '<pay_condition/>'.
                    '<devliver_site></devliver_site>'.
                    '<deliver_mode/>'.
                    '<head_corp_code>'. $params['cid'].'</head_corp_code>'.
                    '<deli_warehouse/>'.
                    '<super_dept/>'.
                    '<ar_rest>0</ar_rest>'.
                    '<last_tr_date/>'.
                    '<last_tr_amount>0</last_tr_amount>'.
                    '<last_rec_date/>'.
                    '<last_rec_amount>0</last_rec_amount>'.
                    '<end_date/>'.
                    '<tr_frequency>0</tr_frequency>'.
                    '<self_define1/>'.
                    '<self_define2/>'.
                    '<self_define3/>'.
                    '<pricegrade>-1</pricegrade>'.
                    '<CreatePerson>demo</CreatePerson>'.
                    '<ModifyPerson>demo</ModifyPerson>'.
                    '<ModifyDate>'. $time.'</ModifyDate>'.
                    '<auth_class>'. $params['cid'].'</auth_class>'.
                    '<self_define4/>'.
                    '<self_define5/>'.
                    '<self_define6/>'.
                    '<self_define7/>'.
                    '<self_define8/>'.
                    '<self_define9/>'.
                    '<self_define10/>'.
                    '<self_define11/>'.
                    '<self_define12/>'.
                    '<self_define13/>'.
                    '<self_define14/>'.
                    '<self_define15/>'.
                    '<self_define16/>'.
                    '<InvoiceCompany>'. $params['cid'].'</InvoiceCompany>'.
                    '<Credit>0</Credit>'.
                    '<CreditByHead>0</CreditByHead>'.
                    '<CreditDate>0</CreditDate>'.
                    '<LicenceDate>0</LicenceDate>'.
                    '<LicenceSDate/>'.
                    '<LicenceEDate/>'.
                    '<LicenceADays/>'.
                    '<LicenceRange/>'.
                    '<LicenceNo/>'.
                    '<BusinessDate>0</BusinessDate>'.
                    '<BusinessSDate/>'.
                    '<BusinessEDate/>'.
                    '<BusinessADays/>'.
                    '<CusBusinessRange/>'.
                    '<CusBusinessNo/>'.
                    '<CusGSPSDate/>'.
                    '<CusGSPEDate/>'.
                    '<CusGSPADays/>'.
                    '<CusGSPAuthRange/>'.
                    '<CusGSPAuthNo/>'.
                    '<Proxy>0</Proxy>'.
                    '<ProxySDate/>'.
                    '<ProxyEDate/>'.
                    '<ProxyADays/>'.
                    '<Memo/>'.
                    '<bLimitSale>0</bLimitSale>'.
                    '<cCusCountryCode/>'.
                    '<cCusEnName/>'.
                    '<cCusEnAddr1/>'.
                    '<cCusEnAddr2/>'.
                    '<cCusEnAddr3/>'.
                    '<cCusEnAddr4/>'.
                    '<cCusPortCode/>'.
                    '<cPrimaryVen/>'.
                    '<fCommisionRate/>'.
                    '<fInsueRate/>'.
                    '<bHomeBranch>0</bHomeBranch>'.
                    '<cBranchAddr/>'.
                    '<cBranchPhone/>'.
                    '<cBranchPerson/>'.
                    '<cCusTradeCCode/>'.
                    '<CustomerKCode/>'.
                    '<bCusState>0</bCusState>'.
                    '<ccusbankcode/>'.
                    '<cRelVendor/>'.
                    '<ccusexch_name>人民币</ccusexch_name>'.
                    '<bshop>0</bshop>'.
                    '<bOnGPinStore>0</bOnGPinStore>'.
                    '<bcusdomestic>1</bcusdomestic>'.
                    '<bcusoverseas>0</bcusoverseas>'.
                    '<bserviceattribute>0</bserviceattribute>'.
                    '<ccuscreditcompany>'.$params['cid'].'</ccuscreditcompany>'.
                    '<ccussaprotocol/>'.
                    '<ccusexprotocol/>'.
                    '<ccusotherprotocol/>'.
                    '<ccusimagentprotocol/>'.
                    '<fcusdiscountrate/>'.
                    '<ccussscode/>'.
                    '<ccusmngtypecode>999</ccusmngtypecode>'.
                    '<brequestsign>0</brequestsign>'.
                    '<fExpense/>'.
                    '<fApprovedExpense/>'.
                    '<dTouchedTime/>'.
                    '<dRecentlyInvoiceTime/>'.
                    '<dRecentlyQuoteTime/>'.
                    '<dRecentlyActivityTime/>'.
                    '<dRecentlyChanceTime/>'.
                    '<dRecentlyContractTime/>'.
                    '<cLtcCustomerCode/>'.
                    '<bTransFlag/>'.
                    '<cLtcPerson/>'.
                    '<dLtcDate/>'.
                    '<cLocationSite/>'.
                    '<iCusTaxRate/>'.
                    '<sa_invoicecustomersall>'.
                        '<sa_invoicecustomers>'.
                            '<ccuscode>'.$params['cid'].'</ccuscode>'.
                            '<cinvoicecompany>'.$params['cid'].'</cinvoicecompany>'.
                            '<autoid>1</autoid>'.
                            '<bdefault>True</bdefault>'.
                        '</sa_invoicecustomers>'.
                    '</sa_invoicecustomersall>'.
                '</customer>';
        
        return $str;
    }
    
    /**
     * 生产凭证的实体 - 客户.
     * 
     * @param array $params
     */
    public static function genVoucherEntry_Supplier($params)
    {
        $time = date('Y-m-d H:i:s');
        $str = '<vendor>'.
                    '<code>'. $params['sid'].'</code>'.
                    '<name>'. $params['name'].'</name>'.
                    '<abbrname>'. $params['name'].'</abbrname>'. 
                    '<sort_code>'. $params['city_code'].'</sort_code>'. // 01:北京 02:天津
                    '<domain_code/>'.
                    '<industry/>'.
                    '<address/>'.
                    '<postcode/>'.
                    '<tax_reg_code/>'.
                    '<bank_open/>'.
                    '<bank_acc_number/>'.
                    '<seed_date>'. $params['ctime'].'</seed_date>'.
                    '<legal_man/>'.
                    '<phone/>'.
                    '<fax/>'.
                    '<email/>'.
                    '<contact/>'.
                    '<bp/>'.
                    '<mobile/>'.
                    '<spec_operator/>'.
                    '<discount_rate>0</discount_rate>'.
                    '<credit_rank/>'.
                    '<credit_amount>0</credit_amount>'.
                    '<credit_deadline>0</credit_deadline>'.
                    '<pay_condition/>'.
                    '<receive_site/>'.
                    '<receive_mode/>'.
                    '<head_corp_code>'. $params['sid'].'</head_corp_code>'.
                    '<rec_warehouse/>'.
                    '<super_dept/>'.
                    '<ap_rest>0</ap_rest>'.
                    '<last_tr_date/>'.
                    '<last_tr_money>0</last_tr_money>'.
                    '<last_pay_date/>'.
                    '<last_pay_amount>0</last_pay_amount>'.
                    '<end_date/>'.
                    '<tr_frequency>0</tr_frequency>'.
                    '<tax_in_price_flag>1</tax_in_price_flag>'.
                    '<CreatePerson>demo</CreatePerson>'.
                    '<ModifyPerson>demo</ModifyPerson>'.
                    '<ModifyDate>'. $time.'</ModifyDate>'.
                    '<auth_class/>'.
                    '<barcode/>'.
                    '<self_define1/>'.
                    '<self_define2/>'.
                    '<self_define3/>'.
                    '<self_define4/>'.
                    '<self_define5/>'.
                    '<self_define6/>'.
                    '<self_define7/>'.
                    '<self_define8/>'.
                    '<self_define9/>'.
                    '<self_define10/>'.
                    '<self_define11/>'.
                    '<self_define12/>'.
                    '<self_define13/>'.
                    '<self_define14/>'.
                    '<self_define15/>'.
                    '<self_define16/>'.
                    '<RegistFund/>'.
                    '<EmployeeNum/>'.
                    '<GradeABC>-1</GradeABC>'.
                    '<Memo/>'.
                    '<LicenceDate>0</LicenceDate>'.
                    '<LicenceSDate/>'.
                    '<LicenceEDate/>'.
                    '<LicenceADays/>'.
                    '<BusinessDate>0</BusinessDate>'.
                    '<BusinessSDate/>'.
                    '<BusinessEDate/>'.
                    '<BusinessADays/>'.
                    '<ProxyDate>0</ProxyDate>'.
                    '<ProxySDate/>'.
                    '<ProxyEDate/>'.
                    '<ProxyADays/>'.
                    '<PassGMP>0</PassGMP>'.
                    '<bvencargo>1</bvencargo>'.
                    '<bproxyforeign>0</bproxyforeign>'.
                    '<bvenservice>0</bvenservice>'.
                    '<cVenTradeCCode/>'.
                    '<cvenbankcode/>'.
                    '<cRelCustomer/>'.
                    '<cvenexch_name>人民币</cvenexch_name>'.
                    '<ivengsptype>0</ivengsptype>'.
                    '<ivengspauth>-1</ivengspauth>'.
                    '<cvengspauthno/>'.
                    '<cvenbusinessno/>'.
                    '<cvenlicenceno/>'.
                    '<bvenoverseas>0</bvenoverseas>'.
                    '<bvenaccperiodmng>0</bvenaccperiodmng>'.
                    '<cvenpuomprotocol/>'.
                    '<cvenotherprotocol/>'.
                    '<cvencountrycode/>'.
                    '<cvenenname/>'.
                    '<cvenenaddr1/>'.
                    '<cvenenaddr2/>'.
                    '<cvenenaddr3/>'.
                    '<cvenenaddr4/>'.
                    '<cvenportcode/>'.
                    '<cvenprimaryven/>'.
                    '<fvencommisionrate/>'.
                    '<fveninsuerate/>'.
                    '<bvenhomebranch>0</bvenhomebranch>'.
                    '<cvenbranchaddr/>'.
                    '<cvenbranchphone/>'.
                    '<cvenbranchperson/>'.
                    '<cvensscode/>'.
                    '<comwhcode/>'.
                    '<cvencmprotocol/>'.
                    '<cvenimprotocol/>'.
                    '<iventaxrate/>'.
                    '<dvencreatedatetime>'. $time.'</dvencreatedatetime>'.
                    '<cVenMnemCode/>'.
                    '<cvenbankall></cvenbankall>'.
                '</vendor>';
        
        return $str;
    }
    
    public static function getCityCode($cityId=0, $wid=0)
    {
        $cityCodes = array(
            Conf_City::BEIJING => '01',
            Conf_City::TIANJIN => '02',
            Conf_City::WUHAN => '03',
            Conf_City::CHONGQING => '04',
            Conf_City::LANGFANG => '05',
        );
        
        $wid2City = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING;
        if (!empty($wid) && array_key_exists($wid, $wid2City))
        {
            $currCityid = $wid2City[$wid];
        }
        
        if (!empty($cityId))
        {
            $currCityid = $cityId;
        }
        
        if (array_key_exists($currCityid, $cityCodes))
        {
            return $cityCodes[$currCityid];
        }
        
        return $cityCodes[Conf_City::BEIJING];
    }
    
    // 获取key在数组中的顺序号
    public static function getKeySeqNum($key, $array)
    {
        $c = 0;
        $isSearch = false;
        
        foreach ($array as $k => $v)
        {
            if ($key == $k)
            {
                $isSearch = true;
                break;
            }
            
            $c++;
        }
        
        return $isSearch? $c: false;
    }
}