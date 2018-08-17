<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
	private $search;
	private $locationList;
	private $total;
    
    private $city;

	protected function getPara()
	{
        $this->city = City_Api::getCity();
        $dfWid = Conf_Warehouse::$WAREHOUSE_CITY[$this->city['city_id']][0];
		$wid = $this->getWarehouseId();
        
        $this->search = array(
			'wid' => !empty($wid) ? $wid : $dfWid,
			'lstart' => strtoupper(Tool_Input::clean('r', 'lstart', TYPE_STR)), //按货区筛选
			'lend' => Tool_Input::clean('r', 'lend', TYPE_STR),          //按货架筛选
		);
	}

	protected function main()
	{
		header("Content-type:text/csv");
		header("Content-Disposition:attachment;filename=" . 'stock-' . date('Ymd') . '.csv');
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');

		$head = array(
			'行号',
			'sid',
			'sku名称',
			'单位',
            '品牌',
            '分类1',
            '分类2',
			'货位',
            '售价(元)',
			'系统库存',
			'盘点数量',
		);
		Data_Csv::send($head);

        $sb = new Shop_Brand();
        $brands = $sb->getAllBrands();

        //$today = date("Y-m-d");
        //$changeSids = $this->getStockHistorySidOfDay($this->search['wid'], $today);

        $sp = new Shop_Product();
        $where = 'city_id='. $this->city['city_id'];
        $salesPrices = Tool_Array::list2Map($sp->getListByWhere($where), 'sid', 'price');
        
		$start = 0;
		$step = 1000;
		$i = 1;
		do
		{
			$data = Warehouse_Location_Api::exportLocation($this->search, $start, $step);
			$this->locationList = $data['list'];
            
			foreach ($this->locationList as $location)
			{
				$sid = $location['sid'];

                //if (!in_array($sid, $changeSids)) continue;

				$title = $location['_skuInfo']['title'];
                $bid = $location['_skuInfo']['bid'];
                $cate1 = $location['_skuInfo']['cate1'];
                $cate2 = $location['_skuInfo']['cate2'];
                $cate2 = Conf_Sku::$CATE2[$cate1][$cate2]['name'];
                $cate1 = Conf_Sku::$CATE1[$cate1]['name'];
                $brand = $brands[$bid]['name'];
				$loc = $location['location'];
                $salesPrice = isset($salesPrices[$sid])? ($salesPrices[$sid]/100):0;
				$num = $location['num'];

                $title = str_replace('ø', 'Φ', $title);
                $title = str_replace('º', ' ', $title);
                $title = str_replace('²', '2', $title);
                
				$arr = array(
					$i++,
					$sid,
					$title,
					$location['_skuInfo']['unit'],
                    $brand,
                    $cate1,
                    $cate2,
					$loc,
                    $salesPrice,
					$num,
				);
				Data_Csv::send($arr);
			}

			$start += $step;
		}
		while (count($this->locationList) > 0);
	}

    private function getStockHistorySidOfDay($wid, $day)
    {
        $one = Data_One::getInstance();
        $where = sprintf('status=0 and wid=%d and ctime>="%s 00:00:00" and ctime<="%s 23:59:59"', $wid, $day, $day);
        $res = $one->select('t_stock_history', array('sid', 'num', 'type'), $where);
        $list = $res['data'];
        $sids = Tool_Array::getFields($list, 'sid');
        $sids = array_unique(array_filter(array_map('intval',$sids)));
        return $sids;
    }

	protected function outputHead()
	{

	}

	protected function outputBody()
	{

	}

	protected function outputTail()
	{

	}
}

$app = new App('pri');
$app->run();

