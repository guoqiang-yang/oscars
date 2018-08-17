<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
	// cgi参数
	private $start;
	private $cuid;
	private $type;
	private $startTime;
	private $endTime;

	// 中间结果
	private $orders;
	private $num = 20;
	private $total;
	private $cinfo;

	protected function getPara()
	{
        echo "offline"; exit;
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
		$this->cuid = Tool_Input::clean('r', 'cuid', TYPE_UINT);
		$this->type = Tool_Input::clean('r', 'type', TYPE_UINT);
		$this->startTime = Tool_Input::clean('r', 'from_date', TYPE_STR);
		$this->endTime = Tool_Input::clean('r', 'end_date', TYPE_STR);
	}

	protected function main()
	{
		$order = ' order by oid desc ';
		// 获取订单的司机搬运工信息
		$res = Logistics_Coopworker_Api::getWorkerOrderList($this->cuid, $this->type, $this->start, $this->num, $order, $this->startTime, $this->endTime);

		$this->orders = $res['list'];
		$this->total = $res['total'];

		if ($this->type == 1)
		{
			$this->cinfo = Logistics_Api::getDriver($this->cuid);
		}
		else
		{
			$this->cinfo = Logistics_Api::getCarrier($this->cuid);
		}

		$this->addFootJs(array());
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$app = '/logistics/order_list.php?cuid=' . $this->cuid . '&type=' . $this->type . '&from_date=' . $this->startTime . '&end_date=' . $this->endTime;
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('total', $this->total);
		$this->smarty->assign('orders', $this->orders);
		$this->smarty->assign('cuid', $this->cuid);
		$this->smarty->assign('start_time', $this->startTime);
		$this->smarty->assign('end_time', $this->endTime);
		$this->smarty->assign('type', $this->type);
		$this->smarty->assign('cinfo', $this->cinfo);

		$this->smarty->display('logistics/order_list.html');
	}
}

$app = new App('pri');
$app->run();

