<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $statementInfo;
    private $driverInfos;
    private $paidStatus;
    private $staffList;
    private $statementStepList;
    private $searchConf;
    private $carrierInfos;
    private $wid;

    protected function checkAuth()
    {
        parent::checkAuth('/order/driver_order_statement');
    }

    protected function getPara()
    {
        $step = Tool_Input::clean('r', 'step', TYPE_UINT);
        $this->wid = $this->getWarehouseId();
        $this->searchConf = array(
            'wid' => $this->wid,
            'id' => Tool_Input::clean('r', 'id', TYPE_UINT),
            'step' => (empty($step) ? (Conf_Coopworker::STATEMENT_STEP_CREATE) : $step),
            'cuid' => Tool_Input::clean('r', 'cuid', TYPE_UINT),
            'start_ctime' => Tool_Input::clean('r', 'start_ctime', TYPE_STR),
            'end_ctime' => Tool_Input::clean('r', 'end_ctime', TYPE_STR),
            'start_pay_time' => Tool_Input::clean('r', 'start_pay_time', TYPE_STR),
            'end_pay_time' => Tool_Input::clean('r', 'end_pay_time', TYPE_STR),
            'user_type' => Tool_Input::clean('r', 'user_type', TYPE_UINT),
        );
        if (empty($this->searchConf['wid']))
        {
            $this->searchConf['wid'] = array_keys(App_Admin_Web::getAllowedWids4User());
        }
    }

    protected function checkPara()
    {
//        if (empty($this->searchConf['wid']) && $this->_user['wid'])
//        {
//            $this->searchConf['wid'] = $this->_user['wid'];
//        }
        if (empty($this->searchConf['user_type']))
        {
            $this->searchConf['user_type'] = Conf_Base::COOPWORKER_DRIVER;
        }
    }

    protected function main()
    {
        $this->statementStepList = Conf_Coopworker::$Statement_Step;
        unset($this->statementStepList[Conf_Coopworker::STATEMENT_STEP_CHECK]);
        unset($this->statementStepList[Conf_Coopworker::STATEMENT_STEP_PAID]);
        $staffs = Admin_Api::getStaffList();
        $this->staffList = Tool_Array::list2Map($staffs['list'], 'suid', 'name');
        $this->statementInfo = Logistics_Coopworker_Api::getStatementList($this->searchConf);
        $cuids = Tool_Array::getFields($this->statementInfo, 'cuid');

        $this->paidStatus = Conf_Order::$PAY_STATUS;
        unset($this->paidStatus[Conf_Order::PART_PAID]);

        if (!empty($cuids))
        {
            if ($this->searchConf['user_type'] == Conf_Base::COOPWORKER_DRIVER)
            {
                // 补充司机信息
                $ld = new Logistics_Driver();
                $this->driverInfos = Tool_Array::list2Map($ld->getByDids(array_unique($cuids)), 'did');
            }
            else if ($this->searchConf['user_type'] == Conf_Base::COOPWORKER_CARRIER)
            {
                // 补充司机信息
                $lc = new Logistics_Carrier();
                $this->carrierInfos = Tool_Array::list2Map($lc->getByCids(array_unique($cuids)), 'cid');
            }

            $this->addFootJs(array('js/apps/coopworker.js'));
        }
    }

    protected function outputBody()
    {
        if (empty($this->wid))
        {
            $this->searchConf['wid'] = 0;
        }
        $this->smarty->assign('statement_list', $this->statementInfo);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('bank_desc', Conf_Base::$BANK_DESC);
        $this->smarty->assign('coopworker_payment_types', Conf_Base::$COOPWORKER_PAYMENT_TYPES);
        $this->smarty->assign('driver_list', $this->driverInfos);
        $this->smarty->assign('carrier_list', $this->carrierInfos);
        $this->smarty->assign('user_types', Conf_Base::getCoopworkerTypes());
        $this->smarty->assign('source_list', Conf_Driver::$DRIVER_SOURCE);
        $this->smarty->assign('step_list', $this->statementStepList);
        $this->smarty->assign('staff_list', $this->staffList);
        $this->smarty->assign('warehouse_list', App_Admin_Web::getAllowedWids4User());
        $this->smarty->display('order/driver_order_statement.html');
    }
}

$app = new App('pri');
$app->run();

