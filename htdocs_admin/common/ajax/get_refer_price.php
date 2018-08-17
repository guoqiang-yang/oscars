<?php

include_once ('../../../global.php');

Class App extends App_Admin_Ajax
{
    private $id;    //t_coopworker_order 表中的id字段
    private $times;
    private $ret;

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->times = Tool_Input::clean('r', 'times', TYPE_UINT);
        if ($this->times < 1)
        {
            $this->times = 1;
        }
    }

    protected function checkAuth()
    {
        parent::checkAuth('hc_order_edit_coopworker');
    }

    protected function main()
    {
        $lrp = new Logistics_Refer_Price();
        $this->ret = $lrp->setTimes($this->times)->calReferPriceByCoopworkerOrderId($this->id);
    }

    protected function outputBody()
    {
        $result = array('refer_price' => $this->ret['refer_price']);
        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}
$app = new App();
$app->run();