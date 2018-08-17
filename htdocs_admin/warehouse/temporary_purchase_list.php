<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $myWid;
    private $dfWid;
    private $buyDate;
    
    private $productList = array();
    private $unProductList = array();   //已建完采购单商品列表
    
    protected function getPara()
    {
        $this->dfWid = $this->getWarehouseId();
        $this->buyDate = Tool_Input::clean('r', 'buy_date', TYPE_STR);
        
        if (empty($this->buyDate))
        {
            $this->buyDate = date('Y-m-d');
        } 
        $this->dfWid = $this->dfWid==0? Conf_Warehouse::WID_3: $this->dfWid;
        $this->myWid = $this->_user['wid'];
        
    }
    
    protected function main()
    {
        // 临采商品列表
        $this->productList = Warehouse_Temp_Purchase_Api::getHadPurchasedProductList(
            $this->buyDate, $this->dfWid);
        
        foreach($this->productList['data'] as $key=>&$product)
        {
            $remainNum = $product['temp_num']-$product['in_order_num'];
            $product['remain_num'] = $remainNum>=0? $remainNum: 0;
            
            if ($remainNum <= 0)
            {
                $this->unProductList[] = $product;
                unset($this->productList['data'][$key]);
            }
        }
        
		$this->addFootJs(array('js/apps/in_order.js'));
		$this->addCss(array());

    }
    
    protected function outputBody()
    {
        $this->smarty->assign('my_wid', $this->myWid);
        $this->smarty->assign('df_wid', $this->dfWid);
        $this->smarty->assign('buy_date', $this->buyDate);
        $this->smarty->assign('product_list', $this->productList['data']);
        $this->smarty->assign('un_product_list', $this->unProductList);
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2_list', Conf_Sku::$CATE2);
        
        $this->smarty->display('warehouse/temporary_purchase_list.html');
    }
    
}

$app = new App();
$app->run();