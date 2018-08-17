<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{

    private $wid;
    private $sids;
    private $supplierId;
    private $html;

    protected function getPara()
    {
        $this->wid = json_decode(Tool_Input::clean('r', 'wid', TYPE_STR), true);
        $this->sids = json_decode(Tool_Input::clean('r', 'sids', TYPE_STR), true);
        $this->supplierId = Tool_Input::clean('r', 'supplier_id', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->wid) || empty($this->sids) || empty($this->supplierId))
        {
            throw new Exception('参数错误！');
        }
        
        // 第三方仓库/经销商仓库 不允许退货
        if (Conf_Warehouse::isAgentWid($this->wid))
        {
            throw new Exception('经销商入库单，请通过调拨退货！');
        }
    }

    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/ajax/refund_stockin');
    }

    protected function main()
    {
        $products = Warehouse_Location_Api::getSkuLocationBySids($this->sids, $this->wid, 'actual');

        $field = array('sid', 'price');
        $productList = Warehouse_Api::getSupplierLatestSkuListFromStockIn($this->supplierId, $this->sids, $start=0, $num=5, $field);
        foreach ($products as &$_product)
        {
            $_stockInfo = Warehouse_Api::getStockDetail($this->wid, $_product['sid']);
            if(isset($_stockInfo['stock'][$this->wid]['outsourcer_id']) && $_stockInfo['stock'][$this->wid]['outsourcer_id']>0)
            {
                throw new Exception('商品sid:'.$_product['sid'].'是外包供应商商品');
            }
            if (!empty($productList[$_product['sid']]))
            {
                $_priceList = Tool_Array::getFields($productList[$_product['sid']], 'price');
                $_avg = array_sum($_priceList) / count($_priceList);
            }
            else
            {
                $_avg = 0;
            }
            $_product['avg_price'] = sprintf('%.2f', $_avg / 100);
        }

        
        $this->smarty->assign('products', $products);
    }

    protected function outputBody()
    {
        $this->smarty->assign('wid', $this->wid);
        $this->smarty->assign('supplier_id', $this->supplierId);
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2_list', Conf_Sku::$CATE2);
        $this->smarty->assign('warehouse_list', Conf_Warehouse::$WAREHOUSES);
        $this->html = $this->smarty->fetch('warehouse/aj_get_supplier_refund_products.html');
        
        $result = array('supplier_id' => $this->supplierId, 'html' => $this->html);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App();
$app->run();