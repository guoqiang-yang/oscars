<?php

/**
 * 添加移库单的商品.
 * 
 *  1 添加移库单商品
 *  2 分配货位，数量【不写占用】，并记录到t_stock_shift_product表中的from_location字段
 *  3 出库时，处理库存/货位库存
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $products;
	private $ssid;
	
	private $ret;
	
	protected function getPara()
	{
		$this->ssid = Tool_Input::clean('r', 'ssid', TYPE_UINT);
		$products = Tool_Input::clean('r', 'products', TYPE_ARRAY);
		
		foreach($products as $one)
		{
			list($_sid, $_num) = explode(':', $one);
			$this->products[] = array(
				'sid' => $_sid,
				'num' => $_num,
			);
		}
	}
    
    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/stock_shift');
    }
	
	protected function checkPara()
	{
		if (empty($this->ssid))
		{
			throw new Exception('stock shift ID is empty!');
		}
	}
	
	protected function main()
	{
        $shiftInfos = Warehouse_Api::getStockShiftInfo($this->ssid);
        if($shiftInfos['status'] == Conf_Base::STATUS_NORMAL)
        {
            throw new Exception('调拔单发起申请后不能再添加商品！');
        }
		if (!empty($this->products))
		{
			$this->ret = Warehouse_Api::addStockShiftProducts($this->ssid, $this->products);
		}
		else 
		{
			$this->ret = -1;
		}
	}
	
	protected function outputBody()
	{
		$result = array('ssid'=>$this->ssid, 'ret' => $this->ret);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App();
$app->run();