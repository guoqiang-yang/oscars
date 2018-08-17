<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
    private $price;
    private $paymentType;
    private $type;

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->price = Tool_Input::clean('r', 'price', TYPE_NUM);
        $this->paymentType = Tool_Input::clean('r', 'payment_type', TYPE_UINT);
        $this->type = Tool_Input::clean('r', 'type', TYPE_UINT);
    }

    protected function checkPara()
    {
        if ($this->id == 0 || $this->paymentType == 0)
        {
            throw new Exception('finance: params error!');
        }
    }

    protected function main()
    {
        Finance_Api::modifyCustomerSinglePaidRecord($this->id, $this->price * 100, $this->paymentType, $this->type);
    }

    protected function outputBody()
    {
        $result = array('st' => 1);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App();
$app->run();