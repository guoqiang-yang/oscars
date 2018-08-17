<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
	private $bdate;
    private $edate;
	private $skuid;    //商品sku_id
	private $type;
	private $wid;    //仓库编号
	private $start;
	private $reason;

	private $num = 20;
	private $searchConf;
	private $total;
	private $historyList;

	protected function getPara()
	{
		$this->bdate = Tool_Input::clean('r', 'bdate', TYPE_STR);
		$this->edate = Tool_Input::clean('r', 'edate', TYPE_STR);
		$this->skuid = Tool_Input::clean('r', 'sid', TYPE_UINT);
		$this->reason = Tool_Input::clean('r', 'reason', TYPE_UINT);
		$this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);

		if (isset($_REQUEST['type']))
		{
			$this->type = Tool_Input::clean('r', 'type', TYPE_INT);
		}
		else
		{
			$this->type = -1;
		}
        
	}

    protected function checkPara()
    {
        $curCity = City_Api::getCity();
        if (empty($this->wid))
        {
            if (empty($this->_user['city_wid_map'][$curCity['city_id']]))
            {
                $this->wid = -1;
            }
            else
            {
                $this->wid = $this->_user['city_wid_map'][$curCity['city_id']];
            }
        }
    }

	protected function main()
	{
        if (empty($_REQUEST))
        {
            $this->total = 0;
            $this->historyList = array();
            
            return;
        }
        
		$this->searchConf = array(
			'sid' => $this->skuid,
			'wid' => $this->wid,
			'bdate' => $this->bdate,
            'edate' => $this->edate,
			'type' => $this->type,
            'reason' => $this->reason,
		);

		$ret = Warehouse_Api::getHistoryList($this->searchConf, $this->start, $this->num);

		$this->total = $ret['total'];
		$this->historyList = $ret['data'];
	}

	protected function outputBody()
	{
	    if (is_array($this->searchConf['wid']))
        {
            unset($this->searchConf['wid']);
        }
		$app = '/warehouse/stock_history.php?' . http_build_query($this->searchConf);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('wid', $this->wid);
		$this->smarty->assign('reason', $this->reason);
		$this->smarty->assign('sid', $this->skuid);
		$this->smarty->assign('type', $this->type);
		$this->smarty->assign('bdate', $this->bdate);
        $this->smarty->assign('edate', $this->edate);
		$this->smarty->assign('allType', Conf_Warehouse::$Stock_History_Type);
		$this->smarty->assign('historyList', $this->historyList);
		$this->smarty->assign('total', $this->total);
        $this->smarty->assign('reasons', Conf_Warehouse::getStockHistoryReasons('stock_history_log'));

        $this->smarty->display('warehouse/stock_history.html');
	}


}

$app = new App('pri');
$app->run();