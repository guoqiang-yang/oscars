<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/14
 * Time: 下午6:28
 */
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $start;
    private $num = 20;
    private $searchConf;
    private $list;
    private $total;
    private $floor;
    private $city;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchConf = array(
            'type' => Tool_Input::clean('r', 'type', TYPE_UINT),
            'city' => Tool_Input::clean('r', 'city', TYPE_UINT),
        );
    }

    protected function main()
    {
        $data = Activity_Floor_Api::getList($this->searchConf, $this->start, $this->num);
        $this->list = $data['list'];
        $this->total = $data['total'];
        $this->floor = Conf_Floor_Activity::$FLOOR_TYPE;
        $this->city = Conf_City::$CITY;
        $this->addFootJs(array('js/apps/floor_activity.js'));
    }

    protected function outputBody()
    {
        $app = '/activity/floor_activity_list.php?' . http_build_query($this->searchConf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('floor', $this->floor);
        $this->smarty->assign('city', $this->city);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('searchConf', $this->searchConf);
        $this->smarty->display('activity/floor_activity_list.html');
    }
}

$app = new App('pri');
$app->run();