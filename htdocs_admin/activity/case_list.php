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
            'id' => Tool_Input::clean('r', 'id', TYPE_UINT),
            'fid' => Tool_Input::clean('r', 'fid', TYPE_UINT),
            'house_style' => Tool_Input::clean('r', 'house_style', TYPE_UINT),
            'house_type' => Tool_Input::clean('r', 'house_type', TYPE_UINT),
            'house_space' => Tool_Input::clean('r', 'house_space', TYPE_UINT),
            'house_area' => Tool_Input::clean('r', 'house_area', TYPE_UINT),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
            'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
        );
    }

    protected function main()
    {
        $sort = 'order by index_sortby desc, id desc';
        $data = Case_Api::getList($this->searchConf, $this->start, $this->num, $sort);
        $this->list = $data['list'];
        $this->total = $data['total'];

        $this->addFootJs(array());
    }

    protected function outputBody()
    {
        $queryStr = http_build_query($this->searchConf);
        $app = '/activity/case_list.php?' . $queryStr;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('house_style', Conf_Fit::getHouseStyle());
        $this->smarty->assign('house_type', Conf_Fit::getHouseType());
        $this->smarty->assign('house_space', Conf_Fit::getHouseSpace());
        $this->smarty->assign('house_area', Conf_Fit::getHouseArea());
        $this->smarty->assign('city_list', Conf_City::$CITY);

        $conf = $this->searchConf;
        unset($conf['status']);
        $queryStr = http_build_query($conf);
        $searchUrl = '/activity/case_list.php?' . $queryStr;
        $this->smarty->assign('search_url', $searchUrl);

        $this->smarty->display('activity/case_list.html');
    }
}

$app = new App('pri');
$app->run();