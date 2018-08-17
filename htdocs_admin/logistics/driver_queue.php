<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
	private $driverList;
	private $searchConf;
	private $carModelList;
	private $start;
	private $num = 200;

	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
		$this->searchConf = array(
            'wid' => $this->getWarehouseId(),
			'did' => Tool_Input::clean('r', 'did', TYPE_UINT),
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
			'line_id' => Tool_Input::clean('r', 'line_id', TYPE_UINT),
			'car_model' => Tool_Input::clean('r', 'car_model', TYPE_UINT),
			'step' => Tool_Input::clean('r', 'step', TYPE_UINT),
		);
        if (empty($this->searchConf['wid']))
        {
            $this->searchConf['wid'] = array_keys(App_Admin_Web::getAllowedWids4User());
        }
		if (!isset($_REQUEST['step']))
        {
            $this->searchConf['step'] = Conf_Driver::STEP_CHECK_IN;
        }
	}

	protected function main()
	{
        $order = array('check_time', 'asc');
		$this->driverList = Logistics_Api::getQueueList($this->searchConf, $this->start, $this->num, $order);

		$this->addFootJs(array('js/apps/driver_queue.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$app = '/logistics/driver_queue.php?'.http_build_query($this->searchConf);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->driverList['total'], $app);

		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('search_conf', $this->searchConf);
		$this->smarty->assign('driver_list', $this->driverList['list']);
		$this->smarty->assign('total', $this->driverList['total']);
		$this->smarty->assign('warehouse', App_Admin_Web::getAllowedWids4User());
		$this->smarty->assign('model_list', Conf_Driver::$CAR_MODEL);
		$this->smarty->assign('steps', Conf_Driver::$STEP_DESC);
        $this->smarty->assign('driver_info_editor', Conf_Driver::$DRIVER_INFO_EDITOR);

		$this->smarty->display('logistics/driver_queue.html');
	}
}

$app = new App('pri');
$app->run();
