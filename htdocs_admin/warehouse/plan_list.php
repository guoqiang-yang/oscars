<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $searchConf;
    private $plan;
    private $start = 0;
    private $num = 20;
    private $total;
    private $staffList;

    protected function getPara()
    {
        $this->searchConf = array(
            'start_time' => Tool_Input::clean('r', 'start_time', TYPE_STR),
            'end_time' => Tool_Input::clean('r', 'end_time', TYPE_STR),
            'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
            'plan_type' => Tool_Input::clean('r', 'plan_type', TYPE_UINT),
//            'step' => Tool_Input::clean('r', 'step', TYPE_UINT),
        );
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
    }

    protected function checkPara()
    {
        $curCity = City_Api::getCity();
        if (empty($this->searchConf['wid']))
        {
            if (empty($this->_user['city_wid_map'][$curCity['city_id']]))
            {
                $this->searchConf['wid'] = -1;
            }
            else
            {
                $this->searchConf['wid'] = $this->_user['city_wid_map'][$curCity['city_id']];
            }
        }
    }

    protected function main()
    {
        $planList = Warehouse_Api::getInventoryPlanList($this->searchConf, '', $this->start, $this->num);
        $suids = Tool_Array::getFields($planList['list'], 'suid');
        $this->plan = $planList['list'];
        $this->total = $planList['total'];
        if (!empty($suids))
        {
            $this->staffList = Tool_Array::list2Map(Admin_Api::getStaffs($suids), 'suid', 'name');
        }
        else
        {
            $this->staffList = array();
        }
        $this->addFootJs(array('js/apps/stock.js'));
    }

    protected function outputBody()
    {
        if (is_array($this->searchConf['wid']))
        {
            unset($this->searchConf['wid']);
        }
        $app = '/warehouse/plan_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('plan_list', $this->plan);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('plan_types', Conf_Stock::$STOCKTAKING_PLANS);
        $this->smarty->assign('step_list', Conf_Stock::$STOCKTAKING_PLAN_STEPS);
        $this->smarty->assign('method_list', Conf_Stock::$STOCKTAKING_METHODS);
        $this->smarty->assign('attr_list', Conf_Stock::$STOCKTAKING_ATTRIBUTES);
        $this->smarty->assign('times', array_keys(Conf_Stock::$STOCKTAKING_TIMES));
        $this->smarty->assign('types', Conf_Stock::$STOCKTAKING_TYPES);
        $this->smarty->assign('staff_list', $this->staffList);
        $this->smarty->display('warehouse/plan_list.html');
    }
}

$app = new App();
$app->run();