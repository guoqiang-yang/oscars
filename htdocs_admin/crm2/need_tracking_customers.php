<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $start;
    private $searchConf;
    private $dueDate;
    private $num = 20;
    private $total;
    private $customers;
    private $salesmanList;
    private $isSales;
    private $teamMemberInfos;
    private $today;
    private $cate;        //0应回访；1已回访；2不回访

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->cate = Tool_Input::clean('r', 'cate', TYPE_UINT);
        $this->searchConf = array(
            'sales_suid' => Tool_Input::clean('r', 'sales_suid', TYPE_UINT),
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
            'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
        );

        $this->searchConf['status'] = Conf_Base::STATUS_NORMAL;
    }

    protected function main()
    {
        $this->isSales = FALSE;
        if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW) && !in_array($this->_uid, Conf_Admin::$SUPER_SALES))
        {
            $this->searchConf['sales_suid'] = !empty($this->searchConf['sales_suid']) && in_array($this->searchConf['sales_suid'], $this->_user['team_member']) ? $this->searchConf['sales_suid'] : $this->_uid;

            $this->isSales = TRUE;
        }

        if (Str_Check::checkMobile($this->searchConf['mobile']) && $this->isSales)
        {
            unset($this->searchConf['sales_suid']);
        }

        $this->searchConf['tracking_cate'] = $this->cate;
        $res = Crm2_Api::getNeeTrackingCustomerList($this->searchConf, $this->start, $this->num);
        $this->customers = $res['list'];
        $this->total = $res['total'];
        $this->salesmanList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW, 0);

        // 取团队成员用户信息
        if (count($this->_user['team_member']) > 1)
        {
            $this->teamMemberInfos = Admin_Api::getStaffs($this->_user['team_member']);
        }

        $this->today = date('Y-m-d');
        $this->dueDate = date('Y-m-d', strtotime('today') + 7 * 86400);

        $this->addFootJs(array('js/apps/tracking.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $app = '/crm2/need_tracking_customers.php?cate=' . $this->cate . '&' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('customers', $this->customers);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('salesman_list', $this->salesmanList);
        $this->smarty->assign('is_sales', $this->isSales);
        $this->smarty->assign('uid', $this->_uid);
        $this->smarty->assign('team_members', $this->teamMemberInfos);
        $this->smarty->assign('cate', $this->cate);
        $this->smarty->assign('visit_due_date', $this->dueDate);

        $this->smarty->display('crm2/need_tracking_customers.html');
    }
}

$app = new App('pri');
$app->run();

