<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $fromDate;
    private $endDate;
    private $start;
    private $list;
    private $num = 20;
    private $total;
    private $wid;
    private $action;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->fromDate = Tool_Input::clean('r', 'from_date', TYPE_STR);
        $this->endDate = Tool_Input::clean('r', 'end_date', TYPE_STR);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
        $this->action = Tool_Input::clean('r', 'action', TYPE_STR);
    }

    protected function checkPara()
    {
        if (empty($this->fromDate))
        {
            if (date('d') == '01')
            {
                $this->fromDate = date("Y-m-01", strtotime('-1 day'));
            }
            else
            {
                $this->fromDate = date("Y-m-01");
            }
        }
        if (empty($this->endDate))
        {
            if (date('d') == '01')
            {
                $this->endDate = date("Y-m-t", strtotime('-1 day'));
            }
            else
            {
                $this->endDate = date("Y-m-d", strtotime('-1 day'));
            }
        }
    }

    protected function main()
    {
        if ($this->action == 'download')
        {
            $ext['wid'] = $this->wid;
            Statistics_Api::exportForWeb(Conf_Statics::TYPE_WAREHOUSE_COST, $this->fromDate, $this->endDate, $ext);
            exit;
        }
        $data = Statistics_Api::getBaseInfoBetweenDateByWid($this->wid, $this->fromDate, $this->endDate, $this->start, $this->num);
        $this->list = $data['list'];
        $this->total = $data['total'];

        if (!empty($this->list))
        {
            foreach ($this->list as &$item)
            {
                $item['wname'] = Conf_Warehouse::$WAREHOUSES[$item['wid']];
            }
        }
    }

    protected function outputBody()
    {
        $app = '/statistics/logistics_fee_by_wid.php?from_date=' . $this->fromDate . '&end_date=' . $this->endDate;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('from_date', $this->fromDate);
        $this->smarty->assign('end_date', $this->endDate);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('wid', $this->wid);
        $this->smarty->assign('warehouses', Conf_Warehouse::getWarehouseByAttr('customer'));
        $this->smarty->assign('yesterday', date('Y-m-d', strtotime('yesterday')));

        $this->smarty->display('statistics/logistics_fee_by_wid.html');
    }
}

$app = new App('pri');
$app->run();
