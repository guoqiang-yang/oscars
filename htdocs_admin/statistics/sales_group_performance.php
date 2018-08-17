<?php
/**
 * Created by PhpStorm.
 * User: jslsxu
 * Date: 2017/7/20
 * Time: 下午2:14
 */

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $date;

    private $canEdit;
    private $list;
    protected function getPara()
    {
        $this->date = Tool_Input::clean('r', 'date', TYPE_STR);
        if(empty($this->date)){
            $this->date = date('Y-m-d');
        }
    }

    protected function main()
    {
        $this->canEdit = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_ASSIS_SALER_NEW);
        $salesStatistics = new Statistics_Sales();
        $this->list = $salesStatistics->getGroupSalesList($this->date);
    }

    protected function outputBody()
    {
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('canEdit', $this->canEdit);
        $this->smarty->assign('date', $this->date);
        $this->smarty->display('statistics/sales_group_performance.html');
    }
}

$app = new App('pri');
$app->run();