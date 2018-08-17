<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $modelList;

	protected function getPara()
	{
	}

	protected function main()
	{
		$this->modelList = Logistics_Api::getModelList();

		$this->addFootJs(array('js/apps/role.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$this->smarty->assign('model_list', $this->modelList['list']);
		$this->smarty->assign('total', $this->modelList['total']);
		$this->smarty->display('logistics/car_model.html');
	}
}

$app = new App('pri');
$app->run();
