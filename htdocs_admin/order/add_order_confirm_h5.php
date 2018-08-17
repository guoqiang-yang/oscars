<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    protected $title = '确认订单';
    private $oid;
    private $order;
    private $orderProducts;
    private $community;
    private $giftProducts;
    private $discountProducts;
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
        $this->order = Order_Api::getOrderInfo($this->oid);
        $this->order['_op_note'] = Order_Api::parseOpNote($this->order['op_note']);
        $orderProducts = Order_Api::getOrderProducts($this->oid);
        $this->orderProducts = Privilege_Api::getRealBuyProducts($orderProducts['products'], $this->oid, $this->activity_products_amount);
        $this->addFootJs(
            array(
                'js/apps/add_order_h5.js',
            )
        );
        
        $orderActivityProductDao = new Data_Dao('t_order_activity_product');
        $activityProducts = $orderActivityProductDao->getListWhere(array('oid' => $this->oid));
        if (!empty($activityProducts))
        {
            $pids = Tool_Array::getFields($activityProducts, 'pid');
            $productInfos = Shop_Api::getProductInfos($pids, Conf_Activity_Flash_Sale::PALTFORM_WECHAT, true);
            foreach ($activityProducts as $item)
            {
                if ($item['type'] == Conf_Privilege::$TYPE_GIFT)
                {
                    $item['title'] = $productInfos[$item['pid']]['sku']['title'];
                    // 商品图片
                    if ($productInfos[$item['pid']]['sku']['_pic']) {
                        $item['image'] = $productInfos[$item['pid']]['sku']['_pic']['small'];
                    } else {
                        $item['image'] = '/i/nopic100.jpg';
                    }
                    // 单位
                    if ($productInfos[$item['pid']]['sku']['unit']) {
                        $item['unit'] = $productInfos[$item['pid']]['sku']['unit'];
                    } else {
                        $item['unit'] = '个';
                    }
                    $this->giftProducts[] = $item;
                }
                elseif ($item['type'] == Conf_Privilege::$TYPE_SPECIAL_PRICE)
                {
                    $item['title'] = $productInfos[$item['pid']]['sku']['title'];
                    // 商品图片
                    if ($productInfos[$item['pid']]['sku']['_pic']) {
                        $item['image'] = $productInfos[$item['pid']]['sku']['_pic']['small'];
                    } else {
                        $item['image'] = '/i/nopic100.jpg';
                    }
                    // 单位
                    if ($productInfos[$item['pid']]['sku']['unit']) {
                        $item['unit'] = $productInfos[$item['pid']]['sku']['unit'];
                    } else {
                        $item['unit'] = '个';
                    }
                    $this->discountProducts[] = $item;
                }
            }
        }
        //header("Content-type:text/html;charset=utf-8");
        //var_dump('<pre>', $this->discountProducts);exit;
    }

    protected function outputBody()
    {
        $this->smarty->assign('oid', $this->oid);
        $this->smarty->assign('payment_types', Conf_Base::getPaymentTypes());
        $this->smarty->assign('order', $this->order);
        $this->smarty->assign('order_products', $this->orderProducts);
        $this->smarty->assign('giftProducts', $this->giftProducts);
        $this->smarty->assign('discountProducts', $this->discountProducts);
        $this->smarty->display('order/add_order_confirm_h5.html');
    }
}

$app = new App('pri');
$app->run();
