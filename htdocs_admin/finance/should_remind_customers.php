<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    // cgi参数
    private $start;
    private $searchConf;
    private $order;
    private $cate; //0是未催账的，1是待催账的
    // 中间结果
    private $num = 20;
    private $total;
    private $customers;
    private $salesmanList;
    private $isSales;
    private $teamMemberInfos;
    private $today;
    private $adminStaff;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->order = Tool_Input::clean('r', 'order', TYPE_STR);
        $this->cate = Tool_Input::clean('r', 'cate', TYPE_UINT);
        $this->searchConf = array(
            'sales_suid' => Tool_Input::clean('r', 'sales_suid', TYPE_UINT),
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
            'last_remind_suid' => Tool_Input::clean('r', 'last_remind_suid', TYPE_STR),
        );

        $this->searchConf['status'] = Conf_Base::STATUS_NORMAL;
    }

    protected function main()
    {
        //排序，共有按金额，按催款次数，按应结账日期三种排序方式
        //默认使用按应结账日期排序
        switch ($this->order)
        {
            case 'amount_desc':
                $order = ' order by account_balance desc ';
                break;
            case 'amount_asc':
                $order = ' order by account_balance asc ';
                break;
            case 'count_desc':
                $order = ' order by remind_count desc ';
                break;
            case 'count_asc':
                $order = ' order by remind_count asc ';
                break;
            case 'date_asc':
                $order = ' order by payment_due_date asc ';
                break;
            case 'date_desc':
                $order = ' order by payment_due_date desc ';
                break;
            default:
                $order = ' order by payment_due_date asc ';
        }

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

        $this->searchConf['payment_due_cate'] = $this->cate;
        $res = Crm2_Api::getShouldRemindCustomerList($this->searchConf, $order, $this->start, $this->num);
        $this->customers = $res['list'];
        $this->total = $res['total'];
        $this->salesmanList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW, 0);

        // 取团队成员用户信息
        if (count($this->_user['team_member']) > 1)
        {
            $this->teamMemberInfos = Admin_Api::getStaffs($this->_user['team_member']);
        }

        $this->today = date('Y-m-d');

        $this->adminStaff = Admin_Api::getStaffList();

        $this->addFootJs(array('js/apps/remind.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $app = '/finance/should_remind_customers.php?order=' . $this->order . '&cate=' . $this->cate . '&' . http_build_query($this->searchConf);
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
        $this->smarty->assign('today', $this->today);
        $this->smarty->assign('cate', $this->cate);
        $this->smarty->assign('admin_list', $this->adminStaff['list']);

        $this->smarty->display('finance/should_remind_customers.html');
    }
}

$app = new App('pri');
$app->run();

