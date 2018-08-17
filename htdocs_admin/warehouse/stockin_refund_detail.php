<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $srid;
    
    private $refundDetail = array();
    
    private $refundProducts = array();
    
    private $stockinDetail = array();
    
    protected function getPara()
    {
        $this->srid = Tool_Input::clean('r', 'srid', TYPE_UINT);
    }
    
    protected function main()
    {
        $wsir = new Warehouse_Stock_In_Refund();
        $this->refundDetail = $wsir->get($this->srid);
        
        $stockinId = $this->refundDetail['stockin_id'];
        if (!empty($stockinId))
        {
            $this->stockinDetail = Warehouse_Api::getStockInDetail($stockinId);
            $this->refundProducts = $this->stockinDetail['refund_products'][$this->srid];
        }
        else
        {
            $this->stockinDetail['customer'] = Warehouse_Api::getSupplier($this->refundDetail['supplier_id']);
            $this->stockinDetail['info']['refund_price'] = $this->refundDetail['price'];
            $this->refundProducts = Warehouse_Api::getRefundProductsBySrid($this->srid);
        }
        
		$this->addFootJs(array('js/apps/stock.js'));
		$this->addCss(array());
    }
    
    protected function outputBody()
    {
        $this->smarty->assign('stockin_refund_info', $this->refundDetail);
        $this->smarty->assign('stockin_refund_products', $this->refundProducts);
        $this->smarty->assign('stockin_info', $this->stockinDetail);
		$this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
		$this->smarty->assign('cate2_list_all', Conf_Sku::$CATE2);
		$this->smarty->assign('warehouse_list', Conf_Warehouse::$WAREHOUSES);
        $this->smarty->assign('status_descs', Conf_Stockin_Refund::$Refund_Descs);

		$this->smarty->display('warehouse/stockin_refund_detail.html');
    }
}

$app = new App();
$app->run();