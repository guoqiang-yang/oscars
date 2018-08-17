<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $wid;
    private $sid;
    private $processedType;
    
    private $allProcessedTypes;
    private $combinInfos = array();
    private $shortCombinInfos;
    
    private $maxCombinNum = 0;
    private $errHtml = '';
    
    protected function getPara()
    {
        $this->wid = $this->getWarehouseId();
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->processedType = Tool_Input::clean('r', 'processed_type', TYPE_UINT);
        
        $this->allProcessedTypes = Conf_Stock::getProcessedOrderTypes();
        $this->processedType = array_key_exists($this->processedType, $this->allProcessedTypes)?
                                $this->processedType: Conf_Stock::PROCESSED_ORDER_COM_SALE;
    }
    
    protected function main()
    {
        if (empty($this->wid) || empty($this->sid))
        {
            $this->errHtml = 'sku-id 或 仓库为空！';
            return;
        }
        
        $this->combinInfos = Shop_Api::getCombinSkuInfos($this->sid);
        if(empty($this->combinInfos))
        {
            $this->errHtml = '组合商品信息异常，';
        }
        
        $this->_genShortCombinDesc();
        
        //获取货位库存
        $this->_appendStockLocation();
        
        // 计算最大组合数量
        if ($this->processedType == Conf_Stock::PROCESSED_ORDER_COM_SALE)
        {
            $this->_calMaxConbinNum();
        }
        
        $this->addFootJs(array('js/apps/sku.js'));
    }
    
    protected function outputBody()
    {
        $this->smarty->assign('sid', $this->sid);
        $this->smarty->assign('wid', $this->wid);
        $this->smarty->assign('processed_type', $this->processedType);
        
        $this->smarty->assign('short_combin_info', $this->shortCombinInfos);
        //$this->smarty->assign('combin_infos', $this->combinInfos);
        $this->smarty->assign('combin_infos_combin', $this->combinInfos['combin'][$this->sid]);
        $this->smarty->assign('combin_infos_parts', $this->combinInfos['parts']);
        
        $this->smarty->assign('max_combin_num', $this->maxCombinNum);
        $this->smarty->assign('err_html', $this->errHtml);
        
        $this->smarty->assign('_warehouseList', $this->getAllowedWarehouses());
        
        $this->smarty->display('shop/processing_products.html');
    }
    
    private function _genShortCombinDesc()
    {
        $unit = !empty($this->combinInfos['combin'][$this->sid]['unit'])? $this->combinInfos['combin'][$this->sid]['unit']: '个';
        $this->shortCombinInfos = $this->combinInfos['combin'][$this->sid]['title'].'(1'. $unit .')'.' = ';
        
        foreach($this->combinInfos['combin'][$this->sid]['_rel_sku'] as $one)
        {
            $unit = !empty($this->combinInfos['parts'][$one['sid']]['unit'])? $this->combinInfos['parts'][$one['sid']]['unit']: '个';
            $this->shortCombinInfos .= $this->combinInfos['parts'][$one['sid']]['title']
                    .'('. $one['num']. $unit . ')'. ' + ';
        }
        
        $this->shortCombinInfos = mb_substr($this->shortCombinInfos, 0, -3, 'UTF-8');
    }
    
    private function _appendStockLocation()
    {
        $wl = new Warehouse_Location();
        $partSids = Tool_Array::getFields($this->combinInfos['parts'], 'sid');
        $locStock = $wl->getLocationsBySids(array_merge(array($this->sid), $partSids), $this->wid, 'actual');
        
        foreach($this->combinInfos as &$skus)
        {
            foreach($skus as &$skuInfo)
            {
                $sid = $skuInfo['sid'];
                $skuInfo['locs'] = array();
                
                foreach($locStock as $lone)
                {
                    if ($lone['sid'] == $sid)
                    {
                        $skuInfo['locs'][] = array(
                            'loc' => $lone['location'],
                            'num' => $lone['num'],
                        );
                    }
                }
                
                // 部件商品的比例关系
                if (empty($skuInfo['rel_sku']))
                {
                    foreach($this->combinInfos['combin'][$this->sid]['_rel_sku'] as $r)
                    {
                        if ($sid == $r['sid'])
                        {
                            $skuInfo['rate_num']  = $r['num'];
                        }
                    }
                }
            }
        }
    }
    
    private function _calMaxConbinNum()
    {
        $maxCom = 99999999;
        foreach($this->combinInfos['parts'] as $sid => $one)
        {
            $locNum = 0;
            foreach($one['locs'] as $l)
            {
                $locNum += $l['num'];
            }
            $rateNum = 999999999;
            foreach($this->combinInfos['combin'][$this->sid]['_rel_sku'] as $r)
            {
                if ($sid == $r['sid'])
                {
                    $rateNum = $r['num'];
                }
            }
            
            $maxCom = min ($maxCom, floor($locNum/$rateNum));
        }
        
        $this->maxCombinNum = $maxCom;
    }
    
   
}

$app = new App();
$app->run();