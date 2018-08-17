<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $cid;
    private $etime;
    private $discount;
    private $customerInfo;
    private $orderList = array();
    private $allPrice = array();
    private $showMsg = '';

    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->etime = Tool_Input::clean('r', 'etime', TYPE_STR);
        $this->discount = Tool_Input::clean('r', 'discount', TYPE_UINT);

        if (empty($this->etime))
        {
            $this->etime = date('Y-m-d');
        }
    }

    protected function main()
    {
//        $this->showMsg = '功能暂且下线，如有疑问请联系产品技术部！！';
//
//        return;
        
        if (empty($this->cid))
        {
            $this->showMsg = '请输入客户ID！';

            return;
        }

        $ret = Finance_Api::getCustomerUnpaidOrderList($this->cid, $this->etime, $this->discount);

        $this->customerInfo = $ret['customer'];
        $this->orderList = $ret['order'];
        $this->showMsg = $ret['msg'];
        $this->allPrice = $ret['prices'];

        $this->addFootJs(array('js/apps/finance.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {

        $this->smarty->assign('cid', $this->cid);
        $this->smarty->assign('etime', $this->etime);
        $this->smarty->assign('discount', $this->discount);
        $this->smarty->assign('order_list', $this->orderList);
        $this->smarty->assign('customer', $this->customerInfo);
        $this->smarty->assign('all_prices', $this->allPrice);
        $this->smarty->assign('show_msg', $this->showMsg);
        $this->smarty->assign('payment_types', Conf_Base::$PAYMENT_TYPES);

        $this->smarty->display('finance/customer_account_pay.html');
    }
}

$app = new App();
$app->run();