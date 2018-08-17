<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
	// cgi参数
	private $start;
	private $num = 20;
	private $total;
	private $list;

	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
	}

	protected function main()
	{
		$data = Activity_Api::getManjianList($this->start, $this->num);
		$this->list = $data['list'];
		$this->total = $data['total'];
	}

	protected function outputBody()
	{
		$app = '/activity/manjian_list.php';
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('total', $this->total);
		$this->smarty->assign('list', $this->list);

		$this->smarty->display('activity/manjian_list.html');
	}
}

$app = new App('pri');
$app->run();

