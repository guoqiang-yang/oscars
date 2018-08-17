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
	}

	protected function main()
	{
		$data = Search_Api::getHotList($this->start, $this->num);
		$this->list = $data['list'];
		$this->total = $data['total'];

		$this->addFootJs(array('js/apps/search.js'));
	}

	protected function outputBody()
	{
		$app = '/activity/search_hot.php?' . http_build_query($this->searchConf);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('total', $this->total);
		$this->smarty->assign('searchConf', $this->searchConf);
		$this->smarty->assign('list', $this->list);
		$this->smarty->assign('city_list', City_Api::getCityList(true));

		$this->smarty->display('activity/search_hot.html');
	}
}

$app = new App('pri');
$app->run();

