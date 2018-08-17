<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
    }

    protected function main()
    {
        Order_Api::forwardOrderStep($this->oid, Conf_Order::ORDER_STEP_NEW, $this->_user);

        $order = Order_Api::getOrderInfo($this->oid);
        $param = array('price' => $order['price'] / 100, 'carryFee' => $order['customer_carriage'] / 100, 'freight' => $order['freight'] / 100, 'privilege' => $order['privilege'] / 100, 'delivery_date' => $order['delivery_date']);
        Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_NEW_ORDER, $param);
    }

    protected function outputPage()
    {
        $result = array('oid' => $this->oid);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App('pub');
$app->run();