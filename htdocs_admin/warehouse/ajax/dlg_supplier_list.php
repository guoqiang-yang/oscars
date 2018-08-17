<?php


include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	// cgi参数
	private $start;
	private $searchConf;

	// 中间结果
	private $num = 200;
	private $total;
	private $suppliers;

	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
		$this->searchConf = array(
            'keyword' => Tool_Input::clean('r', 'keyword', TYPE_STR),
		);
	}

	protected function main()
	{
		$order = 'order by sid desc';
		$res = Warehouse_Api::getSupplierList($this->searchConf, $order, $this->start, $this->num);
		$this->suppliers = $res['list'];
		$this->total = $res['total'];
	}

	protected function outputPage()
	{
		$app = '/warehouse/supplier_list.php?' . http_build_query($this->searchConf);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('search_conf', $this->searchConf);
		$this->smarty->assign('suppliers', $this->suppliers);
		$this->smarty->assign('total', $this->total);
		$this->smarty->assign('managing_modes', Conf_Base::getManagingModes());
		$html = $this->smarty->fetch('warehouse/dlg_supplier_list.html');

		$result = array('html' => $html);
		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pub');
$app->run();
