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
            'design' => Tool_Input::clean('r', 'design', TYPE_UINT),
            'fit_step' => Tool_Input::clean('r', 'fit_step', TYPE_UINT),
            'main_material' => Tool_Input::clean('r', 'main_material', TYPE_UINT),
            'other_material' => Tool_Input::clean('r', 'other_material', TYPE_UINT),
            'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
        );
    }

    protected function main()
    {
        $data = Wiki_Api::getList($this->searchConf, $this->start, $this->num);
        $this->list = $data['list'];
        $this->total = $data['total'];
        $this->addFootJs(array('js/apps/wiki.js'));
    }

    protected function outputBody()
    {
        $queryStr = http_build_query($this->searchConf);
        $app = '/activity/wiki_list.php?' . $queryStr;
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('design', Conf_Fit::getDesign());
        $this->smarty->assign('fit_step', Conf_Fit::getFitStep());
        $this->smarty->assign('main_material', Conf_Fit::getMainMaterial());
        $this->smarty->assign('other_material', Conf_Fit::getOtherMaterial());

        $conf = $this->searchConf;
        unset($conf['status']);
        $queryStr = http_build_query($conf);
        $searchUrl = '/activity/wiki_list.php?' . $queryStr;
        $this->smarty->assign('search_url', $searchUrl);

        $this->smarty->display('activity/wiki_list.html');
    }
}

$app = new App('pri');
$app->run();