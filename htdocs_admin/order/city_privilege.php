<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    // 中间结果
    private $recordList;
    private $total;
    private $month;
    private $searchConf;
    private $amount=0;
    private $city_saler=0;

    protected function getPara()
    {
        $this->month = Tool_Input::clean('r', 'month', TYPE_STR);
    }

    protected function checkPara()
    {
        if(!Admin_Role_Api::isAdmin($this->_uid) && !Admin_Role_Api::hasRoles($this->_user, Conf_Admin::baseRkeyOfSalesLeader()) && !Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_CITY_ADMIN_NEW))
        {
            throw new Exception('没有权限访问');
        }
        if(empty($this->month))
        {
            $this->month = date('Ym');
        }
    }

    protected function main()
    {
        $this->searchConf['month'] = $this->month;
        $city_info = City_Api::getCity();
        $this->searchConf['city_id'] = $city_info['city_id'];
        if(!Admin_Role_Api::isAdmin($this->_uid) && !Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_CITY_ADMIN_NEW))
        {
            $this->searchConf['suid'] = $this->_uid;
        }

        $saleDao = new Data_Dao('t_sale_privilege_config');
        $this->total = $saleDao->getTotal($this->searchConf);
        $this->recordList = $saleDao->getListWhere($this->searchConf);
        if(!empty($this->recordList))
        {
            $pr = new Permission_Role();
            $suids = Tool_Array::getFields($this->recordList, 'suid');
            $saleInfos = Admin_Api::getStaffs($suids);
            $saleInfos = Tool_Array::list2Map($saleInfos, 'suid');
            foreach ($this->recordList as &$item)
            {
                $item['send_name'] = $saleInfos[$item['suid']]['name'];
                $role_ids = explode(',', $saleInfos[$item['suid']]['roles']);
                $item['roles'] = $pr->getBulk($role_ids);
                if(Admin_Role_Api::hasRole($saleInfos[$item['suid']], Conf_Admin::ROLE_CITY_ADMIN_NEW))
                {
                    $this->amount = $item['available_amount'];
                    $this->city_saler = $item['suid'];
                }
            }
        }
        $this->addFootJs(array('js/apps/city_privilege.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $this->smarty->assign('list', $this->recordList);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('month', $this->month);
        $this->smarty->assign('month_list', $this->_getMonths());
        $this->smarty->assign('city_amount', $this->amount);
        $this->smarty->assign('city_suid', $this->city_saler);
        $this->smarty->display('order/city_privilege.html');
    }

    private function _getMonths()
    {
        $data = array();
        for($month='2018-05';$month<=date('Y-m');$month=date('Y-m', strtotime("$month +1 month")))
        {
            $data[] = array(
                'month' => date('Ym', strtotime($month)),
                'name' => $month
            );
        }
        return $data;
    }
}

$app = new App('pri');
$app->run();

