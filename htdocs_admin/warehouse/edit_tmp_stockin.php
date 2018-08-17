<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $oid;
    
    private $inorderInfo;
    private $inorderProducts;
    private $skuinfos;
    private $productsInSalesOrders;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
    }
    
    protected function main()
    {
        echo "页面下线！！！";exit;
        
        $this->inorderInfo = Warehouse_Api::getOrderBase($this->oid);
        $inorderProducts = Warehouse_Inorder_Api::getInorderProudctsNumButUnstockin($this->oid);
        $this->skuinfos = $inorderProducts['skuinfos'];
        
        // 获取未入库的商品在订单中空采数据
        $sids = array_keys($inorderProducts['products']);
        $wid = $this->inorderInfo['wid'];
        $this->productsInSalesOrders = Warehouse_Temp_Purchase_Api::getSalesOrderTmpPurchaseBySids($sids, $wid, $this->inorderInfo['ctime']);
        
        $this->inorderProducts = array();
        foreach($inorderProducts['products'] as $sid=>&$one)
        {
            $one['has_sales_order'] = 0;
            if (array_key_exists($sid, $this->productsInSalesOrders))
            {
                $one['has_sales_order'] = 1;
                $this->inorderProducts[] = $one;
                unset($inorderProducts['products'][$sid]);
            }
        }
        
        $this->inorderProducts = array_merge($this->inorderProducts, $inorderProducts['products']);
        
        $this->addFootJs(array('js/apps/stock.js'));
    }
    
    protected function outputBody()
    {
        $this->smarty->assign('oid', $this->oid);
        $this->smarty->assign('inorder_info', $this->inorderInfo);
        $this->smarty->assign('products', $this->inorderProducts);
        $this->smarty->assign('skuinfos', $this->skuinfos);
        $this->smarty->assign('products_sales_order', $this->productsInSalesOrders);
        
        $this->smarty->display('warehouse/edit_tmp_stockin.html');
    }
}

$app = new App();
$app->run();