<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $num = 20;
    private $start;
    
    private $search;
    
    private $total;
    private $conList;
    
    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->search = array(
            'cid' => Tool_Input::clean('r', 'cid', TYPE_UINT),
            'is_chk' => Tool_Input::clean('r', 'is_chk', TYPE_UINT),
            'has_order' => Tool_Input::clean('r', 'has_order', TYPE_UINT),
        );
    }
    
    protected function main()
    {
        $res = Crm2_Api::searchConstructionSite($this->search, $this->start, $this->num);
        $this->total = $res['total'];
        $this->conList = $res['data'];
        
        $this->addFootJs(array('js/core/autosuggest.js', 'js/core/area.js', 'js/apps/order.js'));
		$this->addCss(array('css/autosuggest.css'));
    }
    
    protected function outputBody()
    {
        $app = '/order/customer_construction_list.php?' . http_build_query($this->search);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);
        
        $this->smarty->assign('pageHtml', $pageHtml);
        $this->smarty->assign('start', $this->start);
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('constructions', $this->conList);
        $this->smarty->assign('search', $this->search);
        $this->smarty->assign('city', Tool_Array::jsonEncode(Conf_Area::$CITY));
		$this->smarty->assign('distinct', Tool_Array::jsonEncode(Conf_Area::$DISTRICT));
        $this->smarty->assign('area', Tool_Array::jsonEncode(Conf_Area::$AREA));
        
        $this->smarty->display('order/customer_construction_list.html');
    }
}

$app = new App();
$app->run();