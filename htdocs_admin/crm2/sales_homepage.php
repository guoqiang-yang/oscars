<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    // cgi参数
    private $suid;
    // 中间结果
    private $saleInfo;
    private $scheduleTotal;
    private $scheduleList;
    private $aftersaleList;
    /**
     * 是否为当前管理员的客户.
     * 作用主要是：限制销售人员操作；只有自己的客户才可进行某些操作
     */
    private $isSale = false;

    protected function getPara()
    {
        $this->suid = Tool_Input::clean('r', 'suid', TYPE_UINT);
        if(empty($this->suid))
        {
            $this->suid = $this->_uid;
        }
    }

    protected function main()
    {
        $this->saleInfo = Admin_Api::getStaff($this->suid);
        if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW))
        {
            $this->isSale = true;
        }
        
        if($this->isSale) {
            $performanceList = Crm2_Api::getPerformanceList(array($this->suid), date('Y-m-01', time()), date('Y-m-d', time()));
            if (isset($performanceList[$this->suid])) {
                $this->saleInfo['order_num'] = $performanceList[$this->suid]['order'];
                $this->saleInfo['order_amount'] = $performanceList[$this->suid]['spending_amount']['all'];
                $this->saleInfo['refund_num'] = $performanceList[$this->suid]['refund_stat']['num'];
                $this->saleInfo['refund_amount'] = $performanceList[$this->suid]['refund'];
                $this->saleInfo['total_customer'] = !empty($performanceList[$this->suid]['total_customer']) ? $performanceList[$this->suid]['total_customer'] : 0;
            } else {
                $this->saleInfo['order_num'] = 0;
                $this->saleInfo['order_amount'] = 0;
                $this->saleInfo['refund_num'] = 0;
                $this->saleInfo['refund_amount'] = 0;
                $this->saleInfo['total_customer'] = 0;
            }

            $performanceMoreinfos = Crm2_Api::getPerformanceMoreinfos(array($this->suid), date('Y-m-01', time()), date('Y-m-d', time()));
            $this->saleInfo['aftersale_num'] = $performanceMoreinfos[$this->suid]['after_sales'];
            $this->saleInfo['schedule_num'] = $performanceMoreinfos[$this->suid]['schedules'];
            $this->saleInfo['visit_num'] = $performanceMoreinfos[$this->suid]['visits']['scene'] + $performanceMoreinfos[$this->suid]['visits']['un_scene'];
            $this->scheduleList = Crm2_Sale_Schedule_Api::getSaleScheduleList(array('from_day' => date('Y-m-d H:i:s', time()), 'suid' => $this->suid), $this->scheduleTotal, 0, 0);
            $scheduleList = Crm2_Sale_Schedule_Api::getSaleScheduleList(array('end_day' => date('Y-m-d H:i:s', time()), 'suid' => $this->suid), $total, 0, 1);

            if (!empty($scheduleList)) {
                $this->scheduleList[] = current($scheduleList);
                $this->scheduleTotal += 1;
            }
        }
        $this->aftersaleList = Aftersale_Api::getList(array('exec_status' => '2,3,4', 'suid' => $this->suid), 0, 0);
        $this->addFootJs(array(
            'js/apps/sale_schedule.js'
        ));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $this->smarty->assign('sale_info', $this->saleInfo);
        //是否为销售总监
        $this->smarty->assign('is_sale', $this->isSale);
        $this->smarty->assign('schedule_list', $this->scheduleList);
        $this->smarty->assign('schedule_total', $this->scheduleTotal);
        $this->smarty->assign('aftersale_list', $this->aftersaleList);
        $this->smarty->assign('remind_tags', Conf_Crm::getRemindList());
        $this->smarty->assign('status_list', Conf_Aftersale::$STATUS);
        $this->smarty->display('crm2/sales_homepage.html');
    }
}

$app = new App();
$app->run();