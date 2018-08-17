<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
	// cgi参数
	private $start;
	private $searchConf;
	private $num = 20;
	private $list;

	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
		$this->searchConf = array(
			'mid' => Tool_Input::clean('r', 'mid', TYPE_UINT),
			'msid' => Tool_Input::clean('r', 'msid', TYPE_UINT),
			'sid' => Tool_Input::clean('r', 'sid', TYPE_UINT),
		);
	}

	protected function main()
	{
		$order = 'order by id desc';
		$this->list = Merchant_Api::getList($this->searchConf, $this->start, $this->num, $order);

		$this->addFootJs(array('js/apps/merchant.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$this->smarty->assign('merchant_list', Conf_Merchant::$MERCHANT);
		$this->smarty->assign('list', $this->list);
		$this->smarty->assign('search_conf', $this->searchConf);

		$this->smarty->display('shop/merchant_sku_mapping.html');
	}
}

$app = new App('pri');
$app->run();

