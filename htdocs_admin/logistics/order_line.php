<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $query;
    private $search;
    private $maxOid;
    private $orderList = array();
    private $orderListFull = array();
    private $warehousePoint;
    private $sourceOrderDetail;
    
    protected function getPara()
    {
        $this->query = array(
            'wid' => $this->getWarehouseId(),
            'oid' => Tool_Input::clean('r', 'oid', TYPE_STR),
            'cid' => Tool_Input::clean('r', 'cid', TYPE_STR),
            'delivery_data' => Tool_Input::clean('r', 'delivery_data', TYPE_STR),
//            'delivery_btime' => max(Tool_Input::clean('r', 'delivery_btime', TYPE_UINT), 8),
//            'delivery_etime' => min(Tool_Input::clean('r', 'delivery_etime', TYPE_UINT), 21),
        );
        
        $this->query['delivery_data'] = !empty($this->query['delivery_data'])?$this->query['delivery_data']: date('Y-m-d');
//        $this->query['delivery_etime'] = !empty($this->query['delivery_etime'])? $this->query['delivery_etime']: 21;
//        $this->query['delivery_etime'] = $this->query['delivery_etime']<=$this->query['delivery_btime']?
//                                        $this->query['delivery_btime']: $this->query['delivery_etime'];
//        $deliveryBtime = $this->query['delivery_btime']<10? '0'.$this->query['delivery_btime']: $this->query['delivery_btime'];
//        
        $this->search = array(
            'wid' => $this->query['wid'],
            'oid' => $this->query['oid'],
            'cid' => $this->query['cid'],
            'delivery_btime' => $this->query['delivery_data']. ' 00:00:00',
            'delivery_etime' => $this->query['delivery_data']. ' 23:59:59',
        );
    }
    
    protected function main()
    {
        $this->orderListFull = Logistics_Order_Api::getUnlineOrders($this->search);
        $map = Tool_Array::list2Map($this->orderListFull, 'source_oid', 'oid');
        unset($map[0]);

        //最大的订单id
        $this->maxOid = max(Tool_Array::getFields($this->orderListFull, 'oid'));

        $oo = new Order_Order();
        $this->sourceOrderDetail = $oo->getList(array_keys($map));

        foreach($this->orderListFull as &$oinfo)
        {
            if (array_key_exists($oinfo['oid'], $map))
            {
                $oinfo['add_oid'] = $map[$oinfo['oid']];
            }

            //补单经度向右平移0.002度
            $this->orderList[] = array(
                'oid' => $oinfo['oid'],
                'lng' => $oinfo['lng'],
                'lat' => $oinfo['lat'],
                'mapimg' => $oinfo['mapimg'],
            );
        }

        $warehousePoint = Conf_Warehouse::$LOCATION;
        if (!empty($this->query['wid']))
        {
            $this->warehousePoint[] = $warehousePoint[$this->query['wid']];
        }
        else
        {
            $myCity = City_Api::getCity();
            $wid2city = Conf_Warehouse::$WAREHOUSE_CITY_MAPPING;
            foreach ($warehousePoint as $_wid => $_point)
            {
                if ($wid2city[$_wid] == $myCity['city_id'])
                {
                    $this->warehousePoint[] = $_point;
                }
            }
        }
        
        $this->addFootJs(array(
                             'js/apps/logistics.js',
                             'http://api.map.baidu.com/api?v=2.0&ak=YnOoqPMg9gnlLYqO2ew3LwQI',
                         ));
    }
    
    protected function outputBody()
    {
        $this->smarty->assign('query', $this->query);
        $this->smarty->assign('allow_worehouses', App_Admin_Web::getAllowedWids4User());
        $this->smarty->assign('warehouse_points', Tool_Array::jsonEncode($this->warehousePoint));
        $this->smarty->assign('car_model', Conf_Driver::$CAR_MODEL);
        $this->smarty->assign('source_order_detail', $this->sourceOrderDetail);
        $this->smarty->assign('today', date('Y-m-d'));
        $this->smarty->assign('tomorrow', date('Y-m-d', strtotime('+1 day')));
        $this->smarty->assign('order_list', Tool_Array::jsonEncode($this->orderList));
        $this->smarty->assign('order_list_full', $this->orderListFull);
        $this->smarty->assign('step_list', Conf_Order::$ORDER_STEPS);
        $this->smarty->assign('max_oid', empty($this->maxOid)?0:$this->maxOid);
        $this->smarty->assign('aftersale_types', Conf_Order::$AFTERSALE_TYPES);

        $this->smarty->display('logistics/order_line.html');
    }
}

$app = new App();
$app->run();