<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $num = 20;
    private $start;
    private $orderby;
    private $sort;
    private $search;
    private $list;
    private $sum = 0;
    private $sumPaymentDays = 0;
    private $sumNoPaymentDays = 0;
    private $backAmount = 0;
    private $noBackAmount = 0;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->orderby = Tool_Input::clean('r', 'order', TYPE_STR);
        $this->sort = Tool_Input::clean('r', 'sort', TYPE_STR);

        $this->search = array(
            'mobile' => Tool_Input::clean('r', 'mobile', TYPE_UINT),
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'sales_suid' => Tool_Input::clean('r', 'sales_suid', TYPE_UINT),
            'bid' => Tool_Input::clean('r', 'bid', TYPE_UINT),
            'status' => 0,
            'has_payment_days' => Tool_Input::clean('r', 'has_payment_days', TYPE_UINT),
        );
    }

    protected function main()
    {
        $orderby = !empty($this->orderby) ? $this->orderby : 'account_balance';
        $sort = !empty($this->sort) ? $this->sort : 'desc';
        $this->list = Crm2_Api::getCustomerListForAdmin($this->search, $this->_user, $orderby, $this->start, $this->num, $sort);

        //$this->sum = Crm2_Api::getAllDebtOfCustomer($this->search, $this->_user);

        // 临时下线 by yangguoqiang 2016-10-28
//        $this->show = FALSE;
//        if (empty($this->search['mobile']) && empty($this->search['name']) && empty($this->search['cid']) && empty($this->search['sales_suid']))
//        {
//            $this->sumPaymentDays = Crm2_Api::getPaymentDaysDebt($this->search, $this->_user);
//            $this->sumNoPaymentDays = Crm2_Api::getNoPaymentDaysDebt($this->search, $this->_user);
//
//            $orderAmount = Crm2_Api::getBalanceAmount($this->search, $this->_user);
//            $this->backAmount = $orderAmount['back_amount'] / 100;
//            $this->noBackAmount = $orderAmount['not_back_amount'] / 100;
//
//            $this->show = TRUE;
//        }
        $this->addFootJs(array(
            'js/apps/send_vip_coupon.js'
        ));
    }

    protected function outputBody()
    {
        $app = '/finance/customer_list.php?' . http_build_query($this->search) . '&order=' . $this->orderby . '&sort=' . $this->sort;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->list['total'], $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search', $this->search);
        $this->smarty->assign('customer_list', $this->list['data']);
        $this->smarty->assign('total', $this->list['total']);
        $this->smarty->assign('sum', $this->sum);
        $this->smarty->assign('salesman_list', Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW, 0));
        $this->smarty->assign('sum_payment_days', $this->sumPaymentDays);
        $this->smarty->assign('sum_no_payment_days', $this->sumNoPaymentDays);
        $this->smarty->assign('back_amount', $this->backAmount);
        $this->smarty->assign('not_back_amount', $this->noBackAmount);
        $this->smarty->assign('show', FALSE);
        $this->smarty->assign('vip_coupon_list', Conf_Coupon::getVipCouponList());

        $this->smarty->display('finance/customer_list.html');
    }
}

$app = new App('pri');
$app->run();
