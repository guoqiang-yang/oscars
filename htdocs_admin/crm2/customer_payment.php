<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $start;
    private $searchConf;
    private $order;
    private $num = 20;
    private $total;
    private $customers;
    private $salesmanList;
    private $teamMemberInfos;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->order = Tool_Input::clean('r', 'order', TYPE_STR);

        $this->searchConf = array(
            'sales_suid' => Tool_Input::clean('r', 'sales_suid', TYPE_UINT),
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'payment_due_date' => Tool_Input::clean('r', 'payment_due_date', TYPE_STR)
        );

        if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW) && empty($this->searchConf['sales_suid']))
        {
            $this->searchConf['sales_suid'] = $this->_uid;
        }

        if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_CITY_ADMIN_NEW))
        {
            $this->searchConf['city_id'] = $this->_user['city_id'];
        }

        //$this->searchConf['level_for_saler'] = $this->_getSearchSalesLevels();
    }

    protected function main()
    {
        $allOrderbys = array(
            'payment_due_date',
            'account_balance'
        );
        $this->order = in_array($this->order, $allOrderbys) ? $this->order : 'payment_due_date';
        
        $customerList = Crm2_Api::getCustomerList4Payment($this->searchConf, $this->order, $this->start, $this->num);
//print_r($customerList);exit;
        $this->total = $customerList['total'];
        $this->customers = $customerList['data'];
        $allSalerList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW, 0);
        foreach ($allSalerList as $saler)
        {
            if ($saler['status'] == 0)
            {
                $this->salesmanList[$saler['suid']] = $saler;
            }
        }

        // 取团队成员用户信息
        if (count($this->_user['team_member']) > 1)
        {
            // 城市经理 临时判断，后期再改
            if(in_array(13, explode(',', $this->_user['roles'])))
            {
                $this->teamMemberInfos[] = $this->_user;
                $_sale_list = Admin_Api::getStaffs($this->_user['team_member']);
                foreach ($_sale_list as $_sale)
                {
                    $this->teamMemberInfos[] = $_sale;
                }

            }
        }

        $this->addFootJs(array(
            'js/apps/crm2.js',
        ));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $baseUrl = '/crm2/customer_payment.php?' . http_build_query($this->searchConf);
        $app = $baseUrl . '&order=' . $this->order;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('base_url', $baseUrl);
        $this->smarty->assign('pageHtml', $pageHtml);

        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('customers', $this->customers);
        $this->smarty->assign('uid', $this->_uid);
        $this->smarty->assign('team_members', $this->teamMemberInfos);
        $this->smarty->assign('salesman_list', $this->salesmanList);
        $this->smarty->assign('order_by', $this->order);

        $this->smarty->display('crm2/customer_payment.html');
    }
}

$app = new App('pri');
$app->run();

