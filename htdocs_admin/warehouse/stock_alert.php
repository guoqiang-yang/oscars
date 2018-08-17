<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
	// cgi参数
	private $start;
	private $wid;

	// 中间结果
	private $products;
    private $total;
	private $num = 20;

	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT); 
		$this->wid = $this->getWarehouseId();
		
		$this->wid = !empty($this->wid)? $this->wid: Conf_Warehouse::WID_3;
	}

	protected function main()
	{
		$ret = Warehouse_Api::getAlertList($this->wid, $this->start, $this->num);
        $this->total = $ret['total'];
        $this->products = $ret['data'];
		
		$this->addFootJs(array());
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$app = '/warehouse/stock_alert.php?wid='.$this->wid;
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);
		$this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('products', $this->products);
		$this->smarty->assign('wid', $this->wid);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('_warehouseList', $this->getAllowedWarehouses());
        
        
		$this->smarty->display('warehouse/stock_alert.html');
	}
}

$app = new App('pri');
$app->run();

