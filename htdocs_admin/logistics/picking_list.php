<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $num = 20;
    private $start;
    private $search;
    private $pickingArea;
    private $total = 0;
    private $pickingList = array();
    private $wid;

    protected function checkAuth()
    {
        parent::checkAuth('/order/picking_list');
    }

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->wid = $this->getWarehouseId();
        $this->search = array(
            'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
            'wid' => $this->wid,
            'picking_type' => Tool_Input::clean('r', 'picking_type', TYPE_UINT),
            'aftersale_type' => Tool_Input::clean('r', 'aftersale_type', TYPE_UINT),
            'delivery_date' => Tool_Input::clean('r', 'delivery_date', TYPE_STR),
            'delivery_time' => Tool_Input::clean('r', 'delivery_time', TYPE_UINT),
        );
        $this->pickingArea = array(
            'A_area' => Tool_Input::clean('r', 'A_area', TYPE_STR),
            'B_area' => Tool_Input::clean('r', 'B_area', TYPE_STR),
            'C_area' => Tool_Input::clean('r', 'C_area', TYPE_STR),
            'D_area' => Tool_Input::clean('r', 'D_area', TYPE_STR),
        );

        if (empty($this->search['wid']))
        {
            $tmp = array_keys(App_Admin_Web::getAllowedWids4User());
            $this->search['wid'] = $tmp[0];
        }

        if (empty($this->search['delivery_date']))
        {
            $this->search['delivery_date'] = date('Y-m-d');
        }

        $this->search['picking_area'] = array();
        foreach ($this->pickingArea as $_area => $flag)
        {
            if ($flag == 'on')
            {
                $this->search['picking_area'][] = substr($_area, 0, 1);
            }
        }
    }

    protected function main()
    {
        if (!Conf_Warehouse::isUpgradeWarehouse($this->search['wid']))
        {
            return;
        }
        
        $ret = Order_Picking_Api::getPickingList($this->search, $this->start, $this->num);

        $this->total = $ret['total'];
        $this->pickingList = $ret['data'];
    }

    protected function outputBody()
    {
        if (empty($this->wid))
        {
            $this->search['wid'] = 0;
        }
        $app = '/logistics/picking_list.php?' . http_build_query($this->search) . '&' . http_build_query($this->pickingArea);
        $pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search', $this->search);
        $this->smarty->assign('picking_area', $this->pickingArea);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('picking_list', $this->pickingList);
        $this->smarty->assign('allowed_warehouses', App_Admin_Web::getAllowedWids4User());
        $this->smarty->assign('aftersale_types', Conf_Order::$AFTERSALE_TYPES);

        $this->smarty->display('order/picking_list.html');
    }
}

$app = new App();
$app->run();