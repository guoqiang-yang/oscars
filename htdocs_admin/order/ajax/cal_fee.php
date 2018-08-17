<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $service;
    private $floorNum;
    private $freight;
    private $oid;
    private $city;
    private $district;
    private $area;
    private $carryFee;
    private $cmid;
    private $deliveryType;

    protected function getPara()
    {
        $this->service = Tool_Input::clean('r', 'service', TYPE_UINT);
        $this->floorNum = Tool_Input::clean('r', 'floor_num', TYPE_UINT);
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->city = Tool_Input::clean('r', 'city', TYPE_UINT);
        $this->district = Tool_Input::clean('r', 'district', TYPE_UINT);
        $this->area = Tool_Input::clean('r', 'area', TYPE_UINT);
        $this->cmid = Tool_Input::clean('r', 'cmid', TYPE_UINT);
        $this->deliveryType = Tool_Input::clean('r', 'delivery_type', TYPE_UINT);
    }

    protected function main()
    {
        $orderInfo = Order_Api::getOrderInfo($this->oid);
        if ($orderInfo['wid'] == Conf_Warehouse::WID_101 || $orderInfo['delivery_type'] == Conf_Order::DELIVERY_BY_YOURSELF)
        {
            //$this->carryFee = 0;
            $this->freight = 0;
        }
        else
        {
            //$this->carryFee = Logistics_Api::calCarryFee($this->oid, $this->service, $this->floorNum);
            $this->freight = Logistics_Api::calFreightByAddress($this->oid, $this->city, $this->district, $this->area, $this->cmid, $this->deliveryType);
        }

        $client = new Yar_Client(MS . "/cmpt/order/fees");
        $result = $client->AdminCarryFee($this->oid, $this->floorNum, $this->service);
        $this->carryFee = 0;
        if ( isset($result['user']) ) {
            $this->carryFee = $result['user'];
        }
    }

    protected function outputPage()
    {
        $result = array('carry_fee' => $this->carryFee / 100, 'freight' => $this->freight / 100);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App('pub');
$app->run();

