<?php

include_once('../../global.php');


class App extends App_Admin_Page
{
    private $cate;
    private $search;
    private $skuInfos;
    private $productList;
    private $productInfos;
    
    protected function getPara()
    {
        $wid = $this->getWarehouseId();
        $deliveryDate = Tool_Input::clean('r', 'delivery_date', TYPE_STR);
        
        $dfWid = $this->_getDefaultWid();
        
        $this->search = array(
            'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
            'pid' => Tool_Input::clean('r', 'pid', TYPE_UINT),
            'wid' => $wid?: $dfWid,
            'delivery_date' => !empty($deliveryDate)? $deliveryDate: date('Y-m-d'),
        );
    }
    
    protected function main()
    {
        $ret = Warehouse_Temp_Purchase_Api::getWaitTmpPurchaseList($this->search);
        
        $this->skuInfos = $ret['sku_infos'];
        $this->productList = $ret['list'];
        $this->productInfos = $ret['product_infos'];
        
        $this->addFootJs(array('js/apps/in_order.js'));
        
    }


    protected function outputBody()
    {
        $this->smarty->assign('cate', $this->cate);
        $this->smarty->assign('search', $this->search);
        
        $this->smarty->assign('product_list', $this->productList);
        $this->smarty->assign('sku_infos', $this->skuInfos);
        $this->smarty->assign('product_infos', $this->productInfos);
        $this->smarty->assign('buytype_descs', Conf_Product::getBuyTypeDesc());
        $this->smarty->assign('managing_modes', Conf_Base::getManagingModes());

        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2_list', Conf_Sku::$CATE2);
        
        $this->smarty->display('warehouse/tmp_purchase2.html');
    }
    
    private function _getDefaultWid()
    {
        $cityInfo = City_Api::getCity();
        
        $cityId = $cityInfo['city_id'];
        
        $wids = Conf_Warehouse::$WAREHOUSE_CITY[$cityId];
        
        return !empty($wids)? $wids[0]: Conf_Warehouse::WID_3;
    }
}

$app = new App();
$app->run();
