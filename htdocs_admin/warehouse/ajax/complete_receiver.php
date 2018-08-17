<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $wid;
    //private $nextStep;

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        //$this->nextStep = Tool_Input::clean('r', 'next_step', TYPE_UINT);
    }

    protected function main()
    {
        $info = array(
            'rece_suid' => $this->_uid,
            'rece_time' => date('Y-m-d H:i:s'),
            'step' => Conf_In_Order::ORDER_STEP_RECEIVED,
        );
        Warehouse_Api::updateOrder($this->_uid, $this->oid, $info);
        
        Warehouse_Security_Stock_Api::updateWaitNumByInorderId($this->oid, $this->wid);
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

