<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $start;
    private $searchConf;
    private $num = 20;
    private $total;
    private $visitList;
    private $type;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->type = Tool_Input::clean('r', 'type', TYPE_UINT);
        $this->searchConf = array(
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'group_leader' => Tool_Input::clean('r', 'group_leader', TYPE_UINT),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
            'visit_type' => Tool_Input::clean('r', 'visit_type', TYPE_UINT),
            'from_day' => Tool_Input::clean('r', 'from_day', TYPE_STR),
            'end_day' => Tool_Input::clean('r', 'end_day', TYPE_STR),
        );
    }

    protected function checkPara()
    {
        if(!empty($this->searchConf['from_day'])){
            $this->searchConf['from_day'] = date('Y-m-d H:i:s', strtotime($this->searchConf['from_day']));
        }
        if(!empty($this->searchConf['end_day'])){
            $this->searchConf['end_day'] = date('Y-m-d H:i:s', strtotime($this->searchConf['end_day']));
        }
        if(!Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_ADMIN_NEW))
        {
            if(!empty($this->searchConf['group_leader']))
            {
                $this->searchConf['suid'] = $this->searchConf['group_leader'];
            }else{
                $this->searchConf['suid'] = $this->_user['team_member'];
            }
        }
        if(!empty($this->type))
        {
            $this->searchConf['type'] = $this->type;
        }
    }

    protected function main()
    {
        $this->visitList = Crm2_Customer_Visit_Api::getListByWhere($this->searchConf, $this->total ,$this->start, $this->num);

        $this->addFootJs(array('js/apps/customer_visit.js'));
    }

    protected function outputBody()
    {
        unset($this->searchConf['type']);
        $app = '/crm2/customer_visit_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('page_url', $app);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('visit_list', $this->visitList);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('city_list', Conf_City::$CITY);
        $this->smarty->assign('visit_types', Conf_Crm::getTypeList());
        $this->smarty->assign('visit_type', $this->type);
        $this->smarty->assign('team_member', Admin_Api::getStaffs($this->_user['team_member']));
        $this->smarty->display('crm2/customer_visit_list.html');
    }
}

$app = new App('pri');
$app->run();