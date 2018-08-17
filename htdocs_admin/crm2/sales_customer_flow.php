<?php
include_once('../../global.php');
/**
 * title 销售客户转接
 * @author wangxuemin
 */
class App extends App_Admin_Page
{
    /* 客户信息 */
    private $customers;
    /* 销售专员 */
    private $salesmanList;
    /* 全部销售专员 */
    private $allSalerList;
    /* 当前组销售专员 */
    private $groupSalerList;
    /* 当前城市信息 */
    private $curCity;
    /* 管理员 */
    private $isAdmin;

    /**
     * (non-PHPdoc)
     * @see Base_App::getPara()
     */
    protected function getPara()
    {
        
    }
    
    /**
     * (non-PHPdoc)
     * @see Base_App::main()
     */
    protected function main()
    {
        /* 当前城市 */
        $this->curCity = City_Api::getCity();
        /* 判断是否是管理员 */
        $this->isAdmin = Admin_Role_Api::isAdmin($this->_uid, $this->_user);
        if ($this->isAdmin) {
            /* 当前城市下全部销售专员 */
            $this->allSalerList = Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW, false, 0, $this->curCity['city_id']);
            foreach ($this->allSalerList as $saler)
            {
                if ($saler['status'] == 0)
                {
                    $this->salesmanList[] = $saler;
                }
            }
        } else {
            /* 下属销售专员 */
            $this->groupSalerList = Admin_Role_Api::getTeamMembers(Conf_Admin::ROLE_SALES_NEW, false, 0, $this->curCity['city_id'], $this->_user['team_member']);
            foreach ($this->groupSalerList as $saler)
            {
                if ($saler['status'] == 0)
                {
                    $this->salesmanList[] = $saler;
                }
            }
        }
        //header("Content-type:text/html;charset=utf-8");
        //var_dump('<pre>', $this->_user);exit;
        /* 取全部下属 */
        //var_dump('<pre>', Admin_Role_Api::getTeamMembersById(Conf_Admin::ROLE_SALES_NEW, false, 0, $this->curCity['city_id'], $this->_uid));exit;
    }
    
    /**
     * (non-PHPdoc)
     * @see App_Admin_Page::checkAuth()
     */
    protected function checkAuth()
    {
        parent::checkAuth('/crm2/sales_customer_flow');
    }

    /**
     * (non-PHPdoc)
     * @see Base_App::outputBody()
     */
    protected function outputBody()
    {
        /* 销售专员 */
        $this->smarty->assign('salesman_list', $this->salesmanList);
        /* 模板输出 */
        $this->smarty->display('crm2/sales_customer_flow.html');
    }
}
$app = new App('pri');
$app->run();