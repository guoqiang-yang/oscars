<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $num = 100;
    private $types = array(
            Conf_Money_In::FINANCE_INCOME,
            Conf_Money_In::FINANCE_ADJUST,
            Conf_Money_In::FINANCE_REFUND,
            Conf_Money_In::CUSTOMER_PRE_PAY,
            Conf_Money_In::AMOUNT_CUSTOMER_PAID,
        );
    private $start;
    private $searchConf;
    private $summaryBills = array();
    private $moneyoutBills = array();
    private $adjustBills = array();
    private $billList = array();
    private $balanceBills = array();
    private $customerPayBackBills = array();
    private $staffList;
    private $remainBalance = array(); //财务结余

    protected function getPara()
    {
        $this->searchConf = array(
            'start_date' => Tool_Input::clean('r', 'start_date', TYPE_STR),
            'end_date' => Tool_Input::clean('r', 'end_date', TYPE_STR),
            'payment_type' => Tool_Input::clean('r', 'payment_type', TYPE_UINT),
            'suid' => Tool_Input::clean('r', 'suid', TYPE_UINT),
            'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
        );

        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);

        if (empty($this->searchConf['start_date']))
        {
            $this->searchConf['start_date'] = date('Y-m-d');
        }
    }

    protected function main()
    {
        $fmi = new Finance_Money_In();

        // 财务收账汇总
        $summaryWhere = $this->_genWhere();
        $summaryGroup = ' group by type, payment_type, wid, suid ';
        $summaryField = array(
            'type',
            'payment_type',
            'wid',
            'suid',
            'sum(price)'
        );
        $this->summaryBills = $fmi->openGet($summaryWhere . $summaryGroup, $summaryField);

        // 财务支出汇总
        $moneyoutWhere = $this->_genWhere('moneyout');
        $moneyoutGroup = ' group by type, payment_type, wid, suid';
        $moneyoutField = array(
            'type',
            'payment_type',
            'wid',
            'suid',
            'sum(price)'
        );

        $fcmo = new Finance_Coopworker_Money_Out();
        $this->moneyoutBills = $fcmo->openGet($moneyoutWhere . $moneyoutGroup, $moneyoutField);

        // 客户账户余额金额汇总
        $crmBalanceWhere = $this->_genWhere('balance');
        $crmBalanceGroup = ' group by type, payment_type, suid';
        $crmBalanceField = array(
            'type',
            'payment_type',
            'suid',
            'sum(price)'
        );

        $fca = new Finance_Customer_Amount();
        $this->balanceBills = $fca->openGet($crmBalanceWhere . $crmBalanceGroup, $crmBalanceField);

        // 查账务列表
        $where = $this->_genWhere();
        $_groupby = ' group by cid, type';
        $this->billList = $fmi->openGet($where . $_groupby, array(
            '*',
            'sum(price) as total_price',
            'count(1) as total_order'
        ), '', $this->start, $this->num);

        $this->staffList = Tool_Array::list2Map(Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_FINANCE_NEW), 'suid');

        //客户补偿
        $customerPayBackWhere = $this->_genWhere('customer_pay_back');
        $customerPayBackGroup = ' group by suid';
        $customerPayBackField = array(
            'type',
            'payment_type',
            'suid',
            'sum(price)'
        );

        $fca = new Finance_Customer_Amount();
        $this->customerPayBackBills = $fca->openGet($customerPayBackWhere . $customerPayBackGroup, $customerPayBackField);

        // 计算结余
        foreach ($this->summaryBills['data'] as $_bill)
        {
            $suid = $_bill['suid'];
            $payType = $_bill['payment_type'];

            if (!isset($this->remainBalance[$suid][$payType]))
            {
                $this->remainBalance[$suid][$payType] = 0;
            }

            if ($_bill['type'] == Conf_Money_In::FINANCE_REFUND)
            {
                $this->remainBalance[$suid][$payType] += 0 - abs($_bill['sum(price)']);
            }
            else if ($_bill['type'] == Conf_Money_In::FINANCE_ADJUST)
            {
                $this->remainBalance[$suid][$payType] += $_bill['sum(price)'];
            }
            else
            {
                $this->remainBalance[$suid][$payType] += abs($_bill['sum(price)']);
            }
        }

        foreach ($this->moneyoutBills['data'] as $_bill)
        {
            $suid = $_bill['suid'];
            $payType = $_bill['payment_type'];

            if (!isset($this->remainBalance[$suid][$payType]))
            {
                $this->remainBalance[$suid][$payType] = 0;
            }

            $this->remainBalance[$suid][$payType] += 0 - abs($_bill['sum(price)']);
        }

        foreach ($this->balanceBills['data'] as $_bill)
        {
            $suid = $_bill['suid'];
            $payType = $_bill['payment_type'];

            if (!isset($this->remainBalance[$suid][$payType]))
            {
                $this->remainBalance[$suid][$payType] = 0;
            }

            if ($_bill['type'] == Conf_Finance::CRM_AMOUNT_TYPE_PREPAY || $_bill['type'] == Conf_Finance::CRM_AMOUNT_TYPE_PREPAY_ORDER)
            {
                $this->remainBalance[$suid][$payType] += abs($_bill['sum(price)']);
            }
            else if ($_bill['type'] == Conf_Finance::CRM_AMOUNT_TYPE_CASH)
            {
                $this->remainBalance[$suid][$payType] += 0 - abs($_bill['sum(price)']);
            }
        }

        if (!empty($this->customerPayBackBills['data']))
        {
            foreach ($this->customerPayBackBills['data'] as $_bill)
            {
                $suid = $_bill['suid'];
                $payType = $_bill['payment_type'];

                if (!isset($this->remainBalance[$suid][$payType]))
                {
                    $this->remainBalance[$suid][$payType] = 0;
                }

                $this->remainBalance[$suid][$payType] += 0 - abs($_bill['sum(price)']);
            }
        }
    }

    protected function outputBody()
    {
        $app = '/finance/bill_list_for_day.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->billList['total'], $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search', $this->searchConf);
        $this->smarty->assign('total', $this->billList['total']);
        $this->smarty->assign('staff_list', $this->staffList);
        $this->smarty->assign('summary_bills', $this->summaryBills['data']);
        $this->smarty->assign('moneyout_bills', $this->moneyoutBills['data']);
        $this->smarty->assign('adjust_bills', $this->adjustBills['data']);
        $this->smarty->assign('bill_list', $this->billList['data']);
        $this->smarty->assign('balance_bills', $this->balanceBills['data']);

        $allPaymentTypes = Conf_Base::getPaymentTypes();
        foreach (Conf_Base::$BANK_DESC as $k => $bname)
        {
            $allPaymentTypes[$k] = $bname;
        }

        $this->smarty->assign('payment_types', $allPaymentTypes);
        $this->smarty->assign('coopworker_fee_types', Conf_Base::getCoopworkerFeeTypes());
        $this->smarty->assign('customer_amount_types', Conf_Finance::$Crm_AMOUNT_TYPE_DESCS);
        $this->smarty->assign('types', $this->types);
        $this->smarty->assign('types_desc', Conf_Money_In::$STATUS_DESC);
        $this->smarty->assign('remain_balance', $this->remainBalance);
        $this->smarty->assign('coopworker_bill_list', $this->_genCoopworkerBillUrl());
        $this->smarty->assign('cities', Conf_City::$CITY);

        $this->smarty->display('finance/bill_list_for_day.html');
    }

    private function _genWhere($whereType = '')
    {
        $where = ' status=0 ';
        switch ($whereType)
        {
            case 'moneyout':
                break;
            case 'balance':
                $where .= ' and type in (' . implode(',', array_keys(Conf_Finance::$Crm_AMOUNT_TYPE_DESCS)) . ')';
                break;
            case 'customer_pay_back':
                $where .= sprintf(' and type=%d AND payment_type=%d', Conf_Finance::CRM_AMOUNT_TYPE_PAYBACK, Conf_Base::PT_PAY_BACK);
                break;
            default:
                $types = !empty($this->searchConf['type']) ? array($this->searchConf['type']) : $this->types;
                $where .= ' and type in (' . implode(',', $types) . ')';
                break;
        }

        if (!empty($this->searchConf['end_date']))
        {
            $where .= ' and ctime>="' . $this->searchConf['start_date'] . ' 00:00:00"' . ' and ctime<="' . $this->searchConf['end_date'] . ' 23:59:59"';
        }
        else
        {
            $where .= ' and ctime>="' . $this->searchConf['start_date'] . ' 00:00:00"' . ' and ctime<="' . $this->searchConf['start_date'] . ' 23:59:59"';
        }

        if (!empty($this->searchConf['payment_type']))
        {
            $where .= ' and payment_type=' . $this->searchConf['payment_type'];
        }
        if (!empty($this->searchConf['suid']))
        {
            $where .= ' and suid=' . $this->searchConf['suid'];
        }
        if (!empty($this->searchConf['city_id']))
        {
            $where .= ' and city_id='. $this->searchConf['city_id'];
        }

        return $where;
    }

    private function _genCoopworkerBillUrl()
    {
        $etime = !empty($this->searchConf['end_date']) ? $this->searchConf['end_date'] : $this->searchConf['start_date'];

        return sprintf('/finance/coopworker_bill_list.php?btime=%s&etime=%s&suid=%d', $this->searchConf['start_date'], $etime, $this->searchConf['suid']);
    }
}

$app = new App();
$app->run();