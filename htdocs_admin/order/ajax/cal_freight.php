<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $city;
    private $district;
    private $area;
    private $fee;
    private $oid;
    private $cmid;
    private $deliveryType;

    protected function getPara()
    {
        $this->city = Tool_Input::clean('r', 'city', TYPE_UINT);
        $this->district = Tool_Input::clean('r', 'district', TYPE_UINT);
        $this->area = Tool_Input::clean('r', 'area', TYPE_UINT);
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->cmid = Tool_Input::clean('r', 'cmid', TYPE_UINT);
        $this->deliveryType = Tool_Input::clean('r', 'delivery_type', TYPE_UINT);
    }

    protected function checkAuth()
    {
        parent::checkAuth('/order/edit_order');
    }

    protected function main()
    {
        $orderInfo = Order_Api::getOrderInfo($this->oid);
        if ($orderInfo['wid'] == Conf_Warehouse::WID_101 || $orderInfo['delivery_type'] == Conf_Order::DELIVERY_BY_YOURSELF)
        {
            $this->fee = 0;
        }
        else
        {
            $this->fee = Logistics_Api::calFreightByAddress($this->oid, $this->city, $this->district, $this->area, $this->cmid, $this->deliveryType);
        }
    }

    protected function outputPage()
    {
        $result = array('freight' => $this->fee);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App('pri');
$app->run();

