<?php
/**
 * Created by PhpStorm.
 * User: jslsxu
 * Date: 2017/7/20
 * Time: 下午2:13
 */

include_once('../../global.php');

class App extends App_Admin_Page{
    private $group;
    private $month;

    private $groupList;
    private $monthList;
    private $canEdit;
    private $list;
    protected function getPara()
    {
        $this->group = Tool_Input::clean('r', 'group', TYPE_INT);
        $this->month = Tool_Input::clean('r', 'month', TYPE_STR);
    }

    protected function main()
    {
        $saleList = Admin_Api::getSaleStaff();
        $this->groupList = array();
        $this->groupList[] = array('suid' => 0, 'name' => '所有');
        foreach ($saleList as $key => $value){
            foreach ($value as $item){
                if($item['suid'] == $key){
                    $this->groupList[] = $item;
                }
            }
        }
        $this->monthList = array();
        $today = date('Y-m');
        for ($i = 0; $i < 12; $i++){
            $this->monthList[] = date('Y-m', strtotime("$today - $i month"));
        }
        $this->canEdit = Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_ASSIS_SALER_NEW);
        $statisticsSales = new Statistics_Sales();
        $this->list = $statisticsSales->getIndividualSalesList($this->group, $this->month);
    }

    protected function outputBody()
    {

        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('canEdit', $this->canEdit);
        $this->smarty->assign('groupList', $this->groupList);
        $this->smarty->assign('monthList', $this->monthList);
        $this->smarty->assign('group', $this->group);
        $this->smarty->assign('month', $this->month);
        $this->smarty->display('statistics/sales_salary.html');
    }
}

$app = new App('pri');
$app->run();