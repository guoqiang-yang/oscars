<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    protected $title = '添加订单商品';
    private $productList;
    private $start = 0;
    private $num = 0;
    private $total;
    private $hasMore = false;
    private $oid;
    private $totalPrice;
    private $keyword;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->keyword = Tool_Input::clean('r', 'keyword', TYPE_STR);
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

        $statusTag = Conf_Product::PRODUCT_STATUS_ONLINE;
        if (!empty($this->keyword))
        {
            $res = Shop_Api::searchProduct($this->keyword, $this->start, $this->num, $statusTag, 0, $orderInfo['city_id']);
        }
        $this->productList = $res['list'];
        $this->total = $res['total'];
        $this->hasMore = $res['has_more'];
        
        $orderProducts = Order_Api::getOrderProducts($this->oid);
        $pid2Num = array();
        foreach ($orderProducts['products'] as $item)
        {
            if ($item['status'] != Conf_Base::STATUS_NORMAL || $item['rid'] > 0)
            {
                continue;
            }
            $pid2Num[$item['pid']] = $item['num'];
            $this->totalPrice += $item['num'] * $item['price'];
        }

        foreach ($this->productList as &$item)
        {
            $item['num'] = $pid2Num[$item['product']['pid']];
        }

        $this->addFootJs(array(
            'js/apps/add_order_h5.js',
                         ));
    }

    protected function outputBody()
    {
        $this->smarty->assign('product_list', $this->productList);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('has_more', $this->hasMore);
        $this->smarty->assign('oid', $this->oid);
        $this->smarty->assign('total_price', $this->totalPrice);
        $this->smarty->assign('keyword', $this->keyword);

        $this->smarty->display('order/add_order_product_h5.html');
    }
}

$app = new App('pri');
$app->run();
