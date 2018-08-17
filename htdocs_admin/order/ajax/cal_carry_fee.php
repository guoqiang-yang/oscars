<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $service;
    private $floorNum;
    private $fee;
    private $oid;

    protected function getPara()
    {
        $this->service = Tool_Input::clean('r', 'service', TYPE_UINT);
        $this->floorNum = Tool_Input::clean('r', 'floor_num', TYPE_UINT);
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
    }

    protected function checkAuth()
    {
        parent::checkAuth('/order/edit_order');
    }

    protected function main()
    {
        $client = new Yar_Client(MS . "/cmpt/order/fees");
        $result = $client->AdminCarryFee($this->oid, $this->floorNum, $this->service);
        $this->fee = 0;
        if ( isset($result['user']) ) {
            $this->fee = $result['user'];
        }
        // $orderInfo = Order_Api::getOrderInfo($this->oid);
        // if ($orderInfo['wid'] == Conf_Warehouse::WID_101 || $orderInfo['delivery_type'] == Conf_Order::DELIVERY_BY_YOURSELF)
        // {
        //     $this->fee = 0;
        // }
        // else
        // {
        //     $this->fee = Logistics_Api::calCarryFee($this->oid, $this->service, $this->floorNum);
        // }
    }

    protected function outputPage()
    {
        $result = array('carry_fee' => $this->fee);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App('pri');
$app->run();

