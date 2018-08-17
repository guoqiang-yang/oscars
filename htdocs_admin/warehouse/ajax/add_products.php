<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $oid;
    private $source;
	private $productStr;

	private $products = array();

	protected function getPara()
	{
		$this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
        $this->source = Tool_Input::clean('r', 'source', TYPE_UINT);
		$this->productStr = Tool_Input::clean('r', 'product_str', TYPE_STR);
	}

    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/edit_in_order');
    }
    
    protected function checkPara()
	{
		if (empty($this->oid))
		{
			throw new Exception('order:empty order id');
		}
        
        if (empty($this->source)||!array_key_exists($this->source, Conf_In_Order::$In_Order_Source))
        {
            throw new Exception('采购单来源异常，请联系技术同事！');
        }
	}

	private function parseProducts($str)
	{
		// 解析字符串
		$products = $pids = array();
		$items = array_filter(explode(',', $str));
		foreach ($items as $item)
		{
			list($sid, $num, $price) = explode(":", $item);
            if (!empty($num) && empty($price))
            {
                throw new Exception('采购单商品单价不能为0!');
            }
            // 可填价钱为零的商品-赠送使用
//			if (!empty($num) && empty($price))
//			{
//				throw new Exception('warehouse:in order must has price');
//			}

			$products[] = array('sid' => $sid, 'num' => $num, 'oid' => $this->oid, 'price' => round($price*100));
		}

		if (empty($products))
		{
			return array();
		}

		return $products;
	}

	protected function main()
	{
		$this->products = $this->parseProducts($this->productStr);
		Warehouse_Api::addProducts($this->oid, $this->source, $this->products);
        
        
        // 非临采供应商，添加商品到供应商关系
        $inorderInfo = Warehouse_Api::getOrderBase($this->oid);
        
        if(!in_array($inorderInfo['sid'], Conf_In_Order::$Temporary_Purchase_Suppliers))
        {
            $addProducts = array();
            foreach($this->products as $one)
            {
                if ($one['num'] <= 0) continue;

                $addProducts[] = array(
                    'sku_id' => $one['sid'],
                    'purchase_price' => $one['price'],
                );
            }
            $wssl = new Warehouse_Supplier_Sku_List();
            $wssl->addSkuWhenUnExist($inorderInfo['sid'], $addProducts);
        }
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

$app = new App('pri');
$app->run();

