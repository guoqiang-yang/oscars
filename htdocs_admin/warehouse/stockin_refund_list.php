<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    
    private $num = 40;
    private $start = 0;
    
    private $searchConf;
    
    private $refundList = array();
    
    protected function getPara()
    {
        $this->searchConf = array(
            'stockin_id' => Tool_Input::clean('r', 'stockin_id', TYPE_UINT),
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'supplier_id' => Tool_Input::clean('r', 'supplier_id', TYPE_UINT),
            'step' => Tool_Input::clean('r', 'step', TYPE_UINT),
            'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
        );
        
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
    }

    protected function checkPara()
    {
        $curCity = City_Api::getCity();
        if (empty($this->searchConf['wid']))
        {
            if (empty($this->_user['city_wid_map'][$curCity['city_id']]))
            {
                $this->searchConf['wid'] = -1;
            }
            else
            {
                $this->searchConf['wid'] = $this->_user['city_wid_map'][$curCity['city_id']];
            }
        }
    }
    
    protected function main()
    {
        $wsir = new Warehouse_Stock_In_Refund();
        
        $this->refundList = $wsir->getList($this->searchConf, $this->start, $this->num);
        
        $supplierIds = $suids = array();
        
        foreach ($this->refundList['data'] as $one)
        {
            $supplierIds[] = $one['supplier_id'];
            $suids[] = $one['suid'];
        }
        
        // 供应商信息 
        if (!empty($supplierIds))
        {
            $ws = new Warehouse_Supplier();
            $supplierInfos = Tool_Array::list2Map($ws->getBulk($supplierIds), 'sid');
        }
        
        // 管理员信息
        if (!empty($suids))
        {
            $as = new Admin_Staff;
            $adminorInfos = Tool_Array::list2Map($as->getUsers($suids), 'suid');
        }
        
        foreach ($this->refundList['data'] as &$_refund)
        {
            $_refund['_supplier'] =$supplierInfos[$_refund['supplier_id']];
            $_refund['_admin'] = $adminorInfos[$_refund['suid']];
        }

        $this->addFootJs('js/apps/warehouse.js');
    }
    
    protected function outputBody()
    {
        if (is_array($this->searchConf['wid']))
        {
            unset($this->searchConf['wid']);
        }
        $total = $this->refundList['total'];
        $app = '/warehouse/stockin_refund_list.php?' . http_build_query($this->searchConf);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $total, $app);
        
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $total);
        $this->smarty->assign('refund_list', $this->refundList['data']);
        $this->smarty->assign('searchConf', $this->searchConf);
        $this->smarty->assign('refund_descs', Conf_Stockin_Refund::$Refund_Descs);
        
        $this->smarty->display('warehouse/stockin_refund_list.html');
    }
}

$app = new App();
$app->run();