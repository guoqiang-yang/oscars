<?php

/**
 * 编辑货物上架单.
 */

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $oid;
    private $order;
    private $type;
    private $skuInfos;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->type = Tool_Input::clean('r', 'type', TYPE_UINT);
    }
    
    protected function checkPara()
    {
        if (empty($this->oid))
        {
            throw new Exception('common:params error');
        }
    }

    protected function checkAuth()
    {
        parent::checkAuth('hc_shelved_other_stock_in_product');
    }

    protected function main()
    {
        $this->order = Warehouse_Api::getOtherStockOutOrderDetail($this->oid);
        foreach ($this->order['loc_list'] as &$_val)
        {
            $_val = array_keys($_val);
        }
        $sids =Tool_Array::getFields($this->order['products'], 'sid');
        $this->skuInfos = Shop_Api::getSkuInfos($sids);

        $this->addFootJs(array('js/apps/warehouse.js'));
    }
    
    protected function outputBody()
    {
        $this->smarty->assign('oid', $this->oid);
        $this->smarty->assign('type', $this->type);
        $this->smarty->assign('order', $this->order);
        $this->smarty->assign('skuinfos', $this->skuInfos);
        $this->smarty->assign('warehouses', Conf_Warehouse::$WAREHOUSES);
        
        $this->smarty->display('warehouse/other_stock_in_shelved_detail.html');
    }
    
}

$app = new App();
$app->run();