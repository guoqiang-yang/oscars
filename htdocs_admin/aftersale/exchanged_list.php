<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    // cgi参数
    private $start;
    private $mobile;
    private $searchConf;
    // 中间结果
    private $exchangedList;
    private $num = 20;
    private $total;

    protected function checkAuth()
    {
        parent::checkAuth('/order/exchanged_list');
    }

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->mobile = Tool_Input::clean('r', 'mobile', TYPE_STR);
        $this->searchConf = array(
            'eid' => Tool_Input::clean('r', 'eid', TYPE_UINT),
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
            'exchanged_date' => Tool_Input::clean('r', 'exchanged_date', TYPE_STR),
            'exchanged_date_end' => Tool_Input::clean('r', 'exchanged_date_end', TYPE_STR),
            'wid' => $this->getWarehouseId(),
        );

        if (isset($_REQUEST['step']))
        {
            $this->searchConf['step'] = Tool_Input::clean('r', 'step', TYPE_UINT);
        }
        $cityInfo = City_Api::getCity();
        $this->searchConf['city_id'] = $cityInfo['city_id'];
    }

    protected function main()
    {
        if (!empty($this->mobile))
        {
            $c = Crm2_Api::getByMobile($this->mobile);
            if (!empty($c))
            {
                $this->searchConf['cid'] = $c['cid'];
            }
        }

        $res = Exchanged_Api::getExchangedList($this->searchConf, $this->start, $this->num);

        $this->exchangedList = $res['list'];
        $this->total = $res['total'];
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $app = '/aftersale/exchanged_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('mobile', $this->mobile);
        $this->smarty->assign('searchConf', $this->searchConf);
        $this->smarty->assign('exchanged_steps', Conf_Exchanged::getExchangedStepNames());
        $this->smarty->assign('exchanged_list', $this->exchangedList);
        $this->smarty->assign('_warehouseList', Conf_Warehouse::getWarehousesOfCity($this->searchConf['city_id']));
        unset($this->searchConf['step']);
        $this->searchConf['mobile'] = $this->mobile;
        $step_url = '/aftersale/exchanged_list.php?' . http_build_query($this->searchConf);
        $this->smarty->assign('step_url', $step_url);
        $this->smarty->display('order/exchanged_list.html');
    }
}

$app = new App('pri');
$app->run();

