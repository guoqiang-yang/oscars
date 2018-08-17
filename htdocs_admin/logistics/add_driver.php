<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $source;
	private $model;
	private $driver;
	private $did;
	private $warehouse;
    
    private $cityId;
    private $transScopeInCity = array();

	protected function getPara()
	{
		$this->did = Tool_Input::clean('r', 'did', TYPE_UINT);
	}

	protected function checkAuth()
	{
		parent::checkAuth();

		if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_YUNNIAO))
		{
			$this->source['list'] = array(3 => '云鸟');
		}
		else
		{
			$this->source = Logistics_Api::getSourceList();
		}
	}

	protected function main()
	{
		$this->model = Logistics_Api::getModelList();

        $this->cityId = Conf_City::BEIJING;
        
		if (0 != $this->did)
		{
			$this->driver = Logistics_Api::getDriver($this->did);
            $this->driver['_trans_scope'] = !empty($this->driver['trans_scope'])?
                        explode(',', $this->driver['trans_scope']): array();
            $this->cityId = !empty($this->driver['city_id'])? $this->driver['city_id']: $this->cityId;
		}
        
        $this->transScopeInCity = array_key_exists($this->cityId, Conf_Driver::$TRANS_SCOPES)?
                                    Conf_Driver::$TRANS_SCOPES[$this->cityId]: array();
        
        $this->warehouse = App_Admin_Web::getAllowedWids4User();

		$this->addFootJs(array('js/apps/role.js'));
	}

	protected function outputBody()
	{ 
		$this->smarty->assign('source_list', $this->source['list']);
		$this->smarty->assign('model_list', $this->model['list']);
		$this->smarty->assign('driver', $this->driver);
		$this->smarty->assign('warehouse', $this->warehouse);
		$this->smarty->assign('car_code_list', Conf_Driver::$CAR_CODE);
		$this->smarty->assign('can_carry_list', Conf_Driver::$CAN_CARRY);
		$this->smarty->assign('referer', $_SERVER['HTTP_REFERER']);
        $this->smarty->assign('trans_scopes', json_encode(Conf_Driver::$TRANS_SCOPES));
        $this->smarty->assign('driver_info_editor', Conf_Driver::$DRIVER_INFO_EDITOR);
        $city = Conf_City::$CITY;
        $cities = array($_COOKIE['city_id'] => $city[$_COOKIE['city_id']]);
        unset($cities[Conf_City::XIANGHE]);

        $this->smarty->assign('all_cities', $cities);
        $this->smarty->assign('city_id', $this->cityId);
        $this->smarty->assign('trans_scope_in_city', $this->transScopeInCity);
        $this->smarty->assign('driver_trans_scope', json_encode($this->driver['_trans_scope']));
        $this->smarty->assign('car_provinces', Conf_Driver::$CAR_PROVINCES);
        
		$this->smarty->display('logistics/add_driver.html');
	}
}

$app = new App('pri');
$app->run();
