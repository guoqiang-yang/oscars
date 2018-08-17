<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $startTime;
    private $endTime;
    private $warehouseList;
    private $cateList;
    private $type;

    protected function getPara()
    {
        $this->type = Tool_Input::clean('r', 'type', TYPE_UINT);
        $this->startTime = Tool_Input::clean('r', 'start_time', TYPE_STR);
        $this->endTime = Tool_Input::clean('r', 'end_time', TYPE_STR);
    }

    protected function checkPara()
    {
        if (empty($this->endTime))
        {
            if (date('d') == '01')
            {
                $this->endTime = date("Y-m-t", strtotime('-1 day'));
            }
            else
            {
                $this->endTime = date("Y-m-d", strtotime('-1 day'));
            }
        }

        if (empty($this->startTime))
        {
            if (date('d') == '01')
            {
                $this->startTime = date("Y-m-01", strtotime('-1 day'));
            }
            else
            {
                $this->startTime = date("Y-m-01", strtotime('-1 day'));
            }
        }
        if ($this->_getMonthNum($this->startTime, $this->endTime) > 5)
        {
            list($year,$month,$day) = explode('-', $this->endTime);
            if($month-6>0){
                $this->startTime = $year.'-'.str_pad($month-6,2,"0",STR_PAD_LEFT).'-01';
            }else{
                $this->startTime = ($year-1).'-'.str_pad(6+$month,2,"0",STR_PAD_LEFT).'-01';
            }
        }
    }

    protected function main()
    {
        $ssv = new Statistics_Sku_View();
        $skuInfos = Shop_Api::getAllSku();
        $skuIds = array();
        $brandIds = array();
        //初始化数据
        $warehouseInfos = Conf_Warehouse::getWarehouses();
        foreach ($warehouseInfos as $wid => $item)
        {
            $this->warehouseList[$wid]['name'] = $item;
            $this->warehouseList[$wid]['order_amount'] = 0;
            $this->warehouseList[$wid]['refund_amount'] = 0;
            $this->warehouseList[$wid]['order_cost_amount'] = 0;
            $this->warehouseList[$wid]['refund_cost_amount'] = 0;
            $this->warehouseList[$wid]['profit_amount'] = 0;
            $this->warehouseList[$wid]['profit_rate'] = 0;
        }
        $cate1Infos = Conf_Sku::$CATE1;
        foreach ($cate1Infos as $cate_id => $item)
        {
            $this->cateList[$cate_id]['name'] = $item['name'];
            $this->cateList[$cate_id]['order_amount'] = 0;
            $this->cateList[$cate_id]['refund_amount'] = 0;
            $this->cateList[$cate_id]['order_cost_amount'] = 0;
            $this->cateList[$cate_id]['refund_cost_amount'] = 0;
            $this->cateList[$cate_id]['profit_amount'] = 0;
            $this->cateList[$cate_id]['profit_rate'] = 0;
            $this->cateList[$cate_id]['sku_num'] = 0;
            $this->cateList[$cate_id]['brand_num'] = 0;
        }

        $list = $ssv->getAmountStatisticsBetweenDate($this->startTime, $this->endTime, $this->type, 'wid');
        if(!empty($list))
        {
            foreach ($list as $item)
            {
                $wid = $item['wid'];
                $this->warehouseList[$wid]['order_amount'] = floor($item['order_amount']);
                $this->warehouseList[$wid]['refund_amount'] = $item['refund_amount'] ? floor($item['refund_amount']) : 0;
                $this->warehouseList[$wid]['order_cost_amount'] = floor($item['order_cost_amount']);
                $this->warehouseList[$wid]['refund_cost_amount'] = floor($item['refund_cost_amount']);
                $item['profit_amount'] = $item['order_amount'] - $item['refund_amount'] - $item['order_cost_amount'] + $item['refund_cost_amount'];
                $this->warehouseList[$wid]['profit_amount'] = floor($item['profit_amount']);
                $this->warehouseList[$wid]['profit_rate'] = round($item['profit_amount'] * 100 / ($item['order_amount'] - $item['refund_amount']), 2);
            }
        }

        $list = $ssv->getAmountStatisticsBetweenDate($this->startTime, $this->endTime, $this->type, 'sid');
        if(!empty($list))
        {
            foreach ($list as $item) {
                $skuInfo = $skuInfos[$item['sid']];
                $cate_id = $skuInfo['cate1'];
                $this->cateList[$cate_id]['order_amount'] += $item['order_amount'];
                $this->cateList[$cate_id]['refund_amount'] += $item['refund_amount'];
                $this->cateList[$cate_id]['order_cost_amount'] += $item['order_cost_amount'];
                $this->cateList[$cate_id]['refund_cost_amount'] += $item['refund_cost_amount'];

                if(!in_array($item['sid'], $skuIds[$cate_id]))
                {
                    $skuIds[$cate_id][] = $item['sid'];
                }
                if(!in_array($skuInfo['bid'], $brandIds[$cate_id]))
                {
                    $brandIds[$cate_id][] = $skuInfo['bid'];
                }
            }
        }

        foreach ($this->cateList as $cate_id => $item)
        {
            $this->cateList[$cate_id]['order_amount'] = floor($item['order_amount']);
            $this->cateList[$cate_id]['refund_amount'] = floor($item['refund_amount']);
            $this->cateList[$cate_id]['order_cost_amount'] = floor($item['order_cost_amount']);
            $this->cateList[$cate_id]['refund_cost_amount'] = floor($item['refund_cost_amount']);
            $this->cateList[$cate_id]['profit_amount'] = floor($item['profit_amount']);
            $item['profit_amount'] = $item['order_amount'] - $item['refund_amount'] - $item['order_cost_amount'] + $item['refund_cost_amount'];
            $this->cateList[$cate_id]['profit_amount'] = floor($item['profit_amount']);
            $this->cateList[$cate_id]['profit_rate'] = round($item['profit_amount'] * 100 / ($item['order_amount'] - $item['refund_amount']));
            $this->cateList[$cate_id]['sku_num'] = count($skuIds[$cate_id]);
            $this->cateList[$cate_id]['brand_num'] = count($brandIds[$cate_id]);
        }
    }

    private function _getMonthNum($start, $end, $tags='-')
    {
        $date1 = explode($tags, $start);
        $date2 = explode($tags, $end);
        return abs(($date1[0]-$date2[0])*12 + $date1[1] - $date2[1]);
    }

    protected function outputBody()
    {
        $this->smarty->assign('type', $this->type);
        $this->smarty->assign('start_time', $this->startTime);
        $this->smarty->assign('end_time', $this->endTime);
        $this->smarty->assign('warehouse_list', $this->warehouseList);
        $this->smarty->assign('cate_list', $this->cateList);

        $this->smarty->display('statistics/products_statistics.html');
    }
}

$app = new App('pri');
$app->run();
