<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $freight;
    private $service;
    private $floorNum;
    private $customerCarriage;
    private $note;
    private $isPrintPrice;
    private $paymentType;

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->freight = Tool_Input::clean('r', 'freight', TYPE_NUM);
        $this->floorNum = Tool_Input::clean('r', 'floor_num', TYPE_UINT);
        $this->service = Tool_Input::clean('r', 'service', TYPE_UINT);
        $this->customerCarriage = Tool_Input::clean('r', 'customer_carriage', TYPE_NUM);
        $this->note = Tool_Input::clean('r', 'note', TYPE_STR);
        $this->isPrintPrice = Tool_Input::clean('r', 'is_print_price', TYPE_UINT);
        $this->paymentType = Tool_Input::clean('r', 'payment_type', TYPE_INT);
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
            'freight' => $this->freight * 100,
            'service' => $this->service,
            'floor_num' => $this->floorNum,
            'customer_carriage' => $this->customerCarriage * 100,
            'note' => $this->note,
            'payment_type' => $this->paymentType
         );

        $opNote = array(
            'nopprice' => $this->isPrintPrice,
        );
        $update['op_note'] = Order_Api::generateOpNote($opNote);

        //占用优惠券
        $order = Order_Api::getOrderInfo($this->oid);
        $activityProducts = Privilege_Api::getActivityProducts($this->oid);
        $orderProducts = Order_Api::getOrderProducts($this->oid);
        if ($order['step'] < Conf_Order::ORDER_STEP_SURE)
        {
            //优惠
            $info = array_merge($update, $order);
            $info['freight'] = $this->freight * 100;
            //$orderProducts = Order_Api::getOrderProducts($this->oid);
            $privilegeInfo = Privilege_2_Api::savePromotionPrivilege($info['cid'], $orderProducts['products'], $info, $activityProducts);
            $update['privilege_note'] = $privilegeInfo['privilege_note'];
            $update['privilege'] = $privilegeInfo['total_privilege'];
        }

        Order_Api::updateOrderInfo($this->oid, $update);
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