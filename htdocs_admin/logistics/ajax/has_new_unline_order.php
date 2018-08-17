<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $num;
    private $search;
    private $query;

    protected function getPara()
    {
        $this->query = array(
            'wid' => $this->getWarehouseId(),
            'max_oid' => Tool_Input::clean('r', 'max_oid', TYPE_UINT),
            'delivery_data' => Tool_Input::clean('r', 'delivery_data', TYPE_STR),
            'delivery_btime' => max(Tool_Input::clean('r', 'delivery_btime', TYPE_UINT), 8),
            'delivery_etime' => min(Tool_Input::clean('r', 'delivery_etime', TYPE_UINT), 21),
        );

        $this->query['delivery_data'] = !empty($this->query['delivery_data'])?$this->query['delivery_data']: date('Y-m-d');

        $this->query['delivery_etime'] = !empty($this->query['delivery_etime'])? $this->query['delivery_etime']: 21;

        $this->query['delivery_etime'] = $this->query['delivery_etime']<=$this->query['delivery_btime']?
            $this->query['delivery_btime']: $this->query['delivery_etime'];

        $deliveryBtime = $this->query['delivery_btime']<10? '0'.$this->query['delivery_btime']: $this->query['delivery_btime'];

        $this->search = array(
            'wid' => $this->query['wid'],
            'max_oid' => $this->query['max_oid'],
            'delivery_btime' => $this->query['delivery_data']. ' '.$deliveryBtime.':00:00',
            'delivery_etime' => $this->query['delivery_data']. ' '.$this->query['delivery_etime'].':00:00',
        );
    }

    protected function checkPara()
    {
    }

    protected function main()
    {
        $this->num = Logistics_Order_Api::getUnlineOrderNum($this->search);
    }

    protected function outputPage()
    {
        $result = array('num' => $this->num);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pub');
$app->run();