<?php

include_once ('../../global.php');

class App extends App_Admin_Page
{
    private $num = 20;
    private $start;
    
    private $search;
    
    private $locationList;
    private $total;

    //残损虚拟储位
    private $shiftTypes;
    
    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        
        $curCity = City_Api::getCity();
        $dfWids = $this->_user['city_wid_map'][$curCity['city_id']];
        $wid = $this->getWarehouseId();
        $wid = !empty($wid)? $wid: $dfWids[0];
        
        if (empty($wid))
        {
            throw new Exception('操作人员数据异常！！仓库为空！');
        }
        
        $this->search = array(
            'title' => Tool_Input::clean('r', 'title', TYPE_STR),
            'is_used' => Tool_Input::clean('r', 'is_used', TYPE_UINT),
            'sid' => Tool_Input::clean('r', 'sid', TYPE_UINT),
            'wid' => $wid,
            'area' => strtoupper(Tool_Input::clean('r', 'area', TYPE_STR)), //按货区筛选
            'shelf' => Tool_Input::clean('r', 'shelf', TYPE_UINT),          //按货架筛选
            'layer' =>  Tool_Input::clean('r', 'layer', TYPE_UINT),         //按货架层筛选
            'un_shelved' => Tool_Input::clean('r', 'un_shelved', TYPE_UINT), //查询未上架商品
        );
        
        $this->shiftTypes = array(
            0 => array(
                'name' => '普通移架',
                'loc' => '',
                'clock' => false,
            ),
            1 => array(
                'name' => '残损移架',
            'loc' => Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_DAMAGED]['flag'],
                'clock' => true,
            ),
//            2 => array(
//                'name' => '预盘亏',
//            'loc' => Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_LOSS]['flag'],
//                'clock' => true,
//            ),
        );
        
    }
    
    protected function main()
    {
        $data = Warehouse_Location_Api::searchLocation($this->search, $this->start, $this->num);
        $this->locationList = $data['list'];
        $this->total = $data['total'];
        
        $this->addFootJs(array('js/apps/warehouse.js'));
    }
    
    protected function outputBody()
    {
        $app = '/warehouse/location_list.php?' . http_build_query($this->search);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);

        $canShiftVFLoc = array(Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_DAMAGED]['flag'],
            Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_LOSS]['flag']);

        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('search', $this->search);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('location_list', $this->locationList);
        $this->smarty->assign('shift_types', $this->shiftTypes);
        $this->smarty->assign('can_shift_VFLoc', $canShiftVFLoc);
        $this->smarty->assign('can_inventory_VFLoc', array(Conf_Warehouse::$Virtual_Flags[Conf_Warehouse::VFLAG_LOSS]['flag']));
        $this->smarty->assign('reasons', Conf_Warehouse::getStockHistoryReasons());

        $this->smarty->display('warehouse/location_list.html');
    }
}

$app = new App();
$app->run();