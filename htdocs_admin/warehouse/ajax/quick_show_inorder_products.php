<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $wid;
    
    private $orderInfo;
    private $stockList;
    
    private $stockInfo;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
    }
    
    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/in_order_list');
    }


    protected function main()
    {
        $this->orderInfo = Warehouse_Api::getOrderProducts($this->oid);
        $this->stockList = Warehouse_Api::getStockInLists(array('oid' => $this->oid), '', 0, 1000);

        //库存信息
        $sids = array();
        foreach ($this->orderInfo as $_info)
        {
            $sids = array_merge($sids, Tool_Array::getFields($_info, 'sid'));
        }

        $this->stockInfo = Tool_Array::list2Map(Warehouse_Security_Stock_Api::getSecurityStock($this->wid, $sids), 'sid');
        
    }
    
    protected function outputBody()
    {
        $this->smarty->assign('oid', $this->oid);
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);
        $this->smarty->assign('order', $this->orderInfo);
        $this->smarty->assign('stock_in_lists', $this->stockList['list']);
        $this->smarty->assign('stock_info', $this->stockInfo);
        
        $html = $this->smarty->fetch('warehouse/aj_quick_show_inorder_products.html');
        
        $result = array('html' => $html);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
    }
}

$app = new App();
$app->run();