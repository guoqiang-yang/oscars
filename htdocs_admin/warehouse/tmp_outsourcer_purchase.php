<?php

include_once('../../global.php');


class App extends App_Admin_Page
{
    private $city;
    private $search;
    private $skuInfos;
    private $productList;
    private $productInfos;

    protected function getPara()
    {
        $bdate = Tool_Input::clean('r', 'bdate', TYPE_STR);
        $edate = Tool_Input::clean('r', 'edate', TYPE_STR);

        $this->search = array(
            'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
            'pid' => Tool_Input::clean('r', 'pid', TYPE_UINT),
            'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
            'outsourcer_id' => Tool_Input::clean('r', 'outsourcer_id', TYPE_UINT),
            'bdate' => !empty($bdate) ? $bdate : date('Y-m-d', strtotime('-11 day')),
            'edate' => !empty($edate) ? $edate : date('Y-m-d', strtotime('-1 day')),
        );
    }
    
    protected function main()
    {
        $ret = Warehouse_Temp_Purchase_Api::getWaitTmpOutsourcerPurchaseList($this->search);
        $this->city = City_Api::getCity();
        
        $this->skuInfos = $ret['sku_infos'];
        $this->productList = $ret['list'];
        $this->productInfos = $ret['product_infos'];

        $this->addFootJs(array('js/apps/in_order.js'));
        
    }


    protected function outputBody()
    {
        $this->smarty->assign('search', $this->search);
        
        $this->smarty->assign('product_list', $this->productList);
        $this->smarty->assign('sku_infos', $this->skuInfos);
        $this->smarty->assign('product_infos', $this->productInfos);
        $this->smarty->assign('supplier_infos', Warehouse_Api::getOutSourcerList($this->city['city_id']));
        $this->smarty->assign('buytype_descs', Conf_Product::getBuyTypeDesc());
        $this->smarty->assign('managing_modes', Conf_Base::getManagingModes());

        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2_list', Conf_Sku::$CATE2);
        
        $this->smarty->display('warehouse/tmp_outsourcer_purchase.html');
    }
}

$app = new App();
$app->run();
