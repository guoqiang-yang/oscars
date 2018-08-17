<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    protected $title = '编辑订单费用';
    private $oid;
    private $order;

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
        // 非自提计算运费
        if ($this->order['delivery_type'] != Conf_Order::DELIVERY_BY_YOURSELF)
        {
            $community = Order_Community_Api::get($this->order['community_id']);
            $this->order['freight'] = Logistics_Api::calFreightByAddress($this->oid, $community['city_id'], $community['district_id'], $community['ring_road'], $this->order['community_id'], $this->order['delivery_type']);
            //var_dump($community);exit;
        // 自提订单运费为0
        } else {
            $this->order['freight'] = 0;
        }
        //var_dump($this->order);exit;
        $this->order['_op_note'] = Order_Api::parseOpNote($this->order['op_note']);
        $this->addFootJs(
            array(
                'js/apps/add_order_h5.js',
            )
        );
    }

    protected function outputBody()
    {
        $this->smarty->assign('payment_types', Conf_Base::getPaymentTypes());
        $this->smarty->assign('oid', $this->oid);
        $this->smarty->assign('order', $this->order);

        $this->smarty->display('order/add_order_fee_h5.html');
    }
}

$app = new App('pri');
$app->run();
