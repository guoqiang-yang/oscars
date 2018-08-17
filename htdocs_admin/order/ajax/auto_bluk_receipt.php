<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oids;

    protected function getPara()
    {
        $this->oids = Tool_Input::clean('r', 'oids', TYPE_ARRAY);
    }

    protected function checkPara()
    {
        if (isset($this->oids) && empty($this->oids))
        {
            throw new Exception('请勾选订单！');
        }
    }

    protected function main()
    {
        $orderList = Order_Api::getListByPk($this->oids, array('*'));
        foreach ($this->oids as $oid)
        {
            $oldOrder = $orderList[$oid];

            $order['step'] = Conf_Order::ORDER_STEP_FINISHED;
            Order_Api::forwardOrderStep($oid, $order['step'], $this->_user);
            if ($oldOrder['step'] != $order['step'])
            {
                //订单操作日志
                //更新为：{newStep}
                $param = array(
                    'newStep' => Conf_Order::$ORDER_STEPS[$order['step']],
                );
                Admin_Api::addOrderActionLog($this->_uid, $oid, Conf_Order_Action_Log::ACTION_CHANGE_STEP, $param);
            }
        }
    }

    protected function outputPage()
    {
        $result = array('oids' => $this->oids);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}
$app = new App('pri');
$app->run();