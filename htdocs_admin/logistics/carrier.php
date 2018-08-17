<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $carrierList;
	private $searchConf;
	private $start;
	private $num = 20;
    private $wid;

	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->wid = $this->getWarehouseId();
		$this->searchConf = array(
			'wid' => $this->wid,
			'name' => Tool_Input::clean('r', 'name', TYPE_STR),
			'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
			'cid' => Tool_Input::clean('r', 'id', TYPE_UINT),
		);
        if (empty($this->searchConf['wid']))
        {
            $this->searchConf['wid'] = array_keys(App_Admin_Web::getAllowedWids4User());
        }
	}

	protected function main()
	{
		$this->carrierList = Logistics_Api::getCarrierList($this->searchConf, $this->start, $this->num, 1);

		$this->addFootJs(array('js/apps/role.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
        if (empty($this->wid))
        {
            $this->searchConf['wid'] = 0;
        }
		$app = '/logistics/carrier.php?' . http_build_query($this->searchConf);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->carrierList['total'], $app);
		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('search_conf', $this->searchConf);
		$this->smarty->assign('carrier_list', $this->carrierList['list']);
		$this->smarty->assign('total', $this->carrierList['total']);
		$this->smarty->assign('warehouse', App_Admin_Web::getAllowedWids4User());

		$this->smarty->display('logistics/carrier.html');
	}
}

$app = new App('pri');
$app->run();
