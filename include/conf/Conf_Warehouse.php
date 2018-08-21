<?php

/**
 * 库房配置
 */
class Conf_Warehouse
{

    /**
     * 锁库开关.
     * 
     * 如果开启锁库，仓库货物的进出都不能执行.
     * 
     * 销售单出库，退货单入库/上架，盘亏，盘盈，采购入库/上架，入库单退货，调拨单出库，调拨单入库
     */
    CONST LOCK_WAREHOUSE = false;
    
	CONST STOCK_HISTORY_IN = 0,             //采购入库
		STOCK_HISTORY_OUT = 1,              //销售出库
		STOCK_HISTORY_CHK_LOSS = 2,         //盘亏
		STOCK_HISTORY_CHK_GAIN = 3,         //盘盈
		STOCK_HISTORY_REFUND_IN = 4,        //销售退货入库
		STOCK_HISTORY_STOCK_SHIFT_IN = 5,   //移库-移入
		STOCK_HISTORY_STOCK_SHIFT_OUT = 6,  //移库-移出
		STOCK_HISTORY_STOCKIN_DEL = 7,      //删除采购入库
		STOCK_HISTORY_STOCKIN_REFUND = 8,   //采购入库退货出库
		STOCK_HISTORY_ORDER_CHG_WID = 9,    //销售订单修改仓库，出库订单回滚 【下线】
		STOCK_HISTORY_REFUND_CHG_WID = 10,  //销售退货单修改仓库【下线】
        STOCK_HISTORY_LOC_SHIFT = 11,       //货位货物转移【不变更库存，货位库存历史使用】
        STOCK_HISTORY_OTHER_STOCK_OUT = 12, //其他出库单
        STOCK_HISTORY_OTHER_STOCK_IN = 13,  //其他出库单
        STOCK_HISTORY_COMBIN = 14,          //组合售卖
        STOCK_HISTORY_SPLIT = 15,           //整转零售

		STOCK_HISTORY_SCRIPT_CANCEL = 100;  //脚本操作
    
    public static $Stock_History_Type = array(
		'-1' => '全部',
		self::STOCK_HISTORY_IN => '采购入库',
		self::STOCK_HISTORY_OUT => '销售出库',
		self::STOCK_HISTORY_CHK_LOSS => '盘亏',
		self::STOCK_HISTORY_CHK_GAIN => '盘盈',
		self::STOCK_HISTORY_REFUND_IN => '退款入库',
		self::STOCK_HISTORY_STOCK_SHIFT_IN => '移库(入)',
		self::STOCK_HISTORY_STOCK_SHIFT_OUT => '移库(出)',
		self::STOCK_HISTORY_STOCKIN_DEL => '删除入库',
		self::STOCK_HISTORY_STOCKIN_REFUND => '退货出库',
		self::STOCK_HISTORY_ORDER_CHG_WID => '订单修改仓库',
		self::STOCK_HISTORY_REFUND_CHG_WID => '退货单修改仓库',
		self::STOCK_HISTORY_SCRIPT_CANCEL => '后台脚本操作',
        self::STOCK_HISTORY_LOC_SHIFT => '货位货物转移',
        self::STOCK_HISTORY_OTHER_STOCK_OUT => '其他出库单',
        self::STOCK_HISTORY_OTHER_STOCK_IN => '其他入库单',
        self::STOCK_HISTORY_COMBIN  => '组合售卖',
        self::STOCK_HISTORY_SPLIT   => '整转零售',
	);
    
    public static function stockHistoryTypeDetails()
    {
        return array(
            self::STOCK_HISTORY_IN  => array('name'=>'采购入库', 'href'=> '/warehouse/edit_stock_in.php?id=%d'),
            self::STOCK_HISTORY_OUT => array('name'=>'销售出库', 'href'=>'/order/order_detail.php?oid=%d'),
            self::STOCK_HISTORY_CHK_LOSS => array('name'=>'盘亏', 'href'=>''),
            self::STOCK_HISTORY_CHK_GAIN => array('name'=>'盘盈', 'href'=>''),
            self::STOCK_HISTORY_REFUND_IN=> array('name'=>'客户退货', 'href'=>'/order/edit_refund_new.php?rid=%d'),
            self::STOCK_HISTORY_STOCK_SHIFT_IN  => array('name'=>'移库(入)',  'href'=>'/warehouse/stock_shift_detail.php?ssid=%d'),
            self::STOCK_HISTORY_STOCK_SHIFT_OUT => array('name'=>'移库(出)',  'href'=>'/warehouse/stock_shift_detail.php?ssid=%d'),
            self::STOCK_HISTORY_STOCKIN_REFUND  => array('name'=>'采购退货',   'href'=>'/warehouse/stockin_refund_detail.php?srid=%d'),
            self::STOCK_HISTORY_OTHER_STOCK_OUT => array('name'=>'其他出库单', 'href'=>'/warehouse/other_stock_out_order_detail.php?oid=%d'),
            self::STOCK_HISTORY_OTHER_STOCK_IN  => array('name'=>'其他入库单', 'href'=>'/warehouse/other_stock_in_order_detail.php?oid=%d'),
            self::STOCK_HISTORY_COMBIN  => array('name'=>'组合售卖', 'href'=>'/shop/processed_order_detail.php?id=%d'),
            self::STOCK_HISTORY_SPLIT   => array('name'=>'整转零售', 'href'=>'/shop/processed_order_detail.php?id=%d'),
            
            self::STOCK_HISTORY_SCRIPT_CANCEL   => array('name'=>'系统调整', 'href'=>''),
        );
    }

    /**
     * 货位：虚拟货位标识.
     * 
     * @notice 
     *      self::VFLAG_PREFIX 不能被修改
     */
    const VFLAG_PREFIX = 'VFLoc';
    
    const VFLAG_STOCK_IN        = 1;
    const VFLAG_SHIFT           = 2;
    const VFLAG_ORDER_REFUND    = 3;
    
    const VFLAG_DAMAGED         = 1001;
    const VFLAG_LOSS            = 1002;
    
    public static $Virtual_Flags = array(
        self::VFLAG_STOCK_IN        => array('name'=>'入库单',   'flag'=>'VFLoc-1',),
        self::VFLAG_SHIFT           => array('name'=>'移库单',   'flag'=>'VFLoc-2',),
        self::VFLAG_ORDER_REFUND    => array('name'=>'销售退货单','flag'=>'VFLoc-3',),
        
        self::VFLAG_DAMAGED         => array('name'=>'残损货位',  'flag'=>'VFLoc-1001'),
        self::VFLAG_LOSS            => array('name'=>'盘亏货位',  'flag'=>'VFLoc-1002'),
    );

    /**
     * 仓库ID定义
     */
    CONST WID_BJ_1 = 1;

    private static $WAREHOUSE_INFOS = array(
        self::WID_BJ_1 => array('name'=>'北京仓库#1', 'city'=>Conf_City::BEIJING, 'poi'=>array('lng'=>'116.343725', 'lat'=>'39.852428')),
    );
    
    /**
     * 获取仓库的名称.
     */
    public static function getNameOfWarehouses($wids=null)
    {
        $warehouses = array();
        
        
        if (!empty($wids))
        {
            $_wids = is_string($wids)? explode(',', $wids): $wids;
            
            foreach($_wids as $_wid)
            {
                $warehouses[$_wid] = self::$WAREHOUSE_INFOS[$_wid]['name'];
            }
        }
        else
        {
            foreach(self::$WAREHOUSE_INFOS as $_wid => $info)
            {
                $warehouses[$_wid] = $info['name'];
            }
        }
        
        return $warehouses;
    }
    
    /**
     * 获取城市下的仓库.
     */
    public static function getWarehousesOfCity($cityId)
    {
        $warehouses = array();
        
        foreach(self::$WAREHOUSE_INFOS as $_wid => $info)
        {
            if ($info['city'] == $cityId)
            {
                $warehouses[$_wid] = $info['name'];
            }
        }
        
        return $warehouses;
    }
    
    public static function getCityOfWarehouse($wid)
    {
        return self::$WAREHOUSE_INFOS[$wid]['city'];
    }

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    

    /**
	 * 仓库编号
	 */
	public static $WAREHOUSES = array(
        // 华北地区
		self::WID_BJ_1 => '3#玉泉营仓库',
        
    );

    /**
     * 下线仓库.
     */
    public static $Offline_Warehouse = array(
        //self::WID_3, 
    );
    
  

	public static $LOCATION = array(
//		self::WID_3         => array('lng' => '116.343725', 'lat' => '39.852428',),
//		self::WID_4         => array('lng' => '116.484653', 'lat' => '40.028131',),
//		self::WID_5         => array('lng' => '116.766895', 'lat' => '39.847127',),
//        self::WID_6         => array('lng' => '116.239767', 'lat' => '40.036149',),
//        self::WID_7         => array('lng' => '116.239767', 'lat' => '40.036149',),
//        self::WID_8         => array('lng' => '116.350757', 'lat' => '39.860674',),
//        self::WID_101       => array('lng' => '116.128082', 'lat' => '40.08748',),
//		self::WID_TJ1       => array('lng' => '117.259284', 'lat' => '39.076482',),
//        self::WID_TJ2       => array('lng' => '117.417611', 'lat' => '39.004673',),
//        self::WID_WH1       => array('lng' => '114.241149', 'lat' => '30.595838',),
//        self::WID_LF1       => array('lng' => '116.741849', 'lat' => '39.610803',),
//        self::WID_CQ1       => array('lng' => '106.445914', 'lat' => '29.56101',),
//        self::WID_CQ_5001   => array('lng' => '106.519121', 'lat' => '29.667917',),
//        self::WID_LF_COOP1  => array('lng' => '116.745948', 'lat' => '39.501103',),
//        self::WID_LF_COOP2  => array('lng' => '116.700925', 'lat' => '39.554936'),
//        self::WID_CHD1      => array('lng' => '104.010817', 'lat' => '30.614321'),
//        self::WID_QD1       => array('lng' => '120.377005', 'lat' => '36.325916'), 
	);

	public static $DIAODU_MOBILE = array(
//		self::WID_3 => '15922262739',
//		self::WID_4 => '18410432202',
//		self::WID_5 => '18210965728',
//        self::WID_6 => '13240064508',
//        self::WID_7 => '13240064508',
//		self::WID_8 => '18631796862',
//		self::WID_TJ1 => '18709395437',
//        self::WID_TJ2 => '17717732518',
//        self::WID_WH1 => '18210965728',
//        self::WID_101 => 'wait.......',
//        self::WID_LF1 => 'wait.......',
//        self::WID_LF_COOP1 => '13930661300',
//        self::WID_LF_COOP2 => 'sorry',
//        self::WID_CHD1 => 'waiting...',
//        self::WID_QD1 => 'waiting...',
	);

	public static $WAREHOUSE_PICKING_AREA = array(
//		array('id' => 'all', 'name' => '库区'),
		array('id' => 'A', 'name' => 'A'),
		array('id' => 'B', 'name' => 'B'),
		array('id' => 'C', 'name' => 'C'),
		array('id' => 'D', 'name' => 'D'),
		array('id' => 'tmp', 'name' => '临'),
	);

	public static function getWarehousesOfCityxx($city=0, $type='all')
	{
		if ($city>0)
		{
            $warehouses = array();
            $wids = self::$WAREHOUSE_CITY[$city];
            foreach(self::$WAREHOUSES as $wid => $name)
            {
                if(in_array($wid,$wids))
                {
                    $warehouses[$wid] = $name;
                }
            }
		}else{
			$warehouses = self::$WAREHOUSES;
		}

        return self::getWarehouseByAttr($type, $warehouses);
	}
    
       
    
    

    public static $STOCKTAKING_REASONS = array(
        1 => '商品残损',
        2 => '领用出库',
        3 => '错漏配',
        4 => '收退问题',
        5 => '盘库差异',
        6 => '规格混淆',
        7 => '商品暂存',
        8 => '调拨差异',
        9 => '系统原因',
        10 => '其他原因',
        11 => '仓储作业破损',
        12 => '售后退货破损',
        13 => '库内自然损耗',
        14 => '司机退货破损',
        15 => '到货破损',
        16 => '调拨破损',
        17 => '质量问题',
        18 => '装卸破损',
        19 => '运输破损',
        20 => '仓储自用',
        21 => '部门自用',
        22 => '换货出库',
        23 => '换货入库',
    );

    //获取出入库历史原因
    public static function getStockHistoryReasons($type="stock_history")
    {
        $data = self::$STOCKTAKING_REASONS;
        switch ($type)
        {
            case 'stock_history':
                $data = array_slice($data, 0, 10, true);
                break;
            case 'stock_history_log':
                break;
        }

        return $data;
    }

    /**
     * 获取商家仓库
     */
    public static function getSellerWarehouse()
    {
        return array(self::WID_LF_COOP1 => self::$WAREHOUSES[self::WID_LF_COOP1]);
    }

    const ORDER_VNUM_FLAG_DF    = 0,    //缺货-未标记
          ORDER_VNUM_FLAG_LACK  = 1,    //外采
          ORDER_VNUM_FLAG_LATER = 2;    //晚到
    const ORDINARY_STOCKOUT_LATER = 2;//晚到

    public static function getOrderVnumDealType()
    {
        return array(
            self::ORDER_VNUM_FLAG_DF    => '缺货',
            self::ORDER_VNUM_FLAG_LACK  => '外采',
            self::ORDER_VNUM_FLAG_LATER => '在途',
        );
    }
}
