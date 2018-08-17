<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $wid;
	private $sid;

	//库存详情
	private $stock;
	private $sku;
	private $product;
	private $type;

	protected function getPara()
	{
        $this->wid = $this->getWarehouseId();
		$this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
		$this->type = Tool_Input::clean('r', 'type', TYPE_UINT);
        
        if (empty($this->wid) || empty($this->sid))
        {
            throw new Exception('操作失败');
        }
        
	}

	protected function main()
	{
		$res = Warehouse_Api::getStockDetail($this->wid, $this->sid);

		$this->stock = $res['stock'];
		$this->sku = $res['sku'];
		$this->product = $res['product'];

		$this->addFootJs(array('js/apps/stock.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$this->smarty->assign('sid', $this->sid);
		$this->smarty->assign('stock', $this->stock);
		$this->smarty->assign('sku', $this->sku);
        $this->smarty->assign('wid', $this->wid);
		$this->smarty->assign('product', $this->product);
		$this->smarty->assign('city_list', Conf_City::$CITY);
		$this->smarty->assign('city_wid_list', json_encode(Conf_Warehouse::$WAREHOUSE_CITY));
		$html = 'warehouse/edit_stock.html';
		$this->smarty->display($html);
	}
}

$app = new App('pri');
$app->run();
