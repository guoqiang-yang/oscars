<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $start;
    private $searchConf;
    private $num = 20;
    private $total;
    private $scheduleList;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchConf = array(
            'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
            'group_leader' => Tool_Input::clean('r', 'group_leader', TYPE_UINT),
            'from_day' => Tool_Input::clean('r', 'from_day', TYPE_STR),
            'end_day' => Tool_Input::clean('r', 'end_day', TYPE_STR),
        );
    }

    protected function checkPara()
    {
        if(!empty($this->searchConf['from_day']))
        {
            $this->searchConf['from_day'] = date('Y-m-d H:i:s', strtotime($this->searchConf['from_day']));
        }
        if(!empty($this->searchConf['end_day']))
        {
            $this->searchConf['end_day'] = date('Y-m-d H:i:s', strtotime($this->searchConf['end_day']));
        }
    }

    protected function main()
    {
        if(!Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_ADMIN_NEW))
        {
            if(!empty($this->searchConf['group_leader']))
            {
                $this->searchConf['suid'] = $this->searchConf['group_leader'];
            }else{
                $this->searchConf['suid'] = $this->_user['team_member'];
            }
        }
        $this->scheduleList = Crm2_Sale_Schedule_Api::getSaleScheduleList($this->searchConf, $this->total ,$this->start, $this->num);
        $this->addFootJs(array('js/apps/sale_schedule.js'));
    }

    protected function outputBody()
    {
        unset($this->searchConf['suid']);
        $app = '/crm2/sale_schedule_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('schedule_list', $this->scheduleList);
        $this->smarty->assign('total', $this->total);
        unset($this->searchConf['type']);
        $page_url = '/crm2/sale_schedule_list.php?' . http_build_query($this->searchConf);
        $this->smarty->assign('page_url', $page_url);
        $this->smarty->assign('team_member', Admin_Api::getStaffs($this->_user['team_member']));
        $this->smarty->assign('now_time', date('Y-m-d 00:00:00',time()));
        $this->smarty->assign('remind_tags', Conf_Crm::getRemindList());
        $this->smarty->display('crm2/sale_schedule_list.html');
    }
}

$app = new App('pri');
$app->run();