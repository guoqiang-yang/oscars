<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $productList;
    private $areaList;
    private $searchConf;
    private $plan;

    protected function getPara()
    {
        $this->searchConf = array(
            'pid' => Tool_Input::clean('r', 'pid', TYPE_UINT),
            'is_deal' => Tool_Input::clean('r', 'is_deal', TYPE_UINT),
        );
    }

    protected function main()
    {
        if (!empty($this->searchConf['pid']))
        {
            $this->plan = Warehouse_Api::getInventoryPlan($this->searchConf['pid']);
            $this->productList = Warehouse_Api::getDiffProductList($this->searchConf);
            $locations = Tool_Array::getFields($this->productList, 'location');
            $areas = array();
            foreach ($locations as $location)
            {
                $arr = explode('-', $location);
                $areas[] = $arr[0];
            }
            $this->areaList = array_unique($areas);
        }

        $this->addFootJs(array('js/apps/stock.js'));
    }

    protected function outputBody()
    {
        $this->smarty->assign('plan', $this->plan);
        $this->smarty->assign('wid_list', Conf_Warehouse::getWarehouses());
        $this->smarty->assign('search_conf', $this->searchConf);
        $this->smarty->assign('area_list', $this->areaList);
        $this->smarty->assign('product_list', $this->productList);
        $this->smarty->assign('times', Conf_Stock::$STOCKTAKING_TIMES);
        $this->smarty->display('warehouse/deal_diff_products.html');
    }
}

$app = new App();
$app->run();