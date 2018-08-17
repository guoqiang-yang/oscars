<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
	// cgi参数
	private $start;
	private $num = 20;
	private $total;
	private $list;
	private $searchConf;
	private $mobile;

	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
		$this->searchConf = array(
			'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
			'mobile' => Tool_Input::clean('r', 'fmobile', TYPE_STR),
			'fcid' => Tool_Input::clean('r', 'fcid', TYPE_UINT),
		);
		$this->mobile = Tool_Input::clean('r', 'mobile', TYPE_STR);
	}

	protected function main()
	{
		if (!empty($this->mobile))
		{
			$customer = Crm2_Api::getByMobile($this->mobile);
			$this->searchConf['cid'] = $customer['cid'];
		}
		$data = Invite_Api::getInviteList($this->searchConf, $this->start, $this->num);
		$this->total = $data['total'];
		$this->list = $data['list'];

		$cids = Tool_Array::getFields($this->list, 'cid');
		if (!empty($cids))
		{
			$customers = Crm2_Api::getCustomers($cids);
			foreach ($this->list as &$item)
			{
				$item['_customer'] = $customers[$item['cid']];
			}
		}
	}

	protected function outputBody()
	{
		$app = '/activity/invite.php';
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('total', $this->total);
		$this->smarty->assign('list', $this->list);

		$this->smarty->display('invite/invite.html');
	}
}

$app = new App('pri');
$app->run();