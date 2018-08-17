<?php
/**
 * Created by PhpStorm.
 * User: libaolong
 * Date: 2018/5/29
 * Time: 上午10:03
 */
include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $sid;
    private $skuidPrice;

    protected function getPara()
    {
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->skuidPrice = Tool_Input::clean('r', 'info', TYPE_ARRAY);
    }

    protected function checkAuth()
    {
        parent::checkAuth('/warehouse/ajax/save_supplier_purchase_price');
    }

    protected function checkPara()
    {
        if (empty($this->sid))
        {
            throw new Exception('参数错误：供应商ID不能为空！');
        }
    }

    protected function main()
    {
        $wssl = new Warehouse_Supplier_Sku_List();
        $supplierInfo = Warehouse_Api::getSupplierAndSkuList($this->sid);
        $products = Tool_Array::list2Map($supplierInfo['products'], 'sku_id');

        foreach ($this->skuidPrice as $item)
        {
            list($skuid, $price) = explode('_', $item);
            $price = abs($price);
            $diffPrice = ($products[$skuid]['purchase_price'] - $price*100)/100;

            if ($diffPrice != 0)
            {
                $wssl->modifyPurchasePrice($this->sid, $skuid, $price*100);
            }
        }
    }

    protected function outputBody()
    {
        $result = array('msg' => 'suc');

        $response = new Response_Ajax();
        $response->setContent($result);

        $response->send();
    }

}

$app = new App();
$app->run();