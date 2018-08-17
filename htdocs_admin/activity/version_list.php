<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $start;
    private $searchConf;
    private $num = 20;
    private $total;
    private $list;

    protected function checkAuth()
    {
        parent::checkAuth('/admin/version_list');
    }

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);

        $this->searchConf = array(
            'cate' => Tool_Input::clean('r', 'cate', TYPE_UINT),
            'dev' => Tool_Input::clean('r', 'dev', TYPE_UINT),
            'channel' => Tool_Input::clean('r', 'channel', TYPE_STR),
            'version' => Tool_Input::clean('r', 'version', TYPE_STR),
        );
    }

    protected function main()
    {
        $data = Version_Api::getList($this->searchConf, $this->start, $this->num);

        $this->list = $data['list'];
        $this->total = $data['total'];

        Admin_Api::appendStaffSimpleInfo($this->list, array('suid'));
    }

    protected function outputBody()
    {
        $app = '/activity/version_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('cate_list', Conf_App_Version::$CATE_LIST);
        $this->smarty->assign('dev_list', Conf_App_Version::$DEV_LIST);

        $this->smarty->display('version/list.html');
    }
}

$app = new App('pri');
$app->run();

