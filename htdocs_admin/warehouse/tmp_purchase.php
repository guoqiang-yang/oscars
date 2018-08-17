<?php

include_once('../../global.php');


class App extends App_Admin_Page
{
    private $cate;
    private $search;
    private $skuInfos;
    private $orderList;
    private $totalBuyNums;
    
    protected function getPara()
    {
        $wid = $this->getWarehouseId();
        $deliveryDate = Tool_Input::clean('r', 'delivery_date', TYPE_STR);
        
        $this->search = array(
            'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
            'pid' => Tool_Input::clean('r', 'pid', TYPE_UINT),
            'wid' => $wid?: Conf_Warehouse::WID_3,
            'delivery_date' => !empty($deliveryDate)? $deliveryDate: date('Y-m-d'),
        );
        
        $this->cate = Tool_Input::clean('r', 'cate', TYPE_UINT);
    }
    
    protected function main()
    {
        switch ($this->cate)
        {
            case 0:
                $ret = Warehouse_Temp_Purchase_Api::getTmpPurchaseListByType($this->search, 'wait_buy');
        
                $this->skuInfos = $ret['sku_infos'];
                $this->orderList = $ret['list'];
                $this->totalBuyNums = $ret['stat_buy_num'];
                break;
            case 1:
                $ret = Warehouse_Temp_Purchase_Api::getTmpBoughtList($this->search);
                
                $this->productList = $ret['list'];
                $this->skuInfos = $ret['sku_infos'];
                $this->orderList = $ret['orders'];
                $this->totalBuyNums = $ret['stat_buy_num'];
                break;
            
            case 2:
                $ret = Warehouse_Temp_Purchase_Api::getTmpPurchaseListByType($this->search, 'alert');
        
                $this->skuInfos = $ret['sku_infos'];
                $this->orderList = $ret['list'];
                $this->totalBuyNums = $ret['stat_buy_num'];
                break;
            
            default:
                throw new Exception('页面不存在');
        }
        
        $this->addFootJs(array('js/apps/in_order.js'));
        
    }


    protected function outputBody()
    {
        $this->smarty->assign('cate', $this->cate);
        $this->smarty->assign('search', $this->search);
        $this->smarty->assign('warehose_list', $this->getAllowedWarehouses());
        
        $this->smarty->assign('product_list', $this->productList);
        $this->smarty->assign('order_list', $this->orderList);
        $this->smarty->assign('sku_infos', $this->skuInfos);
        $this->smarty->assign('total_buy_num', $this->totalBuyNums);
        
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2_list', Conf_Sku::$CATE2);
        
        $this->smarty->display('warehouse/tmp_purchase.html');
    }
}

$app = new App();
$app->run();
