<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    protected $title = '选择地址及库房';
    private $cid;
    private $uid;
    private $user;
    private $deliverTime;
    private $deliveryDate;
    private $warehouses;
    private $deliveryTypes;
    private $deliveryDateSoon;
    private $oid;
    private $order;
    private $cityId;
    
    // 选择地址后返回的地址信息
    private $community_id;
    private $full_address;
    private $contact_name;
    private $contact_phone;
    private $delivery_type;
    private $address_id;

    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_INT);
        $this->uid = Tool_Input::clean('r', 'uid', TYPE_INT);
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_INT);
        $this->community_id = Tool_Input::clean('r', 'community_id', TYPE_INT);
        $this->full_address = Tool_Input::clean('r', 'full_address', TYPE_STR);
        $this->contact_name = Tool_Input::clean('r', 'contact_name', TYPE_STR);
        $this->contact_phone = Tool_Input::clean('r', 'contact_phone', TYPE_STR);
        $this->delivery_type = Tool_Input::clean('r', 'delivery_type', TYPE_INT);
        $this->address_id = Tool_Input::clean('r', 'address_id', TYPE_INT);
    }

    protected function checkPara()
    {
        $cityInfo = City_Api::getCity();
        if (!empty($this->oid))
        {
            $this->order = Order_Api::getOrderInfo($this->oid);
            $this->cid = $this->order['cid'];
            $this->uid = $this->order['uid'];
            $this->cityId = $this->order['city_id'];

            if ($this->cityId != $cityInfo['city_id'])
            {
                throw new Exception('订单城市与当前城市不符，请重新选择城市！');
            }
        }
        else
        {
            $this->cityId = $cityInfo['city_id'];
        }
        
    }

    protected function main()
    {
        $this->user = Crm2_Api::getUserInfo($this->uid, true, false);
        // 配送时间
        //$this->deliverTime = Order_Api::getDeliveryTime4Admin();
        $this->deliverTime = Conf_Order::$DELIVERY_TIME_NEW;
        //配送日期
        $today = strtotime('today');
        for ($i = 0; $i <= 4; $i++)
        {
            $date = date('Y-m-d', $today + $i * 86400);
            $this->deliveryDate[] = $date;
        }
        $this->warehouses = Appconf_Warehouse::wid4CreateOrder($this->cityId);//Conf_Warehouse::getWarehousesOfCity($this->cityId);
        $this->deliveryTypes = Conf_Order::$DELIVERY_TYPES;

        $this->deliveryDateSoon = date('Y-m-d');
        $hour = date('G');
        if ($hour > 18)
        {
            $this->deliveryDateSoon = date('Y-m-d', time() + 86400);
        }

        if (!empty($this->oid))
        {
            $this->deliveryDateSoon = $this->order['_delivery_date_base'];
        }

        $this->addFootJs(
            array(
                'js/core/autosuggest.js',
                'js/apps/add_order_h5.js',
            )
        );
        
    }
    
    /**
     * 获取可选送货时间
     */
    protected function getDeliveryTime()
    {
        // 系统当前时间
        $h = intval(date('H', time()));
        // 处理下单时间大于21点及小于8点的订单时间
        if ($h > 21 || $h < 8){
            $h = 8;
        }
        // 送货区间段
        $deliveryTime = Conf_Order::$DELIVERY_TIME_NEW;
        $result = array();
        foreach ($deliveryTime as $k => $v){
            if ($k < ($h-2)){
                break;
            }
            $result[$k] = $v;
        }
        return $result;
    }

    protected function outputBody()
    {
        $this->smarty->assign('user', $this->user);
        $this->smarty->assign('delivery_date', $this->deliveryDate);
        $this->smarty->assign('delivery_time', $this->deliverTime);
        $this->smarty->assign('warehouses', $this->warehouses);
        $this->smarty->assign('delivery_types', $this->deliveryTypes);
        $this->smarty->assign('delivery_date_soon', $this->deliveryDateSoon);
        $this->smarty->assign('city_id', $this->cityId);
        $this->smarty->assign('order', $this->order);
        $this->smarty->assign('oid', $this->oid);
        $this->smarty->assign('cid', $this->cid);
        $this->smarty->assign('uid', $this->uid);
        $this->smarty->assign('community_id', $this->community_id);
        $this->smarty->assign('full_address', $this->full_address);
        $this->smarty->assign('contact_name', $this->contact_name);
        $this->smarty->assign('contact_phone', $this->contact_phone);
        $this->smarty->assign('delivery_type', $this->delivery_type);
        $this->smarty->assign('address_id', $this->address_id);
        $this->smarty->display('order/add_order_logistics_h5.html');
    }
}

$app = new App('pri');
$app->run();
