<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
	private $driverList;
	private $searchConf;
	private $carModelList;
	private $sourceList;
	private $start;
	private $num = 20;
	private $hideCreate;
	private $types;
	private $wid;

	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
		$this->wid = $this->getWarehouseId();
		$this->searchConf = array(
            'wid' => $this->wid,
			'name' => Tool_Input::clean('r', 'name', TYPE_STR),
			'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
			'car_model' => Tool_Input::clean('r', 'car_model', TYPE_UINT),
			'source' => Tool_Input::clean('r', 'source', TYPE_UINT),
			'car_code' => Tool_Input::clean('r', 'car_code', TYPE_INT),
			'can_carry' => Tool_Input::clean('r', 'can_carry', TYPE_INT),
			'did' => Tool_Input::clean('r', 'id', TYPE_UINT),
			'type' => Tool_Input::clean('r', 'type', TYPE_STR),
            //'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
		);
        
        if (isset($_REQUEST['status']))
        {
            $this->searchConf['status'] = Tool_Input::clean('r', 'status', TYPE_UINT);
        }
        else
        {
            $this->searchConf['status'] = Conf_Base::STATUS_NORMAL;
        }
        if (empty($this->searchConf['wid']))
        {
            $this->searchConf['wid'] = array_keys(App_Admin_Web::getAllowedWids4User());
        }
	}

	protected function main()
	{
		$this->hideCreate = FALSE;
		if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_YUNNIAO))
		{
			$this->searchConf['source'] = 3;
			$this->hideCreate = TRUE;
		}
		$this->driverList = Logistics_Api::getDriverList($this->searchConf, $this->start, $this->num, 0);
		$this->carModelList = Logistics_Api::getModelList();
        
		$this->addFootJs(array('js/apps/role.js', 'js/apps/driver_queue.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
        if (empty($this->wid))
        {
            $this->searchConf['wid'] = 0;
        }
		$app = '/logistics/driver.php?'.http_build_query($this->searchConf);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->driverList['total'], $app);

		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('search_conf', $this->searchConf);
		$this->smarty->assign('driver_list', $this->driverList['list']);
		$this->smarty->assign('total', $this->driverList['total']);
		$this->smarty->assign('warehouse', App_Admin_Web::getAllowedWids4User());
		$this->smarty->assign('model_list', $this->carModelList['list']);
		$this->smarty->assign('source_list', $this->sourceList['list']);
		$this->smarty->assign('car_code_list', Conf_Driver::$CAR_CODE);
		$this->smarty->assign('can_carry_list', Conf_Driver::$CAN_CARRY);
		$this->smarty->assign('hide_create', $this->hideCreate);
        $this->smarty->assign('driver_info_editor', Conf_Driver::$DRIVER_INFO_EDITOR);

        // 临时写死-北京
        $this->smarty->assign('trans_scopes', Conf_Driver::$TRANS_SCOPES[Conf_City::BEIJING]);

		$this->smarty->display('logistics/driver.html');
	}
}

$app = new App('pri');
$app->run();
