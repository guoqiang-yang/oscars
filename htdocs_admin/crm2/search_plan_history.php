<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $cid;
    private $num = 20;
    private $start;
    private $list;
    private $total;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
    }

    protected function checkPara()
    {
        // 管理员角色判断
        $_isSaler = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW);
        if ($_isSaler)
        {
            $customer = Crm2_Api::getCustomerInfo($this->cid);
            if ($customer['customer']['sales_suid'] != $this->_uid)
            {
                throw new Exception('只能查看自己客户的搜索历史！');
            }
        }
    }

    protected function main()
    {
        $data = Saas_Api::getSearchHistory($this->cid, $this->start, $this->num);
        $this->total = $data['total'];
        $this->list = $data['list'];
    }

    protected function outputBody()
    {
        $app = '/crm2/search_plan_history.php?cid=' . $this->cid;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('cid', $this->cid);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('list', $this->list);

        $this->smarty->display('crm2/search_plan_history.html');
    }
}

$app = new App('pri');
$app->run();