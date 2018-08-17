<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $productStr;
    private $products = array();

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->oid))
        {
            throw new Exception('order:empty order id');
        }
    }

    protected function checkAuth()
    {
        parent::checkAuth('/order/edit_order');
    }

    protected function main()
    {
        $update = array(
            'wid' => 0,
        );
        Order_Api::updateOrderInfo($this->oid, $update);

        $param = array('newStep' => '非本地订单');
        Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_CHANGE_STEP, $param);
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

$app = new App('pri');
$app->run();