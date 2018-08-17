<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $num = 20;
    private $cuid;
    private $searchConf;
    private $start;
    private $billList = array();

    protected function getPara()
    {
        $this->cuid = Tool_Input::clean('r', 'cuid', TYPE_UINT);
        $this->searchConf = array(
            'paid' => 0,
            'user_type' => Tool_Input::clean('r', 'user_type', TYPE_UINT),
            'btime' => Tool_Input::clean('r', 'btime', TYPE_STR),
            'etime' => Tool_Input::clean('r', 'etime', TYPE_STR),

            'cinfo' => Tool_Input::clean('r', 'cinfo', TYPE_STR),
        );
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
    }

    protected function main()
    {
        if (empty($this->cuid) && !empty($this->searchConf['cinfo']))
        {
            // 根据条件查找司机/搬运工
            $cuids = array_unique($this->_getCoopworders());

            if (!empty($cuids))
            {
                $this->searchConf['cuid'] = $cuids;
                $this->billList = Finance_Api::coopworkerWillpayList($cuids, $this->searchConf, $this->start, $this->num);
            }
        }
        else
        {
            $this->billList = Finance_Api::coopworkerWillpayList($this->cuid, $this->searchConf, $this->start, $this->num);
        }
    }

    protected function outputBody()
    {
        $app = '/finance/coopworker_willpay_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->billList['total'], $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('total', $this->billList['total']);
        $this->smarty->assign('bill_list', $this->billList['data']);
        $this->smarty->assign('warehouse_list', Conf_Warehouse::$WAREHOUSES);
        $this->smarty->assign('payment_type_list', Conf_Base::getPaymentTypes());
        $this->smarty->assign('coopworder_types', Conf_Base::getCoopworkerTypes());
        $this->smarty->assign('fee_types', Conf_Coopworker::$Coopworker_Fee_Types);

        $this->smarty->display('finance/coopworker_willpay_list.html');
    }

    private function _getCoopworders()
    {
        $cuids = array();

        if (is_numeric($this->searchConf['cinfo']))
        {
            $search['mobile'] = $this->searchConf['cinfo'];
        }
        else
        {
            $search['name'] = $this->searchConf['cinfo'];
        }

        $coopworkers = Logistics_Api::getCoopworkerInfo($search, $this->searchConf['user_type']);

        if (isset($coopworkers['driver']))
        {
            foreach ($coopworkers['driver'] as $oner)
            {
                $cuids[] = $oner['did'];
            }
        }

        if (isset($coopworkers['carrier']))
        {
            foreach ($coopworkers['carrier'] as $oner)
            {
                $cuids[] = $oner['cid'];
            }
        }

        return $cuids;
    }
}

$app = new App();
$app->run();