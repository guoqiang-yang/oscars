<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $num = 100;
    private $types = array(
            Conf_Money_Out::FINANCE_PAID,
            Conf_Money_Out::FINANCE_ADJUST,
            Conf_Money_Out::FINANCE_PRE_PAY,
        );
    private $start;
    private $searchConf;
    private $summaryBills = array();
    private $billList = array();
    private $staffList;
    private $remainBalance = array(); //结余
    private $prepayBills = array(); //供应商预付
    private $prepayBillDetails = array(); //供应商预付
    private $bankPaySum;

    protected function getPara()
    {
        $this->searchConf = array(
            'start_date' => Tool_Input::clean('r', 'start_date', TYPE_STR),
            'end_date' => Tool_Input::clean('r', 'end_date', TYPE_STR),
            'paid_source' => Tool_Input::clean('r', 'paid_source', TYPE_UINT),
            'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
            'suid' => Tool_Input::clean('r', 'suid', TYPE_UINT),
        );

        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);

        if (empty($this->searchConf['start_date']))
        {
            $this->searchConf['start_date'] = date('Y-m-d');
        }
        if (empty($this->searchConf['end_date']))
        {
            $this->searchConf['end_date'] = date('Y-m-d');
        }
    }

    protected function main()
    {
        $fmo = new Finance_Money_Out();
        $fsa = new Finance_Supplier_Amount();

        // 财务收账汇总
        $summaryWhere = $this->_genWhere();
        $summaryGroup = ' group by type, paid_source, wid, suid ';
        $summaryField = array(
            'type',
            'paid_source',
            'wid',
            'suid',
            'sum(price)'
        );
        $this->summaryBills = $fmo->openGet($summaryWhere . $summaryGroup, $summaryField);

        if ($this->searchConf['type'] == Conf_Money_Out::FINANCE_PRE_PAY || $this->searchConf['type'] == Conf_Base::STATUS_NORMAL)
        {
            $this->prepayBills = $fsa->getPerDayAmountList($this->searchConf);
            $sids = Tool_Array::getFields($this->prepayBills, 'sid');
            if (!empty($sids))
            {
                $ws = new Warehouse_Supplier();
                $supplierList = $ws->getBulk($sids);
                foreach ($this->prepayBills as &$item)
                {
                    $item['name'] = $supplierList[$item['sid']]['name'];
                    $item['type'] = Conf_Money_Out::FINANCE_PRE_PAY;
                    $item['price'] = 0 - $item['price'];
                }
            }

            $this->prepayBillDetails = $fsa->getBillDetail($this->searchConf, array('*', 'sum(price) as total_price', 'count(1) as total'));
            if (!empty($this->prepayBillDetails)) {
                foreach ($this->prepayBillDetails as &$item) {
                    $item['type'] = Conf_Money_Out::FINANCE_PRE_PAY;
                }
            }
        }

        // 结余
        foreach ($this->summaryBills as $_bill)
        {
            $_suid = $_bill['suid'];
            $_paySource = $_bill['paid_source'];
            $this->remainBalance[$_suid][$_paySource] = isset($this->remainBalance[$_suid][$_paySource]) ? $this->remainBalance[$_suid][$_paySource] + $_bill['sum(price)'] : $_bill['sum(price)'];
        }
        
        foreach ($this->prepayBills as $prepay)
        {
            $_suid = $prepay['suid'];
            $_paySource = $prepay['payment_type'];
            $this->remainBalance[$_suid][$_paySource] = isset($this->remainBalance[$_suid][$_paySource]) ? $this->remainBalance[$_suid][$_paySource] + $prepay['price'] : $prepay['price'];
        }
        $this->bankPaySum = array();
        foreach ($this->remainBalance as $suid => $info)
        {
            foreach ($info as $paymentType => $money)
            {
                $this->bankPaySum[$paymentType] += $money;
            }
        }        
        // 查账务列表
        $where = $this->_genWhere();
        $group = ' group by sid, type';
        $this->billList = $fmo->openGet($where . $group, array(
            '*',
            'sum(price) as total_price',
            'count(1) as total_order'
        ), '', $this->start, $this->num);
        
        $allSuids = array_merge(Tool_Array::getFields($this->summaryBills, 'suid'), Tool_Array::getFields($this->prepayBills, 'suid'));
        if (!empty($allSuids))
        {
            $this->staffList = Tool_Array::list2Map(Admin_Api::getStaffs($allSuids), 'suid');
        }
    }

    protected function outputBody()
    {
        $app = '/finance/bill_list_for_day.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->billList['total'], $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search', $this->searchConf);
        $this->smarty->assign('summary_bills', $this->summaryBills);
        $this->smarty->assign('bill_list', $this->billList);
        $this->smarty->assign('types', $this->types);
        $this->smarty->assign('remain_balance', $this->remainBalance);
        $this->smarty->assign('paid_sources', Appconf_Finance::supplierPayment4Show());
        $this->smarty->assign('staff_list', $this->staffList);
        $this->smarty->assign('types_desc', Conf_Money_Out::$STATUS_DESC);
        $this->smarty->assign('prepayBills', $this->prepayBills);
        $this->smarty->assign('prepayBillDetails', $this->prepayBillDetails);
        $this->smarty->assign('bankPaySum', $this->bankPaySum);

        $this->smarty->display('finance/supplier_bill_list_for_day.html');
    }

    private function _genWhere()
    {
        $types = !empty($this->searchConf['type']) ? array($this->searchConf['type']) : $this->types;
        $where = ' status=0 and type in (' . implode(',', $types) . ')';

        if (!empty($this->searchConf['end_date']))
        {
            $where .= ' and date(ctime)>=date("' . $this->searchConf['start_date'] . '")' . ' and date(ctime)<=date("' . $this->searchConf['end_date'] . '")';
        }
        else
        {
            $where .= ' and date(ctime)=date("' . $this->searchConf['start_date'] . '")';
        }

        if (!empty($this->searchConf['paid_source']))
        {
            $where .= ' and paid_source=' . $this->searchConf['paid_source'];
        }

        if (!empty($this->searchConf['suid']))
        {
            $where .= ' and suid=' . $this->searchConf['suid'];
        }

        return $where;
    }
}

$app = new App();
$app->run();