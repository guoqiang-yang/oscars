<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/9/13
 * Time: 下午6:09
 */
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $start;
    private $num = 20;
    private $list;
    private $total;
    private $product;
    private $date;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
    }

    protected function main()
    {
        $data = Activity_Flash_Api::getList(array(), $this->start, $this->num);
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
        $app = '/activity/flash_activity_list.php?';
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('list', $this->list);
        $this->smarty->assign('date', $this->date);
        $this->smarty->assign('total', $this->total);
        $this->smarty->display('activity/flash_activity_list.html');
    }
}

$app = new App('pri');
$app->run();