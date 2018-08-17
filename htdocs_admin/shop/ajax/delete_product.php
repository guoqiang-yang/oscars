<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $pid;

    protected function getPara()
    {
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
    }

    protected function checkPara()
    {
        //检查有无库存
        $product = Shop_Api::getProductInfo($this->pid);
        //只能删除下架的商品！！
        if ($product['product']['status'] != Conf_Base::STATUS_OFFLINE)
        {
            throw new Exception('shop:only can delete offline product');
        }
        //有库存，有占用的商品不能删！！
        //查库存和占用，只查商品所属城市的仓库！！
        $sid = $product['sku']['sid'];
        $list = Warehouse_Api::getSkuStocks($sid);
        $hasStockWnameStr = '';
        $hasOccupiedWnameStr = '';
        $canNotDelete = false;
        $cityId = $product['city_id'];
        $cityWarehouses = Conf_Warehouse::$WAREHOUSE_CITY[$cityId];
        foreach ($list as $item)
        {
            if (!empty($cityWarehouses))
            {
                if (!in_array($item['wid'], $cityWarehouses))
                {
                    continue;
                }
            }
            $wname = Conf_Warehouse::$WAREHOUSES[$item['wid']];
            if ($item['num'] > 0)
            {
                $hasStockWnameStr .= "$wname,";
                $canNotDelete = true;
            }
            if ($item['occupied'] > 0)
            {
                $hasOccupiedWnameStr .= "$wname,";
                $canNotDelete = true;
            }
        }

        if ($canNotDelete)
        {
            $msg = sprintf("无法删除该商品！\n\n{$hasStockWnameStr}以上仓库还有库存！\n\n{$hasOccupiedWnameStr}以上仓库还有占用！");
            throw new Exception($msg);
        }
    }

    protected function main()
    {
        Shop_Api::deleteProduct($this->pid);
    }

    protected function outputPage()
    {
        $result = array('pid' => $this->pid);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();