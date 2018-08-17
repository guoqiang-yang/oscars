<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $start;
    private $searchConf;
    private $num = 20;
    private $adminList;
    private $list;
    private $total;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchConf = array(
            'suid' => Tool_Input::clean('r', 'suid', TYPE_UINT),
        );
        
        if (!empty($_REQUEST['ip']))
        {
            $this->searchConf['ip'] = $_REQUEST['ip'];
        }
    }

    protected function main()
    {
        $data = Admin_Api::getLoginLogList($this->searchConf, $this->start, $this->num);
        $this->list = $data['list'];
        $this->total = $data['total'];
        $this->adminList = Admin_Api::getStaffList();
        $this->addFootJs(array('js/apps/admin.js'));
    }

    protected function outputBody()
    {
        $app = '/admin/admin_login_log.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('admin_list', $this->adminList['list']);
        $this->smarty->assign('total', $this->total);

        $this->smarty->display('admin/admin_login_log.html');
    }
}

$app = new App('pri');
$app->run();

