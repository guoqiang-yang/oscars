<?php
include_once('../../global.php');

class App extends App_Admin_Page
{
    // 中间结果
    private $pictures;
    private $total;
    private $searchConf;
    private $staffList;
    private $start;
    private $num = 20;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchConf = array(
            'platform' => Tool_Input::clean('r', 'platform', TYPE_STR),
            'type' => Tool_Input::clean('r', 'type', TYPE_STR),
            'status' => Tool_Input::clean('r', 'status', TYPE_STR),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_STR),
        );
    }

    protected function main()
    {
        $res = Activity_Api::getPictureList($this->searchConf, $this->start, $this->num);
        $this->pictures = $res['list'];
        $this->total = $res['total'];
        $staffs = Admin_Api::getStaffList();
        $this->staffList = Tool_Array::list2Map($staffs['list'], 'suid', 'name');

        $this->addFootJs(array('js/apps/picture.js'));
    }

    protected function outputBody()
    {
        $app = '/activity/picture_list.php?';
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('city_list', Conf_City::$CITY);
        $this->smarty->assign('list', $this->pictures);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('platform_list', Conf_Picture::$PLATFORM);
        $this->smarty->assign('type_list', Conf_Picture::$TYPE);
        $this->smarty->assign('staff_list', $this->staffList);

        $this->smarty->display('activity/picture_list.html');
    }
}

$app = new App('pri');
$app->run();