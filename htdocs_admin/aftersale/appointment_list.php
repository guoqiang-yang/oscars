<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/22
 * Time: 上午11:13
 */
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $start;
    private $num = 20;
    private $searchConf;
    private $list;
    private $total;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchConf = array(
            'case_id' => Tool_Input::clean('r', 'case_id', TYPE_UINT),
            'fid' => Tool_Input::clean('r', 'fid', TYPE_UINT),
            'saler_suid' => Tool_Input::clean('r', 'saler_suid', TYPE_UINT),
            'house_style' => Tool_Input::clean('r', 'house_style', TYPE_UINT),
            'house_type' => Tool_Input::clean('r', 'house_type', TYPE_UINT),
            'house_area' => Tool_Input::clean('r', 'house_area', TYPE_UINT),
            'step' => Tool_Input::clean('r', 'step', TYPE_UINT),
        );
    }

    protected function main()
    {
        if(Admin_Role_Api::hasRole($this->_user, Conf_Admin::ROLE_SALES_NEW))
        {
            $this->searchConf['saler_suid'] = $this->_uid;
        }

        $data = Forman_Api::getAppointmentList($this->searchConf, $this->start, $this->num);
        $this->list = $data['list'];
        $this->total = $data['total'];
    }

    protected function outputBody()
    {
        $app = '/aftersale/appointment_list.php?';
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('house_style', Conf_Fit::getHouseStyle());
        $this->smarty->assign('house_type', Conf_Fit::getHouseType());
        $this->smarty->assign('house_area', Conf_Fit::getHouseArea());
        $this->smarty->assign('salers', Admin_Role_Api::getStaffOfRole(Conf_Admin::ROLE_SALES_NEW, Conf_Base::STATUS_NORMAL));

        $conf = $this->searchConf;
        unset($conf['step']);
        $queryStr = http_build_query($conf);
        $searchUrl = '/aftersale/appointment_list.php?' . $queryStr;
        $this->smarty->assign('search_url', $searchUrl);

        $this->smarty->display('aftersale/appointment_list.html');
    }
}

$app = new App('pri');
$app->run();