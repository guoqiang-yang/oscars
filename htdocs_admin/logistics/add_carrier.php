<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $cid;
	private $carrier;
	private $warehouse;

	protected function getPara()
	{
		$this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
	}

	protected function main()
	{
		if (0 != $this->cid)
		{
			$this->carrier = Logistics_Api::getCarrier($this->cid);
		}

        $this->warehouse = App_Admin_Web::getAllowedWids4User();

        $this->addFootJs(array('js/apps/role.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$this->smarty->assign('carrier', $this->carrier);
		$this->smarty->assign('warehouse', $this->warehouse);
		$this->smarty->assign('referer', $_SERVER['HTTP_REFERER']);

		$this->smarty->display('logistics/add_carrier.html');
	}
}

$app = new App('pri');
$app->run();
