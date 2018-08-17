<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
	// cgi参数
	private $start;
	private $num = 20;
	private $total;
	private $list;
    private $brandList;

	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
	}

	protected function main()
	{
	    $cb = new Activity_City_Brand();
        list($this->list, $this->brandList) = $cb->getList($this->start, $this->num);
		$this->total = $cb->getTotal();
        $this->addFootJs(array('js/apps/city_brand.js'));
	}

	protected function outputBody()
	{
		$app = '/activity/city_brand_list.php';
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('total', $this->total);
		$this->smarty->assign('list', $this->list);
        $this->smarty->assign('city_list', Conf_City::$CITY);
        $this->smarty->assign('brand_list',$this->brandList);

		$this->smarty->display('activity/city_brand_list.html');
	}
}

$app = new App('pri');
$app->run();

