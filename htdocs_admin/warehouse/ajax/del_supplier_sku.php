<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    
    private $supplierId;
    private $skuId;
    
    protected function getPara()
    {
        $this->supplierId = Tool_Input::clean('r', 'supplier_id', TYPE_UINT);
        $this->skuId = Tool_Input::clean('r', 'sku_id', TYPE_UINT);
    }
    
    protected function checkPara()
    {
        if (empty($this->supplierId)||empty($this->skuId))
        {
            throw new Exception('参数错误，请查看输入采购价！');
        }
    }
    
    protected function main()
    {
        $wssl = new Warehouse_Supplier_Sku_List();
        
        $wssl->delete($this->supplierId, $this->skuId);
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