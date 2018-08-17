<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
    private $num = 20;
    private $start;
    private $search;
    
    private $result;
    private $customerAcount = array();
    
    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->search = array(
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'objtype' => Tool_Input::clean('r', 'objtype', TYPE_UINT),
        );
    }
    
    protected function main()
    {
        $this->result = Tpfinance_Api::getTpfinanceHistoryList($this->search, $this->start, $this->num, true);
        
        // 附加客户信息
        $cc = new Crm2_Customer();
        $cc->appendInfos($this->result['list'], 'cid');
        
        // 附加操作着信息
        $as = new Admin_Staff();
        $as->appendSuers($this->result['list'], 'suid');
        
        // 跳转url
        foreach ($this->result['list'] as &$item)
        {
            $item['_objurl'] = '';
            
            if ($item['objtype']==Conf_Ex_Finance::HISTORY_OBJTYPE_REFUND)
            {
                $item['_objurl'] = '/order/edit_refund_new.php?rid='. $item['objid'];
            }
            else if (!empty($item['oid']))
            {
                $item['_objurl'] = '/order/order_detail.php?oid='. $item['oid'];
            }
        }
        
        if (!empty($this->search['cid']))
        {
            $tpfa = new Tpfinance_Account();
            $this->customerAcount = $tpfa->getVaildByCid($this->search['cid']);
            
            $cc = new Crm_Customer();
            $cc->appendInfo($this->customerAcount);
        }
    }
    
    protected function outputBody()
    {
        $app = '/finance/credit_history_list.php?' . http_build_query($this->search);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->result['total'], $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('bill_list', $this->result['list']);
        $this->smarty->assign('total', $this->result['total']);
        $this->smarty->assign('customer_account', $this->customerAcount);
        $this->smarty->assign('payment_types', Conf_Base::$PAYMENT_TYPES);
        
        $this->smarty->assign('search', $this->search);
        $this->smarty->assign('objtypes', Conf_Ex_Finance::getObjType());
        
        $this->smarty->display('finance/credit_history_list.html');
    }
    
}

$app = new App();
$app->run();