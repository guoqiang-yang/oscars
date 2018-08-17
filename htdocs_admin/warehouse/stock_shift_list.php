<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
	private $num = 20;
	private $start;
	private $searchConf;
	
	private $shiftList;
	
	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
		$this->searchConf = array(
			'ssid' => Tool_Input::clean('r', 'ssid', TYPE_UINT),
			'src_wid' => Tool_Input::clean('r', 'src_wid', TYPE_UINT),
			'des_wid' => Tool_Input::clean('r', 'des_wid', TYPE_UINT),
			'step' => Tool_Input::clean('r', 'step', TYPE_INT),
			'create_suid' => Tool_Input::clean('r', 'create_suid', TYPE_UINT),
            'sku_id' => Tool_Input::clean('r', 'sku_id', TYPE_UINT),
		);
	}

	protected function checkPara()
    {
        $curCity = City_Api::getCity();
        if (empty($this->searchConf['src_wid']) && empty($this->searchConf['des_wid']))
        {
            if (empty($this->_user['city_wid_map'][$curCity['city_id']]))
            {
                $this->searchConf['src_wid'] = -1;
            }
            else
            {
                $allowedWidsStr = implode(',', $this->_user['city_wid_map'][$curCity['city_id']]);
                $this->searchConf['no_src_and_des_wid'] = sprintf(' and (src_wid in (%s) or des_wid in (%s))', $allowedWidsStr, $allowedWidsStr);
            }
        }
        if($this->searchConf['step'] < 0)
        {
            $this->searchConf['status'] = abs($this->searchConf['step']);
            $this->searchConf['step'] = Conf_Stock_Shift::STEP_CREATE;
        }
    }

    protected function main()
	{
		$this->shiftList = Warehouse_Api::getStockShiftList($this->searchConf, $this->start, $this->num);
		
		$this->addFootJs(array('js/apps/stock.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
	    if (is_array($this->searchConf['src_wid']) && is_array($this->searchConf['des_wid']))
        {
            unset($this->searchConf['src_wid']);
            unset($this->searchConf['des_wid']);
        }
		$app = '/warehouse/stock_shift_list.php?' . http_build_query($this->searchConf);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->shiftList['total'], $app);

		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('buyers', Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_BUYER_NEW));
		$this->smarty->assign('steps', Conf_Stock_Shift::$Step_Descs);
        if($this->searchConf['status'] > 0)
        {
            $this->searchConf['step'] = 0 - $this->searchConf['status'];
        }
		$this->smarty->assign('search_conf', $this->searchConf);
		$this->smarty->assign('shift_list', $this->shiftList['data']);
		$this->smarty->assign('total', $this->shiftList['total']);
		
		$this->smarty->display('warehouse/stock_shift_list.html');
	}
	
}

$app = new App('pri');
$app->run();