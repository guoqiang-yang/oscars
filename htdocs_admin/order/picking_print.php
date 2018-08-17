<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $oid;
    private $areas;
    private $order;
    private $products;
    private $validAreas;
    private $customer;
    protected $headTmpl = 'head/head_none.html';
    protected $tailTmpl = 'tail/tail_none.html';
    private $hasWeixingProduct;
    private $isFirstOrder;
    private $isPaid;
    private $isVip;
    private $isYache = false;

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->areas = Tool_Input::clean('r', 'areas', TYPE_STR);
        $this->areas = array_filter(explode(",", $this->areas));
    }

    protected function main()
    {
        $this->order = Order_Api::getOrderInfo($this->oid);
        $this->products = Order_Picking_Api::getOrderProducts4Picking($this->oid);
        $this->validAreas = array_keys($this->products);

        $this->hasWeixingProduct = Order_Api::hasWeixingProduct($this->orderProducts);

        if ($this->order['delivery_date'] > date('Y-m-d', strtotime('tomorrow')))
        {
            $this->isYache = true;
        }

        //过滤不打印的分区
        foreach ($this->products as $area => &$products)
        {
            /*
            if (!preg_match('/[a-zA-Z]/',$area))    //过滤临采区
            {
                unset($this->products[$area]);
            }
            */
            if (!empty($this->areas) && !in_array($area, $this->areas))
            {
                unset($this->products[$area]);
            }
            
            usort($products, array('self', '_sort'));
        }

        //地址
        if (!empty($this->order['community_id']))
        {
            $this->communityInfo = Order_Community_Api::get($this->order['community_id']);
            $this->order['print_address'] = $this->communityInfo['district'] . ' ' . $this->communityInfo['name'] . ' ' . $this->order['_address'] . '（' . $this->communityInfo['address'] . '）';
        }
        else
        {
            $this->order['print_address'] = $this->order['_district'] . ' ' . $this->order['address'];
        }

        //客户信息
        if ($this->order['uid'])
        {
            $this->customer = Crm2_Api::getCustomerInfoByCidUid($this->order['cid'], $this->order['uid']);
        }
        else
        {
            $this->customer = Crm2_Api::getCustomerInfo($this->order['cid'], FALSE, FALSE);
            $this->customer = $this->customer['customer'];
        }

        if ($this->customer['sales_suid'])
        {
            $this->saler = Admin_Api::getStaff($this->customer['sales_suid']);
        }

        // 获取订单的第三方工人（司机，搬运工）
        $coopworders = Logistics_Coopworker_Api::getOrderOfWorkers($this->oid, 0, TRUE);

        $this->order['driver_list'] = array();
        $this->order['carrier_list'] = array();
        foreach ($coopworders as $oner)
        {
            if ($oner['type'] == Conf_Base::COOPWORKER_DRIVER)
            {
                $this->order['driver_list'][] = array(
                    'cuid' => $oner['cuid'],
                    'name' => $oner['info']['name'],
                    'phone' => $oner['info']['mobile'],
                    'price' => $oner['price'] / 100,
                    'paid' => $oner['paid'],
                    'user_type' => $oner['user_type'],
                );
            }
            else if ($oner['type'] == Conf_Base::COOPWORKER_CARRIER)
            {
                $this->order['carrier_list'][] = array(
                    'cuid' => $oner['cuid'],
                    'name' => $oner['info']['name'],
                    'phone' => $oner['info']['mobile'],
                    'price' => $oner['price'] / 100,
                    'paid' => $oner['paid'],
                    'user_type' => $oner['user_type'],
                );
            }
        }

        $this->isFirstOrder = Order_Api::isFristOrder($this->oid, $this->order);
        $this->isPaid = $this->order['paid'] == Conf_Order::HAD_PAID ? 1 : 0;
        $this->isVip = $this->customer['level_for_sys'] == Conf_User::CRM_SYS_LEVEL_VIP ? 1 : 0;

        $this->addFootJs(array('js/apps/order_print.js'));
    }

    protected function outputBody()
    {

        $this->smarty->assign('order', $this->order);
        $this->smarty->assign('products', $this->products);
        $this->smarty->assign('valid_areas', $this->validAreas);
        $this->smarty->assign('customer', $this->customer);
        $this->smarty->assign('saler', $this->saler);

        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);
        $this->smarty->assign('delivery_types', Conf_Order::$DELIVERY_TYPES);
        $this->smarty->assign('has_weixing_product', $this->hasWeixingProduct);

        $this->smarty->assign('is_first_order', $this->isFirstOrder);
        $this->smarty->assign('is_paid', $this->isPaid);
        $this->smarty->assign('is_vip', $this->isVip);
        $this->smarty->assign('is_ya_che', $this->isYache);

        $this->smarty->display('order/picking_print.html');
    }
    
    private function _sort($a, $b)
    {
        if ($a['location'] == $b['location'])
        {
            return 0;
        }
        
        return ($a['location'] > $b['location']) ? 1 : -1;
    }
}

$app = new App();
$app->run();