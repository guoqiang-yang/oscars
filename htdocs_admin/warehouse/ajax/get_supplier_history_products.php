<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $oid;
    private $orderInfo;
    private $orderProductList;
    private $historyProductList;
    private $html = '';
    
    protected function getPara()
    {
        $this->oid = Tool_Input::clean('r', 'oid', TYPE_UINT);
    }
    
    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/edit_in_order');
    }
    
    protected function checkPara()
    {
        if (empty($this->oid))
        {
            throw new Exception('参数错误！');
        }
    }
    
    protected function main()
    {
        // 取采购单详情
        $this->orderInfo = Warehouse_Api::getOrderInfo($this->oid);
        $this->orderProductList = $this->orderInfo['products'][Conf_In_Order::SRC_COMMON]; //只有普采使用

        // 取历史购买商品列表
        $this->historyProductList = Warehouse_Inorder_Api::getProductHistoryForSupplier($this->orderInfo['info']['sid'], $this->orderInfo['info']['wid']);
        
        foreach($this->historyProductList as &$sku)
        {
            $sid = $sku['sid'];
            
            if (array_key_exists($sid, $this->orderProductList))
            {
                $sku['_inorder']['price'] = $this->orderProductList[$sid]['price'];
                $sku['_inorder']['num'] = $this->orderProductList[$sid]['num'];
                $sku['_stockin']['num'] = $this->orderProductList[$sid]['_stock_in'];
            } 
            else
            {
                $sku['_inorder']['price'] = 0;
                $sku['_inorder']['num'] = 0;
                $sku['_stockin']['num'] = 0;
            }

            $sku['unit'] = !empty($sku['unit']) ? $sku['unit']: '个';
            $sku['_buy_price'] = (isset($sku['_inorder']) && !empty($sku['_inorder']['price'])) ?
                        $sku['_inorder']['price']/100 : ($sku['_stock']['purchase_price']/100?:$sku['_stock']['cost']/100);
        }
        
        $this->smarty->assign('cate1_list', Conf_Sku::$CATE1);
        $this->smarty->assign('cate2_list', Conf_Sku::$CATE2);
        $this->smarty->assign('history_product', $this->historyProductList);

        $this->html = $this->smarty->fetch('warehouse/aj_get_supplier_history_products.html');
    }
    
    protected function outputBody()
    {
        $st = !empty($this->html)? 1 : 0;
        $result = array('st'=>$st, 'html'=>  $this->html);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
    }
}

$app = new App();
$app->run();