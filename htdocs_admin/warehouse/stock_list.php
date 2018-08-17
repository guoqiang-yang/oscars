<?php
include_once ('../../global.php');

class App extends App_Admin_Page
{
	private $total;
	private $sku;
    
	private $brands;
	private $models;
    
    private $num = 20;
    private $start;
    private $searchConf;
    private $allowedWids;

	protected function getPara()
	{
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->allowedWids = $this->getAllowedWids4User(false);
     
        $this->searchConf = array(
            'bid' => Tool_Input::clean('r', 'bid', TYPE_UINT),
            'mid' => Tool_Input::clean('r', 'mid', TYPE_UINT),
            'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
        );
        
        $cate1 = Tool_Input::clean('r', 'cate1', TYPE_UINT);
        $this->searchConf['cate1'] = !empty($cate1)? $cate1: 1;
        $this->searchConf['cate2'] = Tool_Input::clean('r', 'cate2', TYPE_UINT);
        if (empty($this->searchConf['cate2']))
        {
            $cate2List = Conf_Sku::$CATE2[$this->searchConf['cate1']];
			$this->searchConf['cate2'] = array_shift(array_keys($cate2List));
        }
        
        if (!array_key_exists($this->searchConf['wid'], $this->allowedWids))
        {
            $this->searchConf['wid'] = 0;
        }
        
	}

	protected function checkPara()
	{
        
	}

	protected function main()
	{
		$res = Shop_Api::getSkuList($this->searchConf, $this->start, $this->num);
        
		$this->sku = $res['list'];
		$this->total = $res['total'];

		$this->brands = Shop_Api::getBrandList($this->searchConf['cate2']);
		$this->models = Shop_Api::getModelList($this->searchConf['cate2']);
        
		//Warehouse_Api::appendStock($this->wid, $this->sku);
		//Warehouse_Api::appendStock(0, $this->sku);
       
        $sids = Tool_Array::getFields($this->sku, 'sid');
        $wids = (!empty($this->searchConf['wid']) && array_key_exists($this->searchConf['wid'], $this->allowedWids))?
                    $this->searchConf['wid']: array_keys($this->allowedWids);
        
        $stocks = Warehouse_Api::getStockBySidsWids($sids, $wids);
        
        foreach($this->sku as &$item)
        {
            $item['_stock'] = array_key_exists($item['sid'], $stocks)? $stocks[$item['sid']]: array();
        }
        
		$this->addFootJs(array('js/apps/stock.js'));
		$this->addCss(array());
	}

	protected function outputBody()
	{
		$app = '/warehouse/stock_list.php?' . http_build_query($this->searchConf);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

		$this->smarty->assign('pageHtml', $pageHtml);
		$this->smarty->assign('total', $this->total);
		$this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
		$this->smarty->assign('cate2_list', Conf_Sku::$CATE2[$this->searchConf['cate1']]);
		$this->smarty->assign('search_conf', $this->searchConf);
		$this->smarty->assign('list', $this->sku);
		$this->smarty->assign('brands', $this->brands);
		$this->smarty->assign('models', $this->models);
        $this->smarty->assign('_allowed_warehouses', $this->getAllowedWids4User(false));

		$this->smarty->display('warehouse/stock_list.html');
	}
    
}

$app = new App('pri');
$app->run();

