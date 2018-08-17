<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/18
 * Time: 下午1:58
 */
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $start;
    private $num = 20;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->fid = Tool_Input::clean('r', 'fid', TYPE_UINT);
        $this->searchConf = array(
            'fid' => Tool_Input::clean('r', 'fid', TYPE_UINT),
            'type' => Tool_Input::clean('r', 'type', TYPE_UINT)
        );
    }

    protected function main()
    {
        $data = Activity_Floor_Sale_Api::getList($this->searchConf, $this->start, $this->num);
        $this->list = $data['list'];
        $this->total = $data['total'];

        /*$shop = new Shop_Api();
        $suid = Tool_Array::getFields($this->list, 'pid');
        $this->product = $shop->getProductInfos($suid);*/
        $this->date = date('Y-m-d H:i:s');
        $this->floor = Conf_Floor_Activity::$PICTURE;
        $this->addFootJs(array('js/apps/floor_activity.js'));
    }

    protected function outputBody()
    {
        $app = '/activity/floor_sale_list.php?fid=' . $this->fid .'&type='.$this->searchConf['type'];
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('fid', $this->fid);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('floor', $this->floor);
        $this->smarty->assign('date', $this->date);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('searchConf', $this->searchConf);
        $this->smarty->display('activity/floor_sale_list.html');
    }
}

$app = new App('pri');
$app->run();