<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $num = 20;
    private $start;
    private $total;
    private $searchConf;
    private $userList = array();

    protected function getPara()
    {
        $this->searchConf = array(
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'mobile' => Tool_Input::clean('r', 'mobile', TYPE_STR),
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
        );

        $this->start = Tool_Input::clean('r', 'start', TYPE_INT);
    }

    protected function main()
    {
        echo "Sorry! Offline!!";
        exit;
        
        $this->userList = Crm2_Api::getUserList($this->searchConf);
        $this->total = $this->userList['total'];

        $this->addFootJs(array('js/apps/crm2.js'));
        $this->addCss(array());
    }

    protected function outputBody()
    {
        $app = '/crm2/customer_list_cs.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);

        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('user_list', $this->userList['data']);
        $this->smarty->assign('total', $this->total);

        $this->smarty->display('crm2/customer_list_cs.html');
    }
}

$app = new App();
$app->run();