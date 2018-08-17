<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
	private $start;
	private $total;
	private $searchConf;
	private $stockInLists;
	private $buyerList;
	private $num = 20;

	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
		$this->searchConf = array(
			'id' => Tool_Input::clean('r', 'id', TYPE_UINT),
			'sid' => Tool_Input::clean('r', 'sid', TYPE_UINT),
			'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
			'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
			'buyer_uid' => Tool_Input::clean('r', 'buyer_uid', TYPE_UINT),
			'payment_type' => Tool_Input::clean('r', 'payment_type', TYPE_UINT),
			'from_date' => Tool_Input::clean('r', 'from_date', TYPE_STR),
			'end_date' => Tool_Input::clean('r', 'end_date', TYPE_STR),
            'step' => Tool_Input::clean('r', 'step', TYPE_UINT),
            'source' => Tool_Input::clean('r', 'source', TYPE_UINT),
		);

        $this->searchConf['paid'] = isset($_REQUEST['paid'])? $_REQUEST['paid']: Conf_Base::STATUS_ALL;
        if($this->searchConf['sid']>0)
        {
            $this->num = 200;
        }
	}

    protected function checkPara()
    {
        $curCity = City_Api::getCity();
        if (empty($this->searchConf['wid']))
        {
            if (empty($this->_user['city_wid_map'][$curCity['city_id']]))
            {
                $this->searchConf['wid'] = -1;
            }
            else
            {
                $this->searchConf['wid'] = $this->_user['city_wid_map'][$curCity['city_id']];
            }
        }
    }

	protected function main()
	{
		if (!empty($this->searchConf['from_date']))
		{
			$this->searchConf['stime'] = $this->searchConf['from_date'] . ' 00:00:00';
		}
		if (!empty($this->searchConf['end_date']))
		{
			$this->searchConf['etime'] = $this->searchConf['end_date'] . ' 23:59:59';
		}

		$order = '';
		$res = Warehouse_Api::getStockInLists($this->searchConf, $order, $this->start, $this->num);
		$this->stockInLists = $res['list'];
		$this->total = $res['total'];
        $this->buyerList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_BUYER_NEW);

		$oids = array_unique(Tool_Array::getFields($res['list'], 'oid'));
		$orderInfos = Warehouse_Api::getOrderInfos($oids);

		foreach ($this->stockInLists as &$stockIn)
        {
            $stockIn['in_order_type'] = Conf_In_Order::$IN_ORDER_TYPES[$orderInfos[$stockIn['oid']]['in_order_type']];
        }

		$this->addFootJs(array('js/apps/stock.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
	    if (is_array($this->searchConf['wid']))
        {
            unset($this->searchConf['wid']);
        }
		$app = '/warehouse/stock_in_lists.php?' . http_build_query($this->searchConf);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

		$this->smarty->assign('buyerList', $this->buyerList);
		$this->smarty->assign('total', $this->total);
		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('searchConf', $this->searchConf);
		$this->smarty->assign('stock_in_lists', $this->stockInLists);
		$this->smarty->assign('all_pay_types', Conf_Stock::$PAYMENT_TYPES);
        $this->smarty->assign('all_steps', Conf_Stock_In::$Step_Descs);
        unset($this->searchConf['paid']);
        $page_url = '/warehouse/stock_in_lists.php?' . http_build_query($this->searchConf);
        $this->smarty->assign('page_url',$page_url);

		$this->smarty->display('warehouse/stock_in_lists.html');
	}
}

$app = new App('pri');
$app->run();

