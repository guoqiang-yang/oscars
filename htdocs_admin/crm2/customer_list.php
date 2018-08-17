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
    private $allSalerList;
    private $cities;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->order = Tool_Input::clean('r', 'order', TYPE_STR);

        $this->searchConf = array(
            'customer_kind' => Tool_Input::clean('r', 'customer_kind', TYPE_UINT),
            'identity' => Tool_Input::clean('r', 'identity', TYPE_UINT),
            'sale_status' => Tool_Input::clean('r', 'sale_status', TYPE_UINT),
            'sales_suid' => Tool_Input::clean('r', 'sales_suid', TYPE_UINT),
            'record_suid' => Tool_Input::clean('r', 'record_suid', TYPE_UINT),
            'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
            'address' => Tool_Input::clean('r', 'address', TYPE_STR),
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'btime' => Tool_Input::clean('r', 'btime', TYPE_STR),
            'etime' => Tool_Input::clean('r', 'etime', TYPE_STR),
            'first_order' => Tool_Input::clean('r', 'first_order', TYPE_STR),
            'second_order' => Tool_Input::clean('r', 'second_order', TYPE_STR),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
            'start_ctime' => Tool_Input::clean('r', 'start_ctime', TYPE_STR),
            'end_ctime' => Tool_Input::clean('r', 'end_ctime', TYPE_STR),
        );

        if (isset($this->searchConf['first_order']) && $this->searchConf['first_order'] == 'on')
        {
            $this->searchConf['first_order_date']['btime'] = $this->searchConf['btime'];
            $this->searchConf['first_order_date']['etime'] = $this->searchConf['etime'];
        }
        if (isset($this->searchConf['second_order']) && $this->searchConf['second_order'] == 'on')
        {
            $this->searchConf['second_order_date']['btime'] = $this->searchConf['btime'];
            $this->searchConf['second_order_date']['etime'] = $this->searchConf['etime'];
        }

        if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW) && empty($this->searchConf['sales_suid']))
        {
            $this->searchConf['sales_suid'] = $this->_uid;
        }
        $roleLevels = Admin_Role_Api::getRoleLevels($this->_uid, $this->_user);
        if (isset($roleLevels[Conf_Admin::ROLE_SALES_DIRECTOR]) || isset($roleLevels[Conf_Admin::ROLE_CITY_ADMIN_NEW]))
        {
            $this->searchConf['sales_director'] = true;
        }
        if (Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_CITY_ADMIN_NEW))
        {
            $this->searchConf['city_id'] = $this->_user['city_id'];
        }

        if (!Str_Check::checkMobile($this->searchConf['mobile']))
        {
            unset($this->searchConf['mobile']);
        }

        //$this->searchConf['level_for_saler'] = $this->_getSearchSalesLevels();
    }

    protected function main()
    {
        $allOrderbys = array(
            'last_order_date',
            'order_num',
            'total_amount'
        );
        $this->order = in_array($this->order, $allOrderbys) ? $this->order : 
                        ((!empty($this->searchConf['sales_suid'])? 'chg_sstatus_time': 'cid'));

        if ($this->_user['kind'] == Conf_Admin::JOB_KIND_PARTTIME)
        {
            $this->searchConf['staff_kind'] = Conf_Admin::JOB_KIND_PARTTIME;
        }
        
        $customerList = Crm2_Api::getCustomerListForAdmin($this->searchConf, $this->_user, $this->order, $this->start, $this->num);
//print_r($customerList);exit;
        $this->total = $customerList['total'];
        $this->customers = $customerList['data'];
        Crm2_Api::appendConsumeAttr($this->customers);

        $this->allSalerList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW, 0);
        foreach ($this->allSalerList as $saler)
        {
            if ($saler['status'] == 0)
            {
                $this->salesmanList[] = $saler;
            }
        }

        // 取团队成员用户信息
        if (count($this->_user['team_member']) > 1)
        {
            // 城市经理 临时判断，后期再改
            if(in_array(13, explode(',', $this->_user['roles'])))
            {
                $this->teamMemberInfos[] = $this->_user;
            }
            
            foreach ($this->allSalerList as $saler)
            {
                if (in_array($saler['suid'], $this->_user['team_member']))
                {
                    $this->teamMemberInfos[] = $saler;
                }
            }
        }

        $this->cities = Conf_City::$CITY;
        unset($this->cities[Conf_City::XIANGHE]);

        $this->addFootJs(array(
                             'js/apps/crm2.js',
                             'js/apps/package_coupon.js',
                             'js/apps/send_vip_coupon.js',
                             'js/apps/sale_schedule.js'
                         ));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $baseUrl = '/crm2/customer_list.php?' . http_build_query($this->searchConf);
        $app = $baseUrl . '&order=' . $this->order;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('base_url', $baseUrl);
        $this->smarty->assign('pageHtml', $pageHtml);

        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('customers', $this->customers);
        $this->smarty->assign('salesman_list', $this->salesmanList);
        $this->smarty->assign('allsaler_list', $this->allSalerList);
        $this->smarty->assign('rival_desc_list', Conf_User::$Desc_In_Rival);
        $this->smarty->assign('uid', $this->_uid);
        $this->smarty->assign('team_members', $this->teamMemberInfos);

        $this->smarty->assign('sale_status', Conf_User::$Customer_Sale_Status);
        $this->smarty->assign('crm_level_saler', Conf_User::$Crm_Level_BySaler);
        $this->smarty->assign('crm_identity', Conf_User::$Crm_Identity);
        $this->smarty->assign('status_list', Conf_Base::getCustomerStatus());
        $this->smarty->assign('is_partjob', $this->_user['kind'] == Conf_Admin::JOB_KIND_PARTTIME ? 1 : 0);
        $this->smarty->assign('query', http_build_query($this->searchConf));
        $this->smarty->assign('sales_levels', $this->_getAllSalesLevels());
        $this->smarty->assign('cities', $this->cities);
        $this->smarty->assign('remind_tags', Conf_Crm::getRemindList());
        $this->smarty->assign('vip_coupon_list', Conf_Coupon::getVipCouponList());
        $this->smarty->assign('city_list', Conf_City::$CITY);

        $this->smarty->display('crm2/customer_list.html');
    }

    /**
     * 获取搜索时的 客户的销售级别.
     */
    private function _getSearchSalesLevels()
    {
        $salesLevel = isset($_REQUEST['level_for_saler']) ? $_REQUEST['level_for_saler'] : Conf_Base::STATUS_ALL;
        if (array_key_exists($this->_user['kind'], Conf_User::$Grouped_Sales_Levels))
        {
            if (!in_array($salesLevel, Conf_User::$Grouped_Sales_Levels[$this->_user['kind']]))
            {
                $salesLevel = Conf_User::$Grouped_Sales_Levels[$this->_user['kind']];
            }
        }

        return $salesLevel;
    }

    /**
     * 获取当前用户可以访问的全部分类.
     */
    private function _getAllSalesLevels()
    {
        $salesLevels = array();
        if (array_key_exists($this->_user['kind'], Conf_User::$Grouped_Sales_Levels))
        {
            foreach (Conf_User::$Grouped_Sales_Levels[$this->_user['kind']] as $_level)
            {
                $salesLevels[$_level] = Conf_User::$Crm_Sales_Levels[$_level];
            }
        }
        else
        {
            $salesLevels = Conf_User::$Crm_Sales_Levels;
        }

        return $salesLevels;
    }
}

$app = new App('pri');
$app->run();

