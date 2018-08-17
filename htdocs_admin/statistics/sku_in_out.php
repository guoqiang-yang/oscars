<?php

include_once('../../global.php');

/**
 * sku 进销存基本信息
 */
class App extends App_Admin_Page
{
    const TAB_CATE1 = 'cate1', TAB_CATE2 = 'cate2', TAB_BRAND = 'brand', TAB_SKU = 'sku';
    const DATE_MODE_TODAY = 'today', DATE_MODE_OLD_DAY = 'old';

    private $tab;
    private $dateMode;
    private $start;
    private $orderBy;
    private $fromDate;
    private $endDate;
    private $city;
    private $wid;
    private $unit;
    private $cate1;
    private $cate2;
    private $sid;
    private $brandName;
    private $keyword;
    private $list;
    private $num = 20;
    private $total;

    protected function getPara()
    {
        $this->tab = Tool_Input::clean('r', 'tab', TYPE_STR);
        $this->dateMode = Tool_Input::clean('r', 'date_mode', TYPE_STR);
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->cate1 = Tool_Input::clean('r', 'cate1', TYPE_UINT);
        $this->cate2 = Tool_Input::clean('r', 'cate2', TYPE_UINT);
        $this->city = Tool_Input::clean('r', 'city', TYPE_UINT);
        $this->unit = Tool_Input::clean('r', 'unit', TYPE_STR);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->brandName = Tool_Input::clean('r', 'brand_name', TYPE_STR);
        $this->keyword = Tool_Input::clean('r', 'keyword', TYPE_STR);
        $this->orderBy = Tool_Input::clean('r', 'order_by', TYPE_STR);
        $this->fromDate = Tool_Input::clean('r', 'from_date', TYPE_STR);
        $this->endDate = Tool_Input::clean('r', 'end_date', TYPE_STR);
    }

    protected function checkPara()
    {
        if (empty($this->dateMode))
        {
            $this->dateMode = self::DATE_MODE_TODAY;
        }

        // 当日数据
        if ($this->dateMode == self::DATE_MODE_TODAY)
        {
            if (!isset($_REQUEST['cate1'])) $this->cate1 = 5;
            $city = current(array_keys($this->getAllowedCities4User()));
            if (empty($this->wid) && empty($this->city)){
                $this->wid = current($this->getAllAllowedWids4User($city, true));
            }
            if (empty($this->city)) $this->city = $city;
            if (empty($this->unit)) $this->unit = 'num';
        }
        // 以往数据
        else
        {
            if (empty($this->tab))
            {
                $this->tab = self::TAB_SKU;
                $this->unit = 'num';
                $this->orderBy = 'sales_amount';
            }
            if (empty($this->fromDate))
            {
                $this->fromDate = date("Y-m-d", strtotime('-1 day'));
                $this->endDate = date("Y-m-d", strtotime('-1 day'));
            }

            //第一次点击"以往数据 - sku明细"标签
            if ($this->tab==self::TAB_SKU && empty($this->cate1)
                && empty($this->wid) && empty($this->city)
                && empty($this->cate2) && empty($this->brandName) && empty($this->keyword))
            {
                $this->cate1 = 5;
                $this->city = current(array_keys($this->getAllowedCities4User()));
                $this->wid = current($this->getAllAllowedWids4User($this->city, true));
            }
        }

        //如果选了城市没选仓库,置为所有仓库
        if(empty($this->city) && empty($this->wid))
        {
            $this->wid = $this->getAllAllowedWids4User(0, true);
        }elseif (empty($this->wid))
        {
            $this->wid = $this->getAllAllowedWids4User($this->city, true);
        }
    }

    protected function main()
    {
        if ($this->dateMode == self::DATE_MODE_TODAY)
        {
            $conf = array(
                'cate1' => $this->cate1,
                'cate2' => $this->cate2,
                'brand_name' => $this->brandName,
                'keyword' => $this->keyword,
                'sid' => $this->sid
            );
            $day = date('Y-m-d');
            $this->list = Statistics_Api::getSkuDailyReportDirect($this->wid, $day, $conf, $this->start, $this->num, $this->orderBy);
            if (is_numeric($this->wid))
            {
                $this->_appendSecurityStock( $this->list, $this->wid );
            }
        }
        else
        {
            switch ($this->tab)
            {
                case self::TAB_CATE1:
                    $this->list = Statistics_Api::getCate1SkuAbstractBetweenDate($this->fromDate, $this->endDate, $this->wid, $this->orderBy);
                    break;
                case self::TAB_CATE2:
                    $this->list = Statistics_Api::getCate2SkuAbstractBetweenDate($this->fromDate, $this->endDate, $this->cate1, $this->wid, $this->orderBy);
                    break;
                case self::TAB_BRAND:
                    $this->list = Statistics_Api::getBrandSkuAbstractBetweenDate($this->fromDate, $this->endDate, $this->wid, $this->start, $this->num, $this->orderBy);
                    break;
                case self::TAB_SKU:
                    $conf = array(
                        'wid' => $this->wid,
                        'cate1' => $this->cate1,
                        'cate2' => $this->cate2,
                        'brand_name' => $this->brandName,
                        'keyword' => $this->keyword,
                        'sid' => $this->sid
                    );
                    $this->list = Statistics_Api::getSkuAbstractBetweenDate($this->fromDate, $this->endDate, $conf, $this->start, $this->num, $this->orderBy);
                    if (is_numeric($this->wid))
                    {
                        $this->_appendSecurityStock( $this->list, $this->wid );
                    }
                    break;
            }
        }
    }

    private function _appendSecurityStock( &$skuList, $wid )
    {
        $sids = Tool_Array::getFields($skuList,'id');
        $stocks = Warehouse_Security_Stock_Api::getSecurityStock($wid, $sids);
        $stocks = Tool_Array::list2Map($stocks, 'sid');
        foreach ($skuList as &$skuInfo)
        {
            //平均销售量*调整系数*（货期+最小库存天数）
            $sid = $skuInfo['id'];
            $skuInfo['order_point'] = empty($stocks[$sid]) ? 0: $stocks[$sid]['order_point'];
            $skuInfo['security_cal_formula'] = sprintf('( %d * %0.2f * ( %d + %d ) )', 
                    $stocks[$sid]['ave_sale_num'], $stocks[$sid]['season_factor'], $stocks[$sid]['delivery_day'], Conf_Stock::MIN_DAY_OF_STOCK);
        }
    }

    protected function outputBody()
    {
        if(is_array($this->wid))
        {
            $this->wid = 0;
        }
        $app = '/statistics/sku_in_out.php?wid=' . $this->wid . '&from_date=' . $this->fromDate . '&end_date=' . $this->endDate . '&tab=' . $this->tab . '&cate1=' . $this->cate1 . '&cate2=' . $this->cate2 . '&brand_name=' . $this->brandName;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $daysNum = round((strtotime($this->endDate) - strtotime($this->fromDate))/86400)+1;

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('data_list', $this->list);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('tab', $this->tab);
        $this->smarty->assign('date_mode', $this->dateMode);
        $this->smarty->assign('cur_city', $this->city);
        $this->smarty->assign('cur_wid', is_array($this->wid)?0:$this->wid);
        $this->smarty->assign('unit', $this->unit);
        $this->smarty->assign('cate1', $this->cate1);
        $this->smarty->assign('cate2', $this->cate2);
        $this->smarty->assign('brand_name', $this->brandName);
        $this->smarty->assign('sid', $this->sid);
        $this->smarty->assign('keyword', $this->keyword);
        $this->smarty->assign('from_date', $this->fromDate);
        $this->smarty->assign('end_date', $this->endDate);
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('days_num', $daysNum);
        $this->smarty->assign('cities_json', json_encode($this->getAllowedCities4User()));
        $this->_printWarehouseJosn();
        $this->_printCate2Josn();
        $this->smarty->display('statistics/sku_in_out.html');
    }

    private function _printWarehouseJosn()
    {
        $result = array();
        $wids = $this->getAllAllowedWids4User(0, true);
        foreach (Conf_Warehouse::$WAREHOUSE_CITY as $cityId => $warehouses)
        {
            $result[$cityId] = array();
            foreach ($warehouses as $wid)
            {
                if(in_array($wid, $wids)) {
                    $result[$cityId][$wid] = Conf_Warehouse::$WAREHOUSES[$wid];
                }
            }
        }
        $this->smarty->assign('warehouses_json', json_encode($result));
    }

    private function _printCate2Josn()
    {
        $result = array();
        foreach (Conf_Sku::$CATE2 as $cate1 => $cate2Map)
        {
            $result[$cate1] = array();
            foreach ($cate2Map as $cate2=>$item)
            {
                $result[$cate1][$cate2] = $item['name'];
            }
        }
        $this->smarty->assign('cate2_json', json_encode($result));
    }
}

$app = new App('pri');
$app->run();
