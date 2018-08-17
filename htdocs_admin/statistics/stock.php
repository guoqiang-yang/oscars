<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $data;
    private $sum;
    private $wids;

    protected function getPara()
    {
    }

    protected function main()
    {
        $this->data = Statistics_Api::getStock();
        $this->wids = $this->getAllAllowedWids4User(0, true);

        foreach ($this->data as $wid => $info)
        {
            if(in_array($wid,$this->wids))
            {
                $this->sum['total'] += $info['total'];
                foreach ($info as $cate => $stock)
                {
                    $this->sum[$cate]['total'] += $stock['total'];
                }
            }
        }

        $this->addCss(array('css/simple-table.css'));
    }

    protected function outputBody()
    {
        $this->smarty->assign('warehouse_list', Conf_Warehouse::$WAREHOUSES);
        $this->smarty->assign('wids', $this->wids);
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('data', $this->data);
        $this->smarty->assign('sum', $this->sum);

        $this->smarty->display('statistics/stock.html');
    }
}

$app = new App('pri');
$app->run();
