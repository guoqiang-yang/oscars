<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $data;
    private $startTime;
    private $endTime;
    private $cityId;
    private $old = false;
    private $warehouseList;
    private $wid;

    protected function getPara()
    {
        $this->startTime = Tool_Input::clean('r', 'start_time', TYPE_STR);
        $this->endTime = Tool_Input::clean('r', 'end_time', TYPE_STR);
        $this->cityId = Tool_Input::clean('r', 'city_id', TYPE_STR);
        $this->old = Tool_Input::clean('r', 'old', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
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

        if (empty($this->cityId) && empty($this->wid))
        {
            $this->wid = $this->getAllAllowedWids4User(0, true);
        }elseif(empty($this->wid))
        {
            $this->wid = $this->getAllAllowedWids4User($this->cityId, true);
        }
    }

    protected function main()
    {
        $this->addHeadJs(array());
        $this->addFootJs(array());

        if ($this->old)
        {
            $this->data = Statistics_Api::getAmount2($this->startTime, $this->endTime, $this->cityId, $this->wid);
        }
        else
        {
            $this->data = Statistics_Api::getAmount($this->startTime, $this->endTime, $this->cityId, $this->wid);
        }
        $wids = $this->getAllAllowedWids4User(0, true);
        $warehouses = Conf_Warehouse::$WAREHOUSES;
        foreach ($warehouses as $wid => $warehouse)
        {
            if(in_array($wid, $wids))
            {
                $this->warehouseList[$wid] = array(
                    'city' => Conf_Warehouse::getCityByWarehouse($wid),
                    'wname' => $warehouse,
                );
            }
        }
    }

    protected function outputBody()
    {
        $this->smarty->assign('start_time', $this->startTime);
        $this->smarty->assign('end_time', $this->endTime);
        $this->smarty->assign('data', $this->data);
        $this->smarty->assign('city_id', $this->cityId);
        $this->smarty->assign('city_list', $this->getAllowedCities4User());
        $this->smarty->assign('yesterday', date('Y-m-d', strtotime('yesterday')));
        $this->smarty->assign('warehouse_list', $this->warehouseList);
        $this->smarty->assign('cur_wid', $this->wid);

        $this->smarty->display('statistics/count.html');
    }
}

$app = new App('pri');
$app->run();
