<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $start;
	private $searchConf;
	private $num = 20;
	private $adminList;
	private $actionTypeList;
	private $total;
	private $list;
	
	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
		$this->searchConf = array(
			'admin_id' => Tool_Input::clean('r', 'admin_id', TYPE_UINT),
			'action_type' => Tool_Input::clean('r', 'action_type', TYPE_UINT),
            'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
			'start_time' => Tool_Input::clean('r', 'start_time', TYPE_STR),
			'end_time' => Tool_Input::clean('r', 'end_time', TYPE_STR),
		);
	}

	protected function main()
	{
		$res = Admin_Api::getOrderActionLogList($this->searchConf, $this->start, $this->num);
		$this->list = $res['list'];
		$this->total = $res['total'];
		$this->adminList = Admin_Api::getStaffList();
		$this->actionTypeList = Conf_Order_Action_Log::$ACTION_TYPE;
	}

	protected function outputBody()
	{
		$app = '/admin/order_action_log.php?' . http_build_query($this->searchConf);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('total', $this->total);
		$this->smarty->assign('search_conf', $this->searchConf);
		$this->smarty->assign('list', $this->list);
		$this->smarty->assign('admin_list', $this->adminList['list']);
		$this->smarty->assign('action_type_list', $this->actionTypeList);
		$this->smarty->assign('total', $this->total);

		$this->smarty->display('admin/order_action_log.html');
	}
}

$app = new App('pri');
$app->run();

