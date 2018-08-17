<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
	private $pid;

	protected function getPara()
	{
		$this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
	}

	protected function main()
	{
        $plan = Warehouse_Api::getInventoryPlan($this->pid);
        if (empty($plan))
        {
            throw new Exception('盘点计划不存在!');
        }
        if ($plan['step'] != Conf_Stock::STOCKTAKING_PLAN_STEP_FINISHED)
        {
            throw new Exception('盘点计划未完成!');
        }

		header("Content-type:text/csv");
		header("Content-Disposition:attachment;filename=" . '盘点报告(PID：' . $this->pid . ')-' . date('Ymd') . '.csv');
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');

	    //在库商品总金额
	    $total_amount = 0;
	    $wl = new Warehouse_Location();
	    $allStock = $wl->search(array('wid'=> $plan['wid']), 0, 0, array('*'));
	    $allSids = Tool_Array::getFields($allStock['list'], 'sid');
	    $cost = Shop_Api::getCostOfSkusWarehouse($plan['wid'], $allSids);

        foreach ($allStock['list'] as $_stock)
        {
            $total_amount += $_stock['num'] * $cost[$_stock['sid']];
	    }

		$report_head = array(
			'城市',
			'库房',
			'计划类型',
			'sku数量',
			'货位商品数',
			'库存总额',
			'任务行数',
			'初盘差异',
			'初盘差异率',
			'复盘差异',
			'复盘差异率',
			'三盘差异',
			'三盘差异率',
			'盘盈sku数量',
			'盘盈件数',
			'盘盈金额',
			'盘亏sku数量',
			'盘亏件数',
			'盘亏金额',
			'盘盈库率（绝对值）',
			'盘盈库率（净值）',
		);
		Data_Csv::send($report_head);

		$wit = new Warehouse_Inventory_Task();
        $taskList = $wit->getList(array('plan_id' => $this->pid), $taskTotal, '');

        if (empty($taskList))
        {
            throw new Exception('该计划没有生成盘点任务');
        }

        $wips = new Warehouse_Inventory_Products();
        $products = $wips->getList(array('pid' => $this->pid), 0, 0);
        $sids = array_unique(Tool_Array::getFields($products['list'], 'sid'));
        $ss = new Shop_Sku();
        $skuInfo = $ss->getBulk($sids);

        if (empty($products))
        {
            throw new Exception('该计划还未确认盘点商品！');
        }

        $endNum = '';
        $is_picked = '';
        switch ($plan['times'])
        {
            case Conf_Stock::STOCKTAKING_TIME_FIRST:
                $endNum = 'first_num';
                $is_picked = 'is_picked1';
                break;
            case Conf_Stock::STOCKTAKING_TIME_SECOND:
                $endNum = 'second_num';
                $is_picked = 'is_picked2';
                break;
            case Conf_Stock::STOCKTAKING_TIME_THIRD:
                $endNum = 'third_num';
                $is_picked = 'is_picked3';
                break;
        }

        $more = 0;
        $less = 0;
        $moreSku = array();
        $lessSku = array();
        $moreCost = 0;
        $lessCost = 0;
        foreach ($products['list'] as &$_product)
        {
            $diff = $_product[$endNum] - $_product['num'];
            if ($_product[$is_picked] && $diff > 0)
            {
                $more += 1;
                $moreSku[] = $_product['sid'];
                $moreCost += $diff * $cost[$_product['sid']];
            }
            if ($_product[$is_picked] && $diff < 0)
            {
                $less += 1;
                $lessSku[] = $_product['sid'];
                $lessCost += abs($diff * $cost[$_product['sid']]);
            }
        }

        $firstDiffNum = 0;
        $secondDiffNum = 0;
        $thirdDiffNum = 0;
        foreach ($taskList as $_task)
        {
            if ($_task['times'] == Conf_Stock::STOCKTAKING_TIME_FIRST)
            {
                $firstDiffNum += $_task['diff_num'];
            }
            elseif ($_task['times'] == Conf_Stock::STOCKTAKING_TIME_SECOND)
            {
                $secondDiffNum += $_task['diff_num'];
            }
            elseif ($_task['times'] == Conf_Stock::STOCKTAKING_TIME_THIRD)
            {
                $thirdDiffNum += $_task['diff_num'];
            }
        }

		$report_body = array(
            Conf_City::$CITY[Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$plan['wid']]],
		    Conf_Warehouse::$WAREHOUSES[$plan['wid']],
		    Conf_Stock::$STOCKTAKING_TYPES[$plan['plan_type']],
            count($sids),
            $products['total'],
            $total_amount / 10 / 10 . '元',
            $taskTotal,
            $firstDiffNum,
            round($firstDiffNum / $products['total'], 2) * 100 . '%',
            $secondDiffNum,
		    round($secondDiffNum / $firstDiffNum, 2) * 100 . '%',
            $thirdDiffNum,
            round($thirdDiffNum / $secondDiffNum, 2) * 100 . '%',
		    count(array_unique($moreSku)),
		    $more,
		    $moreCost / 10 / 10 . '元',
		    count(array_unique($lessSku)),
		    $less,
		    $lessCost / 10 / 10 . '元',
            round(($moreCost + $lessCost) / $total_amount * 100, 4) . '%',
            round(($moreCost - $lessCost) / $total_amount * 100, 4) . '%',
        );
        Data_Csv::send($report_body);

        Data_Csv::send(array());

		$detail_head = array(
            '区域',
            '储位编码',
            '商品编码',
            '一级分类',
            '二级分类',
            '品牌',
            '单位',
            '库存',
            '初盘任务号',
            '初盘盘点量',
            '初盘差异量',
            '复盘任务号',
            '复盘盘点量',
            '复盘差异量',
            '三盘任务号',
            '三盘盘点量',
            '三盘差异量',
            '备注',
        );
		Data_Csv::send($detail_head);

		$bids = Tool_Array::getFields($skuInfo, 'bid');
		$brandInfo = Shop_Api::getBrandByIds($bids);
		$cate1List = Conf_Sku::$CATE1;
		$cate2List = Conf_Sku::$CATE2;

		foreach ($products['list'] as $product)
        {
            $sid = $product['sid'];
            $cate1 = $skuInfo[$sid]['cate1'];
            $cate2 = $skuInfo[$sid]['cate2'];
            $bid = $skuInfo[$sid]['bid'];
            $num = $product['num'];
            $firstNum = $product['first_num'];
            $secondNum = $product['second_num'];
            $thirdNum = $product['third_num'];

            $detail_body = array(
                $product['location'],
                substr($product['location'] ,0 ,1),
                $product['sid'],
                $cate1List[$cate1]['name'],
                $cate2List[$cate1][$cate2]['name'],
                $brandInfo[$bid]['name'],
                $skuInfo[$sid]['unit'],
                $num,
                $plan['times'] >= 1? $product['task_id1'] : '-',
                $product['is_picked1'] == 1? $firstNum : '-',
                $product['is_picked1'] == 1? $firstNum - $num : '-',
                $plan['times'] >= 2? $product['task_id2'] : '-',
                $product['is_picked2'] == 1? $secondNum : '-',
                $product['is_picked2'] == 1? $secondNum - $num : '-',
                $plan['times'] == 3? $product['task_id3'] : '-',
                $product['is_picked3'] == 1? $thirdNum : '-',
                $product['is_picked3'] == 1? $thirdNum - $num : '-',
                $product['note'],
            );
            Data_Csv::send($detail_body);
        }
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

$app = new App();
$app->run();

