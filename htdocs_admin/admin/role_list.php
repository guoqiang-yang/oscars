<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $list;
    private $total;
    private $num = 20;
    private $start;

    protected function getPara()
    {
        
    }

    protected function main()
    {
        $data = Permission_Api::getList(array(), $this->start, $this->num);

        $this->list = $data['list'];
        $this->total = $data['total'];
        $this->addFootJs('js/apps/role.js');
    }

    protected function outputBody()
    {
        $app = '/admin/role_list.php';
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('total', $this->total);

        $this->smarty->display('admin/role_list.html');
    }
    
}

$app = new App('pri');
$app->run();

