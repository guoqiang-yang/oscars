<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $num=20;
    private $start;
    
    private $optype;
    private $supplierId;
    private $skuid;
    private $keyword;
    private $purchasePrice;
    
    private $html = '';
    private $selectedSkuInfos;
    
    protected function getPara()
    {
        $this->optype = Tool_Input::clean('r', 'optype', TYPE_STR);
        $this->supplierId = Tool_Input::clean('r', 'supplier_id', TYPE_UINT);
        $this->skuid = Tool_Input::clean('r', 'sku_id', TYPE_UINT);
        $this->keyword = Tool_Input::clean('r', 'keyword', TYPE_STR);
        $this->purchasePrice = Tool_Input::clean('r', 'purchase_price', TYPE_UINT);
        
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
    }
    
    protected function checkPara()
    {
        if (empty($this->supplierId))
        {
            throw new Exception('请求异常：缺少供应商id！');
        }
        
        if ($this->optype=='add' && empty($this->skuid))
        {
            throw new Exception('请求异常：缺少sku id！');
        }
    }
    
    protected function main()
    {
        $wssl = new Warehouse_Supplier_Sku_List();
        $this->selectedSkuInfos = Tool_Array::list2Map($wssl->getSupplierSkuList($this->supplierId), 'sku_id');
        
        switch ($this->optype)
        {
            case 'show':
                $this->_getSkuList();
                break;
            case 'add':
                $this->_addSku();
                break;
            default :
                throw new Exception('操作类型不存在！');
        }
        
        
    }
    
    private function _getSkuList()
    {
        $skuList = Shop_Api::searchSku($this->keyword, $this->start, $this->num);
        
        foreach($skuList['list'] as $_sid => &$one)
        {
            if (array_key_exists($_sid, $this->selectedSkuInfos))
            {
                $one['selected'] = 1;
                $one['purchase_price'] = $this->selectedSkuInfos[$_sid]['purchase_price'];
            }
            else
            {
                $one['selected'] = 0;
            }
        }
        
        $this->smarty->assign('pageHtml', Str_Html::getJsPagehtml2($this->start,  $this->num, $skuList['total'], 'searchSkuListForSupplier'));
        $this->smarty->assign('total', $skuList['total']);
        $this->smarty->assign('sku_list', $skuList['list']);
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2_list', Conf_Sku::$CATE2);
        
        $this->html = $this->smarty->fetch('warehouse/block_sku_list.html');
        
    }
    
    private function _addSku()
    {
        if (in_array($this->skuid, array(15467, 12904)) && $this->purchasePrice != 100)
        {
            throw new Exception('虚拟运费单价只能设置为1元！');
        }

        $wssl = new Warehouse_Supplier_Sku_List();
        $info = array(
            'purchase_price' => $this->purchasePrice,
        );
        $wssl->addSku($this->supplierId, $this->skuid, $info);
    }
    
    protected function outputBody()
    {
        $result = array('html'=>$this->html);
        
		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
    }
    
}

$app = new App();
$app->run();