<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
	private $cate1;
	private $cate2;
	private $cate3;
	private $bid;
	private $mid;
	private $brands = array();
	private $models = array();
	
	private $num = 100;
	private $start;
	private $ssid;	//移库单id
	private $queryString;
	private $wid;
	private $keyword;

	private $conf;
	private $products;
	private $total;
	private $ssinfo;
	private $orderProduct;
    private $type;
	
	protected function getPara()
	{
		$this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
		$this->queryString = Tool_Input::clean('r', 'query_str', TYPE_STR);
		$this->ssid = Tool_Input::clean('r', 'ssid', TYPE_UINT);
		$this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
		$this->keyword = Tool_Input::clean('r', 'keyword', TYPE_STR);
		$this->type = Tool_Input::clean('r', 'type', TYPE_STR);

		parse_str($this->queryString, $querys);
		
		$this->cate1 = isset($querys['cate1'])? $querys['cate1']: 0;
		$this->cate2 = isset($querys['cate2'])? $querys['cate2']: 0;
		$this->cate3 = isset($querys['cate3'])? $querys['cate3']: 0;
		$this->bid = isset($querys['bid'])? $querys['bid']: 0;
		$this->mid = isset($querys['mid'])? $querys['mid']: 0;
	}
	
	protected function checkPara()
	{
		if (empty($this->keyword))
		{
			if (empty($this->cate1))
			{
				$this->cate1 = 1;
			}
			if (empty($this->cate2))
			{
				$cate2List = Conf_Sku::$CATE2[$this->cate1];
				$this->cate2 = array_shift(array_keys($cate2List));
			}
			if (empty($this->cate3))
			{
				if (!empty(Conf_Sku::$CATE3[$this->cate2]))
				{
					$cate3List = Conf_Sku::$CATE3[$this->cate2];
					$this->cate3 = array_shift(array_keys($cate3List));
				}
			}
		}
	}
	
	protected function main()
	{
		//品牌信息
		if (empty($this->keyword))
		{
			$this->brands = Shop_Api::getBrandList($this->cate2, $this->cate3);
			$this->models = Shop_Api::getModelList($this->cate2, $this->cate3);
		}
		
		//产品信息
		$this->conf = array(
			'keyword' => $this->keyword,
			'cate1' => $this->cate1,
			'cate2' => $this->cate2,
			'cate3' => $this->cate3,
			'bid' => $this->bid,
			'mid' => $this->mid,
		);
		
		$order = 'order by sid desc';
		$res = Warehouse_Api::getProductsStockByCates($this->conf, $this->wid, $order, $this->start, $this->num);
		$this->products = $res['data'];
		$this->total = $res['total'];
		
		//移库单信息
		$this->ssinfo = array();
		if ($this->ssid)
		{
			$this->ssinfo = Warehouse_Api::getStockShiftInfo($this->ssid);
			$this->orderProduct = Tool_Array::list2Map($this->ssinfo['products'], 'sid');
			unset($this->ssinfo['products']);
		}
	}
	
	protected function outputBody()
	{
		$cate3list = empty(Conf_Sku::$CATE3[$this->conf['cate2']]) ? array():Conf_Sku::$CATE3[$this->conf['cate2']];
		$this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
		$this->smarty->assign('cate2_list', Conf_Sku::$CATE2[$this->conf['cate1']]);
		$this->smarty->assign('cate3_list', $cate3list);
		$this->smarty->assign('search_conf', $this->conf);
		$this->smarty->assign('brands', $this->brands);
		$this->smarty->assign('models', $this->models);
		$this->smarty->assign('type', $this->type);

		
		$this->smarty->assign('search_products', $this->products);
		$this->smarty->assign('keyword', $this->keyword);
		$this->smarty->assign('order_info', $this->ssinfo);
		$this->smarty->assign('order_products', $this->orderProduct);
		
		$html = $this->smarty->fetch('warehouse/block_stock_shift.html');
		
		$result = array('html' => $html);
		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pub');
$app->run();