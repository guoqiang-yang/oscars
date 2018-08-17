<?php
/**
 * 合作工人类 - 第三方的司机，搬运工.
 * 
 */


class Order_Coopworker_Api
{
    /**
     * 获取订单已经安排的合作工人.
     * 
     * @param type $oid
     * @param type $type
     * @param bool $needWorkerInfo
     */
    public static function getOrderOfWorkers($oid, $type=0, $needWorkerInfo=false)
    {
        $oc = new Order_Coopworker();
        
        $ret = $oc->getByOid($oid, 0, $type);
        
        if (!empty($ret) && $needWorkerInfo)
        {
            self::_supplyCoopworkerInfos($ret); 
        }
        
        return $ret;
        
    }
    
    public static function getOrdersOfWorkers($oids, $type=0)
    {
	    if (empty($oids)) return array();
	    
        $oc = new Order_Coopworker();
        $_ret = $oc->getByOids($oids, $type);
        
        $ret = array();
        if (!empty($_ret))
        {
            self::_supplyCoopworkerInfos($_ret);
            
            foreach($_ret as $one)
            {
                $ret[$one['oid']][] = $one;
            }
        }
        
        return $ret;
    }
    
    public static function saveCoopworkerForOrder($data)
    {
        $oc = new Order_Coopworker();
        $id = $oc->saveWorkerForOrder($data);
        
        return $id;
    }
    
    public static function updateOrderCoopworker($oid, $cuid, $type, $data)
    {
        $oc = new Order_Coopworker();
        $ret = $oc->update($oid, $cuid, $type, $data);
        
        return $ret;
    }
    
    
    private static function _supplyCoopworkerInfos(&$orderOfCoopworker)
    {
        $driverIds = $carrierIds = array();
        $driverInfos = $carrierInfos = array();
        
        foreach($orderOfCoopworker as $oner)
        {
            if ($oner['user_type'] == Conf_Base::COOPWORKER_DRIVER)
            {
                $driverIds[] = $oner['cuid'];
            } 
            else if ($oner['user_type'] == Conf_Base::COOPWORKER_CARRIER)
            {
                $carrierIds[] = $oner['cuid'];
            }
        }

        // 司机
        if (!empty($driverIds))
        {
            $ld = new Logistics_Driver();
            $driverInfos = Tool_Array::list2Map($ld->getByDids($driverIds), 'did');
        }
        // 搬运工
        if (!empty($carrierIds))
        {
            $lc = new Logistics_Carrier();

            $carrierInfos = Tool_Array::list2Map($lc->getByCids($carrierIds), 'cid');
        }
        
        foreach($orderOfCoopworker as &$oner)
        {
            $oner['info'] = $oner['user_type']==Conf_Base::COOPWORKER_DRIVER? 
                $driverInfos[$oner['cuid']]: $carrierInfos[$oner['cuid']];
        }
        
    }

    public static function getWorkerOrderList($cuid, $type, $start, $num, $order, $startTime, $endTime)
    {
        $total = 0;
        $oc = new Order_Coopworker();
        $searchConf = array(
            'type' => $type,
            'btime' => $startTime,
            'etime' => $endTime,
        );
        $ret = $oc->getByWorkerWithOrder($cuid, $searchConf, $start, $num, $order, $total);

        if ($ret['total'] == 0)
        {
            return array('list' => array(), 'total' => 0);
        }

        $list = $ret['data'];

        //客户cid转成客户信息customer
        $cc = new Crm_Customer();
        $cc->appendInfos($list);

        //把录单人suid转换成名称信息
        $as = new Admin_Staff();
        $as->appendSuers($list);

        //格式化订单信息 日期, 状态
        Order_View::formatOrders($list);

        Tool_Array::sortByField($list, 'oid');

        return array('list' => $list, 'total' => $ret['total']);
    }
    
}