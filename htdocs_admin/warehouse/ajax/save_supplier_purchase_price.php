<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $supplierId;
    private $skuId;
    private $purchasePrice;
    
    protected function getPara()
    {
        $this->supplierId = Tool_Input::clean('r', 'supplier_id', TYPE_UINT);
        $this->skuId = Tool_Input::clean('r', 'sku_id', TYPE_UINT);
        $this->purchasePrice = abs(Tool_Input::clean('r', 'purchase_price', TYPE_NUM)*100);
    }
    
    protected function checkPara()
    {
        if (empty($this->supplierId)||empty($this->skuId)||empty($this->purchasePrice))
        {
            throw new Exception('参数错误，请查看输入采购价！');
        }
    }
    
    protected function main()
    {
        $wssl = new Warehouse_Supplier_Sku_List();
        $supplierInfo = Warehouse_Api::getSupplierAndSkuList($this->supplierId);
        $products = Tool_Array::list2Map($supplierInfo['products'], 'sku_id');
        $diffPrice = ($products[$this->skuId]['purchase_price'] - $this->purchasePrice)/100;

        if ($diffPrice != 0)
        {
            $wssl->modifyPurchasePrice($this->supplierId, $this->skuId, $this->purchasePrice);
        }
    }
    
    protected function outputBody()
    {
		$response = new Response_Ajax();
		$response->setContent(array('st'=>1));
		$response->send();

		exit;
    }
}

$app = new App();
$app->run();