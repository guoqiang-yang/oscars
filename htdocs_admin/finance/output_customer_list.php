<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $step;
    private $start;
    private $searchConf;
    private $num = 20;
    private $customerList;
    private $total;
    private $action;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->step = Tool_Input::clean('r', 'step', TYPE_STR);
        $this->action = Tool_Input::clean('r', 'action', TYPE_STR);
        $this->searchConf = array(
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
        );
    }

    protected function checkPara()
    {

    }

    protected function main()
    {
        //下载发票信息
        if($this->action == 'download')
        {
            Invoice_Api::exportCustomerListByWhere($this->searchConf);
            exit;
        }
        $customerList = Crm2_Api::getCustomerListForAdmin($this->searchConf, $this->_user , 'cid',$this->start, $this->num);
        $this->total = $customerList['total'];
        $this->customerList = $customerList['data'];
        if($this->total > 0){
            $cids = Tool_Array::getFields($this->customerList, 'cid');
            $oo = new Order_Order();
            $customerList = $oo->getAllCustomerAmount($cids);

            $io = new Invoice_Output();
            $invoiceList = $io->getAllCustomerAmount($cids);
            foreach ($this->customerList as $key => $item) {
                $amount1 = isset($customerList[$item['cid']]) ? $customerList[$item['cid']]['amount'] : 0;
                $amount2 = isset($invoiceList[$item['cid']]) ? $invoiceList[$item['cid']]['amount'] : 0;
                $amount3 = $amount1 - $amount2;
                $this->customerList[$key]['amount1'] = $amount1;
                $this->customerList[$key]['amount2'] = $amount2;
                $this->customerList[$key]['amount3'] = $amount3;
            }
        }
    }

    protected function outputBody()
    {
        $app = '/finance/output_customer_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('customer_list', $this->customerList);
        $this->smarty->assign('total', $this->total);

        $this->smarty->display('finance/output_customer_list.html');
    }
}

$app = new App('pri');
$app->run();