<?php
/**
 * Created by PhpStorm.
 * User: zouliangwei
 * Date: 16/11/1
 * Time: 下午5:12
 */
include_once('../../global.php');

class App extends App_Admin_Page
{
    // cgi参数
    private $start;
    private $num = 20;
    private $mode;
    private $total;
    private $list;
    private $sum = 0;
    private $priceTotal = 0;
    //search
    private $search_conf;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->mode = Tool_Input::clean('r', 'mode', TYPE_STR);
        if ($this->mode == 'order')
        {
            $this->search_conf = array(
                'aid' => Tool_Input::clean('r', 'aid', TYPE_UINT),
                'mode' => $this->mode
            );
        }
        else
        {
            $this->search_conf = array(
                'a_id' => Tool_Input::clean('r', 'a_id', TYPE_UINT),
                'a_title' => Tool_Input::clean('r', 'a_title', TYPE_STR),
                'a_stime' => Tool_Input::clean('r', 'a_stime', TYPE_STR),
                'a_etime' => Tool_Input::clean('r', 'a_etime', TYPE_STR),
                'a_city' => Tool_Input::clean('r', 'a_city', TYPE_UINT),
                'a_status' => Tool_Input::clean('r', 'a_status', TYPE_UINT),
            );
            if(!isset($_REQUEST['a_status']))
            {
                $this->search_conf['a_status'] = 2;
            }
        }
    }

    protected function main()
    {
        if ($this->mode == 'order')
        {
            $oo = new Order_Order();
            $data = $oo->getPromotionOrdersByActivityId($this->search_conf['aid'], FALSE, $this->start, $this->num);
            $this->sum = $data['sum'];
            $this->priceTotal = $data['priceTotal'];
        }
        else
        {
            $data = Activity_Api::getPromotionManjianList($this->start, $this->num, $this->search_conf);
        }
        $this->list = $data['list'];
        $this->total = $data['total'];
    }

    protected function outputBody()
    {
        $app = '/activity/promotion_manjian_list.php?' . http_build_query($this->search_conf);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('list', $this->list);
        if ($this->mode == 'order')
        {
            $cityList = City_Api::getCityList(TRUE);
            $this->smarty->assign('city_list', $cityList);
            $this->smarty->assign('_warehouseList', Conf_Warehouse::getWarehouseByAttr('ext_customer'));
            $this->smarty->assign('order_source', Conf_Order::$SOURCE_DESC);
            $this->smarty->assign('price_total', $this->priceTotal);
            $this->smarty->assign('sum', $this->sum);
            $this->smarty->display('promotion/manjian_order_list.html');
        }
        else
        {
            $this->smarty->assign('city_list', Conf_City::$CITY);
            $this->smarty->assign('conf', $this->search_conf);
            $this->smarty->assign('type_list', Conf_Activity::$AT_PROMOTION_TYPE_DESC);
            unset($this->search_conf['a_status']);
            $status_url = '/activity/promotion_manjian_list.php?' . http_build_query($this->search_conf);
            $this->smarty->assign('status_url', $status_url);
            $this->smarty->assign('status_list', Conf_Activity::$AT_PROMOTION_STATUS_DESC);
            $this->smarty->display('promotion/manjian_list.html');
        }
    }
}

$app = new App('pri');
$app->run();