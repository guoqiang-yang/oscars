<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    
    private $lineId;
    private $optype;
    private $addOid;
    private $oids;

    private $html;
    
    protected function getPara()
    {
        $this->lineId = Tool_Input::clean('r', 'line_id', TYPE_UINT);
        $this->optype = Tool_Input::clean('r', 'optype', TYPE_STR);
        $this->addOid = Tool_Input::clean('r', 'add_oid', TYPE_UINT);
        $this->oidAndDrivers = Tool_Input::clean('r', 'oids', TYPE_STR);
    }

    protected function main()
    {
        if ($this->lineId)
        {
            $lineInfo = Logistics_Api::getLineDetail($this->lineId);
        }
        
        switch ($this->optype)
        {
            case 'modify_order':    // 修改线路订单
                $oo = new Order_Order();
                $orderInfos = $oo->getBulk($lineInfo['_oids']);
                
                foreach($orderInfos as &$oinfo)
                {
                    $oinfo['_address'] = explode(Conf_Area::Separator_Construction, $oinfo['address']);
                    
                    $timestamp = strtotime($oinfo['delivery_date']);
                    $hour = date('H', $timestamp);
                    $oinfo['delivery_date'] = date('m-d', $timestamp).' '.$hour.'点-'.($hour+Conf_Order::INTER_HOUR).'点';
                }
                
                $this->smarty->assign('order_infos', $orderInfos);
                
                break;
            case 'query_modify_order': // 查询待添加的订单
                $orderInfo = Order_Api::getOrderInfo($this->addOid);
                $this->smarty->assign('add_order_info', $orderInfo);
                break;
            case 'chg_carmodel':    // 更换线路车型
                $carModelInfos = $this->_getCarModelsInfos($lineInfo);
                $this->smarty->assign('car_models', $carModelInfos);
                $this->smarty->assign('all_car_models', Conf_Driver::$CAR_MODEL);
                break;
            
            case 'cancel':  // 取消排线
                
                break;
            
            case 'reject':  // 司机拒单
                $drivers = $this->_getDriversInLine($lineInfo);
                $this->smarty->assign('drivers', $drivers);
                
                break;
            case 'arrive':  //司机订单信息
                $orders = $this->_getLineOrder($this->lineId);
                $oids = array_keys($orders);
                $coop_orders = Logistics_Coopworker_Api::getByOids($oids);
                $dids = Tool_Array::getFields($coop_orders, 'cuid');

                $drivers = Logistics_Api::getByDids($dids);

                foreach ($drivers as $driver)
                {
                    $driversWithPk[$driver['did']] = $driver;
                }

                $new_coop_orders = array();
                foreach ($coop_orders as $coop_order)
                {
                    if ($coop_order['obj_type'] == 1 && $coop_order['user_type'] == 1 && $coop_order['type'] == 1 && $coop_order['delivery_time'] > 0)
                    {
                        $new_coop_orders[] = array(
                            'did' => $coop_order['cuid'],
                            'driver_name' => $driversWithPk[$coop_order['cuid']]['name'],
                            'address' => $orders[$coop_order['obj_id']]['address'],
                            'oid' => $coop_order['obj_id'],
                            'arrival_time' => $coop_order['arrival_time'],
                        );
                    }
                }


                $this->smarty->assign('coop_orders', $new_coop_orders);
                $this->smarty->assign('line_id', $this->lineId);


                break;
            case 'ensure_arrive':  //送达
                $oidAndDrivers = explode(',', rtrim($this->oidAndDrivers, ','));
                $oids = array();
                foreach ($oidAndDrivers as $oidAndDriver)
                {
                    $arr = explode('-', $oidAndDriver);
                    $oids[] = $arr[0];
                }

                $coop_orders = Logistics_Coopworker_Api::getByOids($oids);
                $orders = $this->_getOrders($oids);

                $dids = Tool_Array::getFields($coop_orders, 'cuid');

                $drivers = Logistics_Api::getByDids($dids);

                foreach ($drivers as $driver)
                {
                    $driversWithPk[$driver['did']] = $driver;
                }

                $new_coop_orders = array();
                foreach ($coop_orders as $coop_order)
                {
                    if ($coop_order['obj_type'] == 1 && $coop_order['user_type'] == 1 && in_array($coop_order['obj_id'].'-'.$coop_order['cuid'], $oidAndDrivers)
                    && $coop_order['type'] == 1 && $coop_order['delivery_time'] > 0)
                    {
                        $new_coop_orders[] = array(
                            'did' => $coop_order['cuid'],
                            'driver_name' => $driversWithPk[$coop_order['cuid']]['name'],
                            'address' => $orders[$coop_order['obj_id']]['address'],
                            'oid' => $coop_order['obj_id'],
                            'arrival_time' => $coop_order['arrival_time'],
                        );
                    }
                }


                $this->smarty->assign('coop_orders', $new_coop_orders);
                $this->smarty->assign('line_id', $this->lineId);

                break;
            default:
                throw new Exception('非法操作！');
        }
        
        $this->smarty->assign('lineid', $this->lineId);
        $this->smarty->assign('optype', $this->optype);
        $this->smarty->assign('line_info', $lineInfo);
        $this->html = $this->smarty->fetch('logistics/aj_get_order_line_info.html');
    }
    
    protected function outputBody()
    {
        $response = new Response_Ajax();
		$response->setContent(array('html'=>  $this->html));
		$response->send();
        
		exit;
    }
    
    private function _getDriversInLine($lineInfo)
    {
        $ldq = new Logistics_Driver_Queue();
        $driverQueue = $ldq->getByLineid($this->lineId);
        $didP1 = Tool_Array::getFields($driverQueue, 'did');
        
        $lc = new Logistics_Coopworker();
        $coopDrivers = $lc->getByOids($lineInfo['_oids'], Conf_Base::COOPWORKER_DRIVER);
        $didP2 = Tool_Array::getFields($coopDrivers, 'cuid');
        
        // 司机信息
        $dids = array_merge($didP1, $didP2);
        $driverInfos = array();
        if (!empty($dids))
        {
            $ld = new Logistics_Driver();
            $driverInfos = Tool_Array::list2Map($ld->getByDids($dids), 'did');
        }
        
        $drivers = array('can_reject'=>array(), 'no_reject'=>array());
        $carModels = Conf_Driver::$CAR_MODEL;
        foreach($coopDrivers as $cdriver)
        {
            $did = $cdriver['cuid'];
            $drivers['no_reject'][$did] = array(
                'did' => $did,
                'name' => $driverInfos[$did]['name'],
                'mobile' => $driverInfos[$did]['mobile'],
                'car_model' => $driverInfos[$did]['car_model'],
                'car_desc' => $carModels[$driverInfos[$did]['car_model']],
            );
        }
        foreach($driverQueue as $qdriver)
        {
            $did = $qdriver['did'];
            if (!array_key_exists($did, $drivers['no_reject']))
            {
                $drivers['can_reject'][$did] = array(
                    'did' => $did,
                    'name' => $driverInfos[$did]['name'],
                    'mobile' => $driverInfos[$did]['mobile'],
                    'car_model' => $driverInfos[$did]['car_model'],
                    'car_desc' => $carModels[$driverInfos[$did]['car_model']],
                );
            }
        }
        
        return $drivers;
    }
    
    private function _getCarModelsInfos($lineInfo)
    {
        $_models = explode(Conf_Coopworker::Orderline_CarModel_Sp2, $lineInfo['car_models']);
        $carModels = array();
        foreach($_models as $cm)
        {
            $carModels[] = explode(Conf_Coopworker::Orderline_CarModel_Sp1, $cm);
        }
        
        $ldq = new Logistics_Driver_Queue();
        $driverQueue = $ldq->getByLineid($this->lineId);
        $dids = Tool_Array::getFields($driverQueue, 'did');
        
        // 司机信息
        $driverInfos = array();
        if (!empty($dids))
        {
            $ld = new Logistics_Driver();
            $driverInfos = Tool_Array::list2Map($ld->getByDids($dids), 'did');
        }
        
        $carModelDescs = Conf_Driver::$CAR_MODEL;
        $parseCarModels = array();
        foreach($carModels as $_model)
        {
            $_carModel = substr($_model[0], 1);
            $_parseCarModels['car_model'] = $_carModel;
            $_parseCarModels['price'] = $_model[2];
            $_parseCarModels['desc'] = $carModelDescs[$_carModel];
            
            if($_model[1] == 1) //已派单
            {
                $_parseCarModels['driver'] = array();
                foreach($driverQueue as $k=>$driver)
                {
                    if ($driver['car_model'] == $_carModel)
                    {
                        
                        $_parseCarModels['step'] = $driver['step'];
                        $_parseCarModels['step_desc'] = Conf_Driver::$STEP_DESC[$driver['step']];
                        $_parseCarModels['driver']['did'] = $driver['did'];
                        $_parseCarModels['driver']['name'] = $driverInfos[$driver['did']]['name'];
                        $_parseCarModels['driver']['mobile'] = $driverInfos[$driver['did']]['mobile'];
                        unset($driverQueue[$k]);
                        break;
                    }
                }
            }
            else
            {
                $_parseCarModels['step'] = 0;
                $_parseCarModels['step_desc'] = '未派单';
                $_parseCarModels['driver'] = array();
            }
            
            $parseCarModels[] = $_parseCarModels;
        }
        
        return $parseCarModels;
    }
    private function _getLineOrder($line_id)
    {
        $lineInfo = Logistics_Api::getLineDetail($line_id);
        $orders = array();
        //订单信息
        if (!empty($lineInfo['oids']))
        {
            $oids = explode(',', $lineInfo['oids']);
            $driver_orders = Logistics_Coopworker_Api::getByOids($oids);


            $orders = Order_Api::getListByPk($oids);
            foreach ($oids as $oid)
            {
                Order_Helper::formatOrder($orders[$oid]);
                $orders[$oid]['address'] = $orders[$oid]['_city'].$orders[$oid]['_district'].' '.$orders[$oid]['address'];
                foreach ($driver_orders as $driver_order)
                {

                    if ($driver_order['arrival_time'] == '0000-00-00 00:00:00' && $driver_order['oid'] == $oid)
                    {
                        $orders[$oid]['is_arrive'] = 2;
                    }
                    else
                    {
                        $orders[$oid]['is_arrive'] = 1;
                    }
                }
            }
        }
        return $orders;
    }
    private function _getOrders($oids)
    {
        //订单信息
        if (!empty($oids))
        {
            $orders = Order_Api::getListByPk($oids);
            foreach ($oids as $oid)
            {
                Order_Helper::formatOrder($orders[$oid]);
                $orders[$oid]['address'] = $orders[$oid]['_city'].$orders[$oid]['_district'].' '.$orders[$oid]['address'];
            }
        }
        return $orders;
    }
}

$app = new App('pub');
$app->run();