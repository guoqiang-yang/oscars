<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
	// cgi参数
	private $_type;

	// 中间结果
	private $_customers;

	protected function getPara()
	{
		$this->_type = Tool_Input::clean('r', 'type', TYPE_UINT);
	}

	protected function main()
	{
		//todo
		$this->_customers = array();

		$this->addFootJs(array());
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$this->smarty->assign('customers', $this->_customers);
		$this->smarty->display('crm2/stat.html');
	}
}

$app = new App('pri');
$app->run();

