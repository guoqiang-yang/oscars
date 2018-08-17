<?php

/**
 * 编辑货物上架单.
 */

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $objid;
    private $type;
    
    private $objAllInfos;
    private $skuInfos;
    
    protected function getPara()
    {
        $this->objid = Tool_Input::clean('r', 'objid', TYPE_UINT);
        $this->type = Tool_Input::clean('r', 'type', TYPE_UINT);
    }
    
    protected function checkPara()
    {
        if (empty($this->objid) || empty($this->type))
        {
            throw new Exception('common:params error');
        }
        
        if (!array_key_exists($this->type, Conf_Warehouse::$Virtual_Flags))
        {
            throw new Exception('非法操作！单据类型非法！');
        }
    }
    
    protected function main()
    {
        $this->objAllInfos = Warehouse_Location_Api::getBillDetailAndProducts($this->objid, $this->type);
        
        $sids = array_keys($this->objAllInfos['products']);
        $this->skuInfos = Shop_Api::getSkuInfos($sids);
        
        $this->addFootJs(array('js/apps/warehouse.js'));
    }
    
    protected function outputBody()
    {
        $abnormalTypes = array(
            Conf_Warehouse::VFLAG_DAMAGED => array(
                'name' => '残损移架',
                'loc' => Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_DAMAGED]['flag'],
            ),
            Conf_Warehouse::VFLAG_LOSS => array(
                'name' => '预盘亏',
                'loc' => Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_LOSS]['flag'],
            ),
        );
        
        $this->smarty->assign('objid', $this->objid);
        $this->smarty->assign('type', $this->type);
        $this->smarty->assign('objinfos', $this->objAllInfos);
        $this->smarty->assign('skuinfos', $this->skuInfos);
        $this->smarty->assign('warehouses', Conf_Warehouse::$WAREHOUSES);
        $this->smarty->assign('abnormal_types', $abnormalTypes);
        
        if ($this->type == Conf_Warehouse::VFLAG_SHIFT)
        {
            $this->smarty->display('warehouse/shelved_detail_new.html');
        }
        else
        {
            $this->smarty->display('warehouse/shelved_detail.html');
        }
    }
    
}

$app = new App();
$app->run();