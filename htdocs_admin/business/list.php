<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
	// cgi参数
	private $start;
	private $searchConf;

	// 中间结果
	private $num = 20;
	private $total;
	private $businessList;
	private $salesmanList;
	private $isSales;
    private $teamMemberInfos;

	
	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
		$this->searchConf = array(
			'sales_suid' => Tool_Input::clean('r', 'sales_suid', TYPE_UINT),
			'bid' => Tool_Input::clean('r', 'bid', TYPE_UINT),
			'name' => Tool_Input::clean('r', 'name', TYPE_STR),
			'contract_name' => Tool_Input::clean('r', 'contract_name', TYPE_STR),
			'contract_phone' => Tool_Input::clean('r', 'contract_phone', TYPE_STR),
		);
	}

	protected function main()
	{
		$this->isSales = false;
		if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW) && !in_array($this->_uid, Conf_Admin::$SUPER_SALES))
		{
            $this->searchConf['sales_suid'] = !empty($this->searchConf['sales_suid']) && in_array($this->searchConf['sales_suid'], $this->_user['team_member'])
                    ? $this->searchConf['sales_suid']:$this->_uid;
            
			$this->isSales = true;
		}

		$res = Crm2_Api::getBusinessList($this->searchConf, $this->start, $this->num);
		$this->businessList = $res['list'];
		$this->total = $res['total'];
		$this->salesmanList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW, 0);
        
        // 取团队成员用户信息
        if (count($this->_user['team_member']) > 1)
        {
            $this->teamMemberInfos = Admin_Api::getStaffs($this->_user['team_member']);
        }
        
		$this->addFootJs(array('js/apps/business.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$app = '/business/list.php?' . http_build_query($this->searchConf);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('search_conf', $this->searchConf);
		$this->smarty->assign('business', $this->businessList);
		$this->smarty->assign('total', $this->total);
		$this->smarty->assign('salesman_list', $this->salesmanList);
		$this->smarty->assign('is_sales', $this->isSales);
		$this->smarty->assign('uid', $this->_uid);
        $this->smarty->assign('team_members', $this->teamMemberInfos);

		$this->smarty->display('business/list.html');
	}
}

$app = new App('pri');
$app->run();

