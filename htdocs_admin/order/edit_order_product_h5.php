<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    protected $title = '编辑订单商品';
    private $totalPrice;
    private $oid;
    private $orderProducts;
    private $cid;
    private $cityId;
    // 赠品
    private $giftList = array();
    // 满特价商品
    private $discountList = array();
    private $activity_products_amount = 0;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
    }
    
    protected function checkAuth()
    {
        parent::checkAuth('/order/add_order_logistics_h5');
    }
    
    protected function main()
    {
        $orderInfo = Order_Api::getOrderInfo($this->oid);
        $searchConf = array(
            'city_id' => $orderInfo['city_id'],
        );
        $this->cid = $orderInfo['cid'];
        $this->cityId = $orderInfo['city_id'];
        $orderProducts = Order_Api::getOrderProducts($this->oid);
        $this->orderProducts = Privilege_Api::getRealBuyProducts($orderProducts['products'], $this->oid, $this->activity_products_amount);
        $pid2Num = array();
        foreach ($this->orderProducts as $item)
        {
            if ($item['status'] != Conf_Base::STATUS_NORMAL || $item['rid'] > 0)
            {
                continue;
            }
            $pid2Num[$item['pid']] = $item['num'];
            $this->totalPrice += $item['num'] * $item['price'];
        }
        //var_dump('<pre>',$this->cityId);exit;
        $this->addFootJs(array(
            'js/apps/add_order_h5.js',
        ));
        //header("Content-type:text/html;charset=utf-8");
        //var_dump('<pre>',$orderInfo);exit;
        //$privilege = $this->_getActivityProducts();
        //$this->_setActivityProducts($privilege);
        
        //var_dump('<pre>', $this->giftList);
        //var_dump('<pre>', $this->discountList);exit;
    }
    
    private function _getActivityProducts()
    {
        $pids = array_keys($this->orderProducts);
        if (empty($this->orderProducts) || empty($pids)) return;
        $Products = array();
        $productList = Shop_Api::getProductInfos($pids);
        foreach ($productList as $pid => $item)
        {
            $Products[] = array(
                'sid' => $item['product']['sid'],
                'pid' => $pid,
                'num' => $this->orderProducts[$pid]['num'],
                'price' => $item['product']['sale_price']>0? $item['product']['sale_price']: $item['product']['price']  //@todo 活动价...
            );
            $otherInfo = array(
                'city_id' => $this->cityId,
                'ctime' => date('Y-m-d H:i:s'),
                'source' => Conf_Order::SOURCE_KEFU,
                'paid' => Conf_Order::UN_PAID,
                'freight' => 0,
            );
        }
        $privilege = Privilege_2_Api::computePromotionPrivilege($this->cid, $Products, $otherInfo, false, $this->activityProducts);
        return $privilege;
    }
    
    private function _setActivityProducts($privilege)
    {
        if(!empty($privilege['show_gift_products']))
        {
            foreach ($privilege['show_gift_products'] as $item)
            {
                $this->giftList[] = array(
                    'pid' => $item['pid'],
                    'sid' => $item['sid'],
                    'title' => $item['title'],
                    'sale_price' => $item['price']/100,
                    'unit' => $item['unit'],
                    'num' => $item['num'],
                    'image' => $item['image'],
                    'need_cut' =>false,
                    'need_cut_new' => 0,
                    'need_cut_desc' => '',
                );
            }
        }
        if(!empty($privilege['show_special_price_products']))
        {
            foreach ($privilege['show_special_price_products'] as $item)
            {
                $this->discountList[] = array(
                    'pid' => $item['pid'],
                    'sid' => $item['sid'],
                    'title' => $item['title'],
                    'sale_price' => $item['price']/100,
                    'unit' => $item['unit'],
                    'max_num' => $item['num'],
                    'image' => $item['image'],
                    'need_cut' =>false,
                    'need_cut_new' => 0,
                    'need_cut_desc' => '',
                );
            }
        }
        
    }
    
    protected function outputBody()
    {
        $this->smarty->assign('oid', $this->oid);
        $this->smarty->assign('total_price', $this->totalPrice);
        $this->smarty->assign('orderProducts', $this->orderProducts);
        //$this->smarty->assign('giftList', $this->giftList);
        //$this->smarty->assign('discountList', $this->discountList);
        $this->smarty->display('order/edit_order_product_h5.html');
    }
}

$app = new App('pri');
$app->run();
