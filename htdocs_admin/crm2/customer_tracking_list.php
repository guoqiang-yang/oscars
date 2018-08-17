<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $num = 20;
    private $start;
    private $searchConf;
    private $total;
    private $trackingList;
    private $isShowSelectedSalers = 0;
    private $salesmanList = array();

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchConf = array(
            'cid' => Tool_Input::clean('r', 'cid', TYPE_STR),
            'sales_suid' => Tool_Input::clean('r', 'sales_suid', TYPE_UINT),
            'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
            'from_date' => Tool_Input::clean('r', 'from_date', TYPE_STR),
            'end_date' => Tool_Input::clean('r', 'end_date', TYPE_STR),
            'edit_suid' => Tool_Input::clean('r', 'edit_suid', TYPE_STR),
        );
    }

    protected function main()
    {
        $res = Crm2_Api::getCustomerTrackingList($this->searchConf, $this->start, $this->num);

        $this->trackingList = $res['data'];
        $this->total = $res['total'];

        // 显示可以查询的管理员
        $this->isShowSelectedSalers = 2;
        $this->salesmanList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW);

        $this->addFootJs(array());
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $app = '/crm2/customer_tracking_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('list', $this->trackingList);
        $this->smarty->assign('salesman_list', $this->salesmanList);
        $this->smarty->assign('is_show_selected_salers', $this->isShowSelectedSalers);
        $this->smarty->assign('tracking_types', Conf_User::$Customer_Tracking_Types);

        $this->smarty->display('crm2/customer_tracking_list.html');
    }
}

$app = new App('pri');
$app->run();

