<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $orderInfo;
    private $showOids;
    private $type;
    private $customer;

    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->showOids = Tool_Input::clean('r', 'show_oids', TYPE_STR);
        $this->type = Tool_Input::clean('r', 'type', TYPE_STR);
    }

    protected function checkPara()
    {
        if (empty($this->oid))
        {
            throw new Exception('订单号格式不正确！');
        }
        if ($this->type == 'order_show')
        {
            $oids = array_unique(explode(',', $this->showOids));
            if (count($oids) > 0 && in_array($this->oid, $oids))
            {
                throw new Exception('该订单已经在页面中显示！');
            }

        }
    }

    protected function checkAuth($permission = '')
    {
        parent::checkAuth('/order/auto_receipt');
    }

    protected function main()
    {
        $this->orderInfo = Order_Api::getOrderInfo($this->oid);
        if ($this->type == 'payment_modal_show' && $this->orderInfo)
        {
            $this->customer = Crm2_Api::getCustomerInfo($this->orderInfo['cid'], FALSE, FALSE);
        }
    }

    protected function outputPage()
    {
        if ($this->orderInfo)
        {
            $this->smarty->assign('order', $this->orderInfo);
            if ($this->type == 'order_show')
            {
                $this->smarty->assign('step_list', Conf_Order::$ORDER_STEPS);
                $this->smarty->assign('warehouse_list', Conf_Warehouse::$WAREHOUSES);
                $html = $this->smarty->fetch('order/block_receipt_order_info.html');
            }
            elseif ($this->type == 'payment_modal_show')
            {
                $this->smarty->assign('step_list', Conf_Order::$ORDER_STEPS);
                $this->smarty->assign('customer', $this->customer['customer']);
                $this->smarty->assign('payment_types_finance', Conf_Base::getPaymentTypes());
                $html = $this->smarty->fetch('finance/block_money_in.html');
            }
        }

        $result = array('html' => $html);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App('pri');
$app->run();

