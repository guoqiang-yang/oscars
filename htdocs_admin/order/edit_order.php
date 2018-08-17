<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $oid;
    private $cid;
    private $copy;
    private $order;
    private $coupons;
    private $deliverTime = array();
    private $area;
    private $distinct;
    private $city;
    private $carryFee;
    private $carryFeeEle;
    private $orderProducts;
    private $new;
    private $privilege;
    private $floor;
    private $sandPrice;
    private $otherPrice;
    private $communityInfo;
    private $customer;
    private $isInPicking = FALSE;
    private $changeCityList = array();
    private $activity_products_amount = 0;

    private $showCitys = array();

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->copy = Tool_Input::clean('r', 'copy', TYPE_UINT);
        $this->new = Tool_Input::clean('r', 'new', TYPE_UINT);
    }

    protected function checkPara()
    {
        if ($this->order['step'] >= 2)
        {
            throw new Exception('order: order has been confirmed');
        }
    }

    protected function main()
    {
        $this->order = Order_Api::getOrderInfo($this->oid);
        $this->order['_op_note'] = Order_Api::parseOpNote($this->order['op_note']);

        if (!in_array($this->order['saler_suid'], $this->_user['team_member']) && Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW))
        {
            if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_CS_NEW))
            {
                //nothing
            }
            else
            {
                throw new Exception('order:order not belong to you');
            }
        }

        if ($this->new)
        {
            $this->order['service'] = -1;
        }
        $orderProducts = Order_Api::getOrderProducts($this->oid);
        $this->orderProducts = Privilege_Api::getRealBuyProducts($orderProducts['products'], $this->oid, $this->activity_products_amount);

        // 获取小区的详细信息
        if (!empty($this->order['community_id']))
        {
            $this->communityInfo = Order_Community_Api::get($this->order['community_id']);
        }

        $this->deliverTime = Order_Api::getDeliveryTime4Admin();

        if (0 == $this->order['delivery_date'])
        {
            $this->order['delivery_date'] = '';
            $this->order['delivery_time'] = '';
        }
        else
        {
            $this->order['delivery_time'] = date('G', strtotime($this->order['delivery_date']));
            $this->order['delivery_time_end'] = date('G', strtotime($this->order['delivery_date_end']));
            $this->order['delivery_date'] = date('Y-m-d', strtotime($this->order['delivery_date']));
        }

        $this->city = Conf_Area::$CITY;
        $this->distinct = Conf_Area::$DISTRICT;
        $this->area = Conf_Area::$AREA;

        if ($this->new == 1)
        {
            $this->order['freight'] = Logistics_Api::calFreightByAddress($this->oid, $this->order['city'], $this->order['district'], $this->order['ring_road']);
        }

        for ($i = 1; $i <= 30; $i++)
        {
            $this->floor[$i] = $i;
        }
        
        //计算砂石类和非砂石类价格
        $allCitys = Conf_City::$CITY;
        $pCitys = array();
        foreach ($this->orderProducts as $product)
        {
            //if (in_array($product['pid'], Conf_Order::$SAND_CEMENT_BRICK_PIDS))
            if (Shop_Api::isSandCementBrickBySkuinfo($product['sku']))
            {
                $this->sandPrice += $product['num'] * $product['price'];
            }
            else
            {
                $this->otherPrice += $product['num'] * $product['price'];
            }

            if (!$this->isInPicking && $product['picked_time'] > 0)
            {
                $this->isInPicking = TRUE;
            }

            $pCitys[$allCitys[$product['city_id']]] = 1;
        }
        $this->sandPrice = round($this->sandPrice / 100, 2);
        $this->otherPrice = round($this->otherPrice / 100, 2);

        $this->customer = Crm2_Api::getCustomerInfo($this->order['cid'], FALSE, FALSE);

        $this->showCitys['p'] = implode(',', array_keys($pCitys));
        $this->showCitys['o'] = $allCitys[$this->order['city_id']];

        $this->_setChangeCityList();

        $this->addFootJs(array(
                             'js/core/autosuggest.js',
                             'js/apps/close_alert.js',
                             'js/core/area.js',
                             'js/apps/order.js',
                             'js/apps/order_activity.js',
                             'js/apps/order_fee.js',
                             'js/apps/delivery_date_check.js',
                             'http://api.map.baidu.com/api?v=2.0&ak=' . Conf_Base::BAIDU_KEY,
                         ));
        $this->addCss(array('css/autosuggest.css'));
    }

    protected function outputBody()
    {
        $cityInfo = City_Api::getCity();
        $cityId = $cityInfo['city_id'];
        //$warehouses = Conf_Warehouse::getWarehousesOfCity($cityId, 'customer');
        $warehouses = Appconf_Warehouse::wid4CreateOrder($this->order['city_id']);
        //获取库存
        $sids = Tool_Array::getFields($this->orderProducts, 'sid');
        $ws = new Warehouse_Stock();
        $wid = 0;
        if (count($warehouses) > 1)
        {
            if (in_array($this->order['wid'], array_keys($warehouses)))
            {
                $wid = $this->order['wid'];
            }
        }
        else
        {
            $wid = reset(array_keys($warehouses));
        }
        if ($wid > 0)
        {
            $stocks = Tool_Array::list2Map($ws->getBulk($wid, $sids), 'sid');
        }
        else
        {
            $stocks = array();
        }

        // 添加商品的对话框
        $this->smarty->assign('payment_types', Conf_Base::getPaymentTypes());
        $this->smarty->assign('coupons', $this->coupons);
        $this->smarty->assign('order', $this->order);
        $this->smarty->assign('copy', $this->copy);
        $this->smarty->assign('order_steps', Conf_Order::getOrderStepNames());
        $this->smarty->assign('warehouses', $warehouses);
        //$this->smarty->assign('can_change_warehouse', Conf_Warehouse::isOwnWid($wid));
        $this->smarty->assign('delivery_types', Conf_Order::$DELIVERY_TYPES);
        $this->smarty->assign('delivery_type', isset($this->order) ? $this->order['delivery_type'] : Conf_Order::DELIVERY_EXPRESS);
        $this->smarty->assign('delivery_time', $this->deliverTime);
        $this->smarty->assign('city', Tool_Array::jsonEncode($this->city));
        $this->smarty->assign('distinct', Tool_Array::jsonEncode($this->distinct));
        $this->smarty->assign('area', Tool_Array::jsonEncode($this->area));
        $this->smarty->assign('carry_fee', $this->carryFee);
        $this->smarty->assign('carry_fee_ele', $this->carryFeeEle);
        $this->smarty->assign('order_products', $this->orderProducts);
        $this->smarty->assign('privilege', $this->privilege);
        $this->smarty->assign('date', date('Y-m-d'));
        $this->smarty->assign('hour', date('G'));
        $this->smarty->assign('floor', $this->floor);
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);
        $this->smarty->assign('sand_price', $this->sandPrice);
        $this->smarty->assign('other_price', $this->otherPrice);
        $this->smarty->assign('community_info', $this->communityInfo);
        $this->smarty->assign('customer', $this->customer['customer']);
        $this->smarty->assign('today', date('Y-m-d'));
        $this->smarty->assign('hour', date('G'));
        $this->smarty->assign('stocks_list', $stocks);
        $this->smarty->assign('city_id', $cityId);
        $this->smarty->assign('is_in_picking', $this->isInPicking);
        $this->smarty->assign('show_city', $this->showCitys);
        $this->smarty->assign('all_citys', Conf_City::$CITY);
        $this->smarty->assign('city_poi_4_community', json_encode(Conf_City::cityPOI($this->order['city_id'])));
        $this->smarty->assign('activity_products_amount', $this->activity_products_amount);
        
        $this->smarty->assign('carriage_fee_rel', $this->_calReferCarriageFee());

        $this->smarty->assign('change_city_list', $this->changeCityList);
        if ($this->new)
        {
            $this->smarty->display('order/add_order2.html');
        }
        else
        {
            $this->smarty->display('order/edit_order.html');
        }
    }

    private function _setChangeCityList()
    {
        $this->changeCityList = array();

        // 自营城市的订单
        $selfCities = Conf_City::getSelfCities();
        if (array_key_exists($this->order['city_id'], $selfCities))
        {
            unset($selfCities[$this->order['city_id']]);

            $this->changeCityList = $selfCities;
        }
    }
    
    private function _calReferCarriageFee()
    {
        $wet = $vol = 0;
        
        foreach($this->orderProducts as $item)
        {
            $wet += $item['num']*$item['sku']['weight']/1000/1000;
            $vol += $item['num']*$item['sku']['length']*$item['width']*$item['height']/100/100/100;
        }
        
        $times = max(ceil($vol/2.6), ceil($wet/3.0), 1);
        
        return array('weight'=>$wet, 'volume'=>$vol, 'times'=>$times);
    }

}

$app = new App('pri');
$app->run();
