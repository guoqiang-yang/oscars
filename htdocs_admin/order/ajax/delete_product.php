<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $pid;

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
        if (empty($pid))
        {
            $this->pid = Tool_Input::clean('r', 'pid_list', TYPE_ARRAY);
        }
        else
        {
            $this->pid = array($pid);
        }
    }

    protected function checkPara()
    {
        if (empty($this->pid))
        {
            throw new Exception('请选择要删除的商品！');
        }
        $msg = Order_Api::canEditOrderInfo($this->oid);
        if($msg['error'] > 0)
        {
            throw new Exception($msg['errormsg']);
        }
    }

    protected function checkAuth()
    {
        parent::checkAuth('/order/edit_order');
    }

    protected function main()
    {
        $order = Order_Api::getOrderInfo($this->oid);
        if ($order['status'] >= Conf_Order::ORDER_STEP_SURE)
        {
            throw new Exception('common:permission denied');
        }

        // 判断是否临采已采购
        $productsOfOrder = Order_Api::getOrderProduct($this->oid);
        foreach ($this->pid as $_pid)
        {
            if (array_key_exists($_pid, $productsOfOrder['order'])
                && $productsOfOrder['order'][$_pid]['tmp_inorder_num']!=0)
            {
                throw new Exception('临采商品已经采购，不能再修改数量！增加数量走补单，减少数量请通知库房退货！！');
            }
        }

        $price = Order_Api::deleteProduct($this->oid, $this->pid);
        Order_Api::updateOrderModify($this->oid, '');

        if ($order['step'] > Conf_Order::ORDER_STEP_EMPTY)
        {
            $param = array('总价' => $price / 100);
            Admin_Api::addOrderActionLog($this->_uid, $this->oid, Conf_Order_Action_Log::ACTION_CHANGE_PRODUCTS, $param);
        }
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

