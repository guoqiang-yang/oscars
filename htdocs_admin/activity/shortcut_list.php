<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/11/1
 * Time: 上午10:11
 */
include_once('../../global.php');

class App extends App_Admin_Page
{
    // cgi参数
    private $start;
    private $num = 20;
    private $total;
    private $list;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchConf = array(
            '_online' => Tool_Input::clean('r', 'online', TYPE_UINT),
            'city' => Tool_Input::clean('r', 'city', TYPE_UINT),
            'name' => addslashes(Tool_Input::clean('r', 'name', TYPE_STR)),
        );
    }

    protected function main()
    {
        if (!empty($this->searchConf['_online']))
        {
            $this->searchConf['online'] = ($this->searchConf['_online'] - 1);
        }

        $this->data = Activity_Shortcut_Api::getList($this->searchConf, $this->start, $this->num);
        $this->city = Conf_City::$CITY;
        $this->list = $this->data['list'];
        $this->total = $this->data['total'];
        $this->addFootJs(array('js/apps/shortcut.js',));
    }
    protected function outputBody()
    {
        $app = '/activity/shortcut_list.php';
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('city', $this->city);
        $this->smarty->assign('searchConf', $this->searchConf);

        $this->smarty->display('activity/shortcut_list.html');
    }
}

$app = new App('pri');
$app->run();