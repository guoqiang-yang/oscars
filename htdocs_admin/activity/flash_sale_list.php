<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/12
 * Time: 上午10:57
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
    }

    protected function main()
    {
        $data = Activity_Flash_Sale_Api::getList(array('fid' => $this->fid), $this->start, $this->num);
        $this->list = $data['list'];
        $this->total = $data['total'];

        $shop = new Shop_Api();
        $suid = Tool_Array::getFields($this->list, 'pid');
        $this->product = $shop->getProductInfos($suid);
        $this->date = date('Y-m-d H:i:s');
        $this->addFootJs(array('js/apps/flash_sale.js'));
    }

    protected function outputBody()
    {
        $app = '/activity/flash_sale_list.php?';
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('fid', $this->fid);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('date', $this->date);
        $this->smarty->assign('total', $this->total);
        $this->smarty->display('activity/flash_sale_list.html');
    }
}

$app = new App('pri');
$app->run();