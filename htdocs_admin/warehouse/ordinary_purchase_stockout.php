<?php
/**
 * Created by PhpStorm.
 * User: libaolong
 * Date: 2018/3/29
 * Time: 下午1:23
 */
include_once('../../global.php');

class App extends App_Admin_Page
{
    private $search;
    private $start = 0;
    private $num = 20;
    private $list;

    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/ordinary_purchase_stockout');
    }

    protected function getPara()
    {
        $this->search = array(
            'sid' => Tool_Input::clean('r', 'sid', TYPE_UINT),
            'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
            //'status' => Tool_Input::clean('r', 'status', TYPE_UINT),
            'wid' => Tool_Input::clean('r', 'wid', TYPE_UINT),
            'bdate' => Tool_Input::clean('r', 'bdate', TYPE_STR),
            'edate' => Tool_Input::clean('r', 'edate', TYPE_STR),
        );
        $this->search['bdate'] = !empty($this->search['bdate'])? $this->search['bdate'] : date('Y-m-d');
        $this->search['wid'] = !empty($this->search['wid'])? $this->search['wid'] : $this->_getDefaultWid();
        
        $this->search['status'] = !isset($_REQUEST['status'])? Conf_Base::STATUS_ALL: $_REQUEST['status'];
    }

    protected function main()
    {
        $this->search['city_id'] = $_COOKIE['city_id'];
        $skuDao = new Data_Dao('t_sku');
        $wtp = new Warehouse_Temporary_Purchase();

        $stockOutProductList = array();
        if (!empty($this->search['sid']))
        {
            $stockOutProductList = $wtp->getStockOutProduct($this->search, $this->start, $this->num);
        }

        $this->list = array();
        foreach ($stockOutProductList as $item)
        {
            $sku = $skuDao->get($item['sid']);
            $item['sku_title'] = $sku['title'];
            if (($item['step'] >= Conf_Order::ORDER_STEP_SURE && $item['step'] < Conf_Order::ORDER_STEP_PICKED && $item['vnum']>0) || $item['step'] < Conf_Order::ORDER_STEP_SURE)
            {
                $this->list[] = $item;
            }
        }

        $this->addFootJs(array(
            '/js/apps/warehouse.js'
        ));
    }

    protected function outputBody()
    {
        $this->smarty->assign('markStatus', Conf_Warehouse::getOrderVnumDealType());
        $this->smarty->assign('cityList', Conf_City::$CITY);
        $this->smarty->assign('orderStatus', Conf_Order::$ORDER_STEPS);
        $this->smarty->assign('search', $this->search);
        $this->smarty->assign('list', $this->list);
        $this->smarty->display('warehouse/ordinary_purchase_stockout.html');
    }

    private function _getDefaultWid()
    {
        $cityInfo = City_Api::getCity();

        $cityId = $cityInfo['city_id'];

        $wids = Conf_Warehouse::$WAREHOUSE_CITY[$cityId];

        return !empty($wids)? $wids[0]: Conf_Warehouse::WID_3;
    }
}

$app = new App();
$app->run();