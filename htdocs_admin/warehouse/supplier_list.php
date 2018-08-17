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
	private $suppliers;
    private $action;

    protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->action = Tool_Input::clean('r', 'action', TYPE_STR);
        $this->searchConf = array(
			'sid' => Tool_Input::clean('r', 'sid', TYPE_STR),
			'keyword' => Tool_Input::clean('r', 'keyword', TYPE_STR),
			'cate1' => Tool_Input::clean('r', 'cate1', TYPE_STR),
			'wid' => $this->getWarehouseId(),
            'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
            'managing_mode' => Tool_Input::clean('r', 'managing_mode', TYPE_UINT),
		);

        if (!isset($_REQUEST['wid']))
        {
            $this->searchConf['wid'] = 0;
        }
        
		if (!isset($_REQUEST['status']))
        {
            $this->searchConf['status'] = Conf_Base::STATUS_NORMAL;
        }
        if (!isset($_REQUEST['managing_mode']))
        {
            $this->searchConf['managing_mode'] = Conf_Base::STATUS_ALL;
        }
        $this->searchConf['city'] = $_COOKIE['city_id'];
	}

	protected function main()
	{
        //下载
        if ($this->action == 'download') {
            Invoice_Api::exportSupplierListByWhere($this->searchConf);
            exit;
        }
		$order = 'order by sid desc';
		$res = Warehouse_Api::getSupplierList($this->searchConf, $order, $this->start, $this->num);

		foreach ($res['list'] as &$item)
        {
            if (!empty($item['city']))
            {
                $supplierCity = explode(',', $item['city']);
                $item['city'] = $supplierCity;
            }
        }

		$this->suppliers = $res['list'];
		$this->total = $res['total'];
		$this->addFootJs(array('js/apps/supplier.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$app = '/warehouse/supplier_list.php?' . http_build_query($this->searchConf);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

		$this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('search_conf', $this->searchConf);
		$this->smarty->assign('suppliers', $this->suppliers);
		$this->smarty->assign('total', $this->total);
		$this->smarty->assign('city_list', Conf_City::$CITY);
        $this->smarty->assign('_warehouseList', $this->getAllowedWarehouses());
        $this->smarty->assign('status_list', Conf_Base::getSupplierStatusList());
        $this->smarty->assign('managing_modes', Conf_Base::getManagingModes());
        
		$this->smarty->display('warehouse/supplier_list.html');
	}
}

$app = new App('pri');
$app->run();

