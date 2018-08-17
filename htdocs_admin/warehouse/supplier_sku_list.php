<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    
    private $skuId;
    private $supplierId;
    
    private $supplierInfo;
    private $productList;
    private $securityData;
    private $initWeight = 0;
    private $dataForInorder = array();
    
    protected function getPara()
    {
        $this->skuId = Tool_Input::clean('r', 'sku_id', TYPE_UINT);
        $this->supplierId = Tool_Input::clean('r', 'supplier_id', TYPE_UINT);
    }
    
    protected function main()
    {
        $supplierInfo = Warehouse_Api::getSupplierAndSkuList($this->supplierId);
        $this->supplierInfo = $supplierInfo['info'];
        $this->productList = $supplierInfo['products'];
        
        Warehouse_Api::appendSkuInfo4SkuList($this->productList, 'sku_id');
        //$wids = array_keys(Conf_Warehouse::getWarehouseByAttr('stock'));
        
        $wids = array_keys($this->getAllowedWids4User());
        
        $this->securityData = Warehouse_Security_Stock_Api::getSecurityStock4MoreWids(Tool_Array::getFields($this->productList, 'sku_id'), $wids);
        
        $this->dataForInorder = array();
        foreach($this->securityData as $_skuid => $datas)
        {
            $val = array();
            foreach($datas as $_wid => $d)
            {
                $val[] = array('wid'=>$_wid, 'num'=>$d['order_num']); 
            }
            $this->dataForInorder[$_skuid] = json_encode($val);
        }

        // 将选定的sku放到首位
        if (!empty($this->skuId))
        {
            $selectedSkuInfo = array();
            foreach($this->productList as $key => $sinfo)
            {
                if ($sinfo['sku_id'] == $this->skuId)
                {
                    $this->initWeight += $sinfo['sku_info']['weight']/1000;
                    
                    $selectedSkuInfo[] = $sinfo;
                    unset($this->productList[$key]);
                    break;
                }
            }
            
            if (!empty($selectedSkuInfo))
            {
                $this->productList = array_merge($selectedSkuInfo, $this->productList);
            }
        }

        $proDao = new Data_Dao('t_product');
        $cityId = $_COOKIE['city_id'];

        foreach ($this->productList as &$item)
        {
            $where = sprintf(' sid=%d and city_id=%d ', $item['sku_id'], $cityId);
            $productInfo = $proDao->getListWhere($where);
            if (!empty($productInfo))
            {
                $item['_product'] = current($productInfo);
            }
        }

        $this->addFootJs(array('js/apps/supplier.js'));
        
    }
    
    protected function outputBody()
    {
     
        $this->smarty->assign('sku_id', $this->skuId);
        $this->smarty->assign('supplier', $this->supplierInfo);
        $this->smarty->assign('products', $this->productList);
        $this->smarty->assign('security_data', $this->securityData);
        $this->smarty->assign('init_weight', $this->initWeight);
        $this->smarty->assign('data_for_inorder', $this->dataForInorder);
        $this->smarty->assign('warehouses', Conf_Warehouse::getWarehouseByAttr('stock'));
        
        $this->smarty->display('warehouse/supplier_sku_list.html');
    }
    
    
}

$app = new App();
$app->run();