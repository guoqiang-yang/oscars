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
    private $list;
    private $total;
    private $searchConf;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchConf = array(
            'fid' => Tool_Input::clean('r', 'fid', TYPE_UINT),
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'name' => Tool_Input::clean('r', 'name', TYPE_STR),
            'work_age' => Tool_Input::clean('r', 'work_age', TYPE_UINT),
            'birthplace' => Tool_Input::clean('r', 'birthplace', TYPE_STR),
            'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
        );
    }

    protected function main()
    {
        $data = Forman_Api::getFormanList($this->searchConf, $this->start, $this->num);
        $this->list = $data['list'];
        $this->total = $data['total'];
    }

    protected function outputBody()
    {
        $queryStr = http_build_query($this->searchConf);
        $app = '/activity/forman_list.php?' . $queryStr;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('search_conf', $this->searchConf);

        $conf = $this->searchConf;
        unset($conf['status']);
        $queryStr = http_build_query($conf);
        $searchUrl = '/activity/forman_list.php?' . $queryStr;
        $this->smarty->assign('search_url', $searchUrl);

        $this->smarty->display('activity/forman_list.html');
    }
}

$app = new App('pri');
$app->run();