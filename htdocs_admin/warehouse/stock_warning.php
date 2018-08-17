<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    
    private $start;
    private $num = 40;
    
    private $cate;
    
    private $search;
    private $cateAndBrand = array();
    
    private $total;
    private $alertList = array();
    private $diffList = array();
    
    protected function getPara()
    {
        $this->cate = Tool_Input::clean('r', 'cate', TYPE_UINT);
        
        $this->search = array(
            'cate1' => Tool_Input::clean('r', 'cate1', TYPE_UINT),
            'cate2' => Tool_Input::clean('r', 'cate2', TYPE_UINT),
            'bid' => Tool_Input::clean('r', 'bid', TYPE_UINT),
            'wid' => $this->getWarehouseId(),
            'sortby' => Tool_Input::clean('r', 'sortby', TYPE_STR),
        );
        
        if (empty($this->search['cate1']))
        {
            $this->search['cate1'] = 1;
        }   
        
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
    }
    
    protected function main()
    {
        switch($this->cate)
        {
            case 0: //库存预警
                $this->_setCateAndBrand($this->search['cate1'], $this->search['cate2'], $this->search['bid']);
                $ret = Warehouse_Api::getAlertList($this->search, $this->start, $this->num);
                $this->alertList = $ret['data'];
                $this->total = $ret['total'];
                break;
            
            case 1: //大库vs货位库存不一致
                $this->diffList = Warehouse_Api::getDiffListStock2Location($this->search['wid']);
                break;
            
            default:
                break;
        }
        $this->addFootJs('js/apps/stock.js');
    }
    
    protected function outputBody()
    {
        $app = '/warehouse/stock_warning.php?'. http_build_query($this->search);
		$pageHtml = Str_Html::getSimplePage($this->start, $this->num, $this->total, $app);
		$this->smarty->assign('pageHtml', $pageHtml);
        
        $this->smarty->assign('cate', $this->cate);
        $this->smarty->assign('search', $this->search);
        $this->smarty->assign('cate_brand', $this->cateAndBrand);
        $this->smarty->assign('current_url', $this->_genCurrentUrl());
        
        $this->smarty->assign('total', $this->total);
        $this->smarty->assign('alert_list', $this->alertList);
        $this->smarty->assign('diff_list', $this->diffList);
        
        $this->smarty->display('warehouse/stock_warning.html');
    }
    
    private function _setCateAndBrand($cate1=1, $cate2=0, $bid=0)
    {
        if (empty($cate1))
        {
            $cate1 = 1;
        }
        
        $allCate1 = Conf_Sku::$CATE1;
        $allCate2 = Conf_Sku::$CATE2[$cate1];
        
        foreach($allCate1 as $id => $info)
        {
            $this->cateAndBrand['cate1'][] = array(
                'id' => $id, 
                'name' => $info['name'],
                'html_class' => $id==$cate1? 'active': '',
                'query' => '?cate1='. $id,
            );
        }
        
        $this->cateAndBrand['cate2'][] = array(
            'id' => 0,
            'name' => '全部',
            'html_class' => 0==$cate2? 'active': '',
            'query' => '?cate1='. $cate1. '&cate2=0',
        );
        foreach($allCate2 as $id => $info)
        {
            $this->cateAndBrand['cate2'][] = array(
                'id' => $id, 
                'name' => $info['name'],
                'html_class' => $id==$cate2? 'active': '',
                'query' => '?cate1='. $cate1. '&cate2='. $id,
            );
        }
        
        //品牌
        if (!empty($cate2))
        {
            $brands = Shop_Api::getBrandList($cate2);
        }
        else
        {
            $brands = Shop_Api::getBrandListByCate1($cate1);
        }
        $this->cateAndBrand['brand'][] = array(
            'id' => 0,
            'name' => '全部',
            'html_class' => 0==$bid? 'active': '',
            'query' => '?cate1='. $cate1. '&cate2='. $cate2. '&bid=0',
        );
        foreach($brands as $oner)
        {
            $this->cateAndBrand['brand'][] = array(
                'id' => $oner['bid'], 
                'name' => $oner['name'],
                'html_class' => $oner['bid']==$bid? 'active': '',
                'query' => '?cate1='. $cate1. '&cate2='. $cate2. '&bid='. $oner['bid'],
            );
        }
    }
    
    private function _genCurrentUrl()
    {
        $search = $this->search;
        unset($search['sortby']);
        
        return '/warehouse/stock_warning.php?'. http_build_query($search);
    }
}

$app = new App();
$app->run();