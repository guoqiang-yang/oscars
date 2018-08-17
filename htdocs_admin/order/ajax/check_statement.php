<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
    private $totalPrice;

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->totalPrice = Tool_Input::clean('r', 'total_price', TYPE_UINT);
    }

    protected function main()
    {
        Logistics_Coopworker_Api::checkStatement($this->id, $this->_uid, $this->totalPrice);
    }

    protected function outputPage()
    {
        $result = array('id' => $this->id);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App('pri');
$app->run();

