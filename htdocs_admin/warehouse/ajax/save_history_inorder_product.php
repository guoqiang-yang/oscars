<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $source;
    private $productList;
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->source = Tool_Input::clean('r', 'source', TYPE_UINT);
        
        $productList = Tool_Input::clean('r', 'product_list', TYPE_STR);
        $this->productList = json_decode($productList, true);
        
    }
    
    protected function checkPara()
    {
        if (empty($this->oid))
        {
            throw new Exception('没有获取采购单ID，请联系管理员！');
        }
        
        if (empty($this->source))
        {
            throw new Exception('采购单类型异常，请联系管理员');
        }
        
        if (empty($this->productList))
        {
            throw new Exception('添加商品空！');
        }
    }
    
    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/edit_in_order');
    }
    
    protected function main()
    {
        Warehouse_Api::addProducts($this->oid, $this->source, $this->productList);
    }
    
    protected function outputPage()
	{
		$result = array('oid' => $this->oid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App();
$app->run();