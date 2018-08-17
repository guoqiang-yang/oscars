<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $num = 20;
    private $start;
    private $ctime;
    private $customerList;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_INT);
        $this->ctime = Tool_Input::clean('r', 'ctime', TYPE_STR);
    }

    protected function checkPara()
    {
        if (empty($this->ctime))
        {
            $this->ctime = date('Y-m-d');
        }
    }

    protected function main()
    {
        $fmi = new Finance_Money_In();

        // 为了控制数据量，取一个月内的数据

        $where = "status=0 and date(ctime)<date('$this->ctime')";
        $group = " group by cid";
        $table = " (select * from t_money_in_history  order by ctime desc) as new_ftable ";
        $this->customerList = $fmi->getCustomerListForFinance($table, $where, $group, $this->start, $this->num);

        //补全客户基本信息
        $cc = new Crm2_Customer();
        $cids = Tool_Array::getFields($this->customerList['list'], 'cid');
        if (!empty($cids))
        {
            $customerDatas = Tool_Array::list2Map($cc->getBulk($cids), 'cid');

            $as = new Admin_Staff();
            $saleUids = Tool_Array::getFields($customerDatas, 'sales_suid');
            $saleInfos = Tool_Array::list2Map($as->getUsers($saleUids), 'suid');

            foreach ($this->customerList['list'] as &$cinfo)
            {
                $cinfo['_customer'] = $customerDatas[$cinfo['cid']];
                $saleUid = $customerDatas[$cinfo['cid']]['sales_suid'];
                $cinfo['_saler'] = $saleInfos[$saleUid];
            }
        }

        //补充市场专员信息
        $as = new Admin_Staff();
        $as->appendSuers($this->customerList['list'], 'sales_suid');
    }

    protected function outputBody()
    {
        $app = '/finance/customer_list_for_finance.php?ctime=' . $this->ctime;

        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->customerList['total'], $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('ctime', $this->ctime);
        $this->smarty->assign('customer_list', $this->customerList['list']);
        $this->smarty->assign('total', $this->customerList['total']);
        $this->smarty->assign('total_price', $this->customerList['total_price']);

        $this->smarty->display('finance/customer_list_for_finance.html');
    }
}

$app = new App();
$app->run();