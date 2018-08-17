<?php
/**
 * Created by PhpStorm.
 * User: zouliangwei
 * Date: 16/11/9
 * Time: 下午1:57
 */

include_once("../../global.php");

class App extends App_Admin_Page
{
    // cgi参数
    private $start;
    private $num = 20;
    private $total;
    private $list;
    private $mode;
    private $sum = 0;
    private $priceTotal = 0;
    //search
    private $search_conf;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->search_conf = array(
            'tid' => Tool_Input::clean('r', 'tid', TYPE_UINT),
            'mode' => $this->mode
        );
    }

    protected function main()
    {
        $oo = new Order_Order();
        $data = $oo->getPromotionOrdersByCouponId($this->search_conf['tid'], FALSE, $this->start, $this->num);
        $this->sum = $data['sum'];
        $this->priceTotal = $data['priceTotal'];

        $this->list = $data['list'];
        $this->total = $data['total'];
    }

    protected function outputBody()
    {
        $app = '/activity/coupon_order_list.php?' . http_build_query($this->search_conf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('list', $this->list);
        $cityList = City_Api::getCityList(TRUE);
        $this->smarty->assign('city_list', $cityList);
        $this->smarty->assign('_warehouseList', Conf_Warehouse::getWarehouseByAttr('ext_customer'));
        $this->smarty->assign('order_source', Conf_Order::$SOURCE_DESC);
        $this->smarty->assign('price_total', $this->priceTotal);
        $this->smarty->assign('sum', $this->sum);
        $this->smarty->assign('tid', $this->search_conf['tid']);
        $this->smarty->display('promotion/coupon_order_list.html');
    }
}

$app = new App('pri');
$app->run();