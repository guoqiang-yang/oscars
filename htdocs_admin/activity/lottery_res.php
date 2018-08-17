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

	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
		$this->searchConf = array(
			'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
			'start_time' => Tool_Input::clean('r', 'start_time', TYPE_STR),
			'end_time' => Tool_Input::clean('r', 'end_time', TYPE_STR),
			'prize' => Tool_Input::clean('r', 'prize', TYPE_UINT),
		);
	}

	protected function main()
	{
		$data = Activity_Lottery_Api::getLotteryRecord($this->searchConf, $this->start, $this->num);
		$this->list = $data['list'];
		$this->total = $data['total'];

		$this->addFootJs(array('js/apps/lottery.js'));
	}

	protected function outputBody()
	{
		$app = '/activity/lottery_res.php?' . http_build_query($this->searchConf);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('total', $this->total);
		$this->smarty->assign('searchConf', $this->searchConf);
		$this->smarty->assign('list', $this->list);
		$this->smarty->assign('prize_list', Conf_Activity::$LOTTERY_CONF);

		$this->smarty->display('activity/lottery_res_201605.html');
	}
}

$app = new App('pri');
$app->run();

