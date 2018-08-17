<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
    private $id;
    
    private $detail;
    private $skuList;
    
    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
    }
    
    protected function checkAuth()
    {
        parent::checkAuth('/shop/processed_list');
    }


    protected function main()
    {
        $spo = new Shop_Processed_Order();
        $spop = new Shop_Processed_Order_Products();
        
        $this->detail = array($spo->get($this->id));
        $this->skuList = $spop->getProducts($this->id);
        
        Warehouse_Location_Api::parseLocationAndNum($this->skuList);
        
        Admin_Api::appendStaffSimpleInfo($this->detail, array('create_suid'));
        
        $ss = new Shop_Sku();
        $sids = Tool_Array::getFields($this->skuList, 'sid');
        $skuInfos = $ss->getBulk($sids);
        
        foreach($this->skuList as &$one)
        {
            $one['_sku'] = $skuInfos[$one['sid']];
        }
    }
    
    
    protected function outputBody()
    {
        $this->smarty->assign('detail', $this->detail[0]);
        $this->smarty->assign('sku_list', $this->skuList);
        $this->smarty->assign('warehouses', Conf_Warehouse::$WAREHOUSES);
        
        $this->smarty->display('shop/processed_order_detail.html');
    }
}

$app = new App();
$app->run();