<?php
/**
 * Created by PhpStorm.
 * User: libaolong
 * Date: 2018/5/14
 * Time: 下午3:21
 */
include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    private $stockinIds;
    private $html;
    private $supplier;

    protected function checkAuth ()
    {
        parent::checkAuth('/warehouse/ajax/create_stockin_statements');
    }

    protected function getPara ()
    {
        $this->stockinIds = Tool_Input::clean('r', 'ids', TYPE_ARRAY);
    }

    protected function main ()
    {
        $stockInInfos = Warehouse_Api::getStockInInfos($this->stockinIds);
        $sids = Tool_Array::getFields($stockInInfos, 'sid');
        $sids = array_unique($sids);
        if(count($sids)>1)
        {
            throw new Exception('同一供应商的多个入库单才能生成结算单！');
        }
        $sir = new Data_Dao('t_stock_in_refund');

        $this->supplier = Warehouse_Api::getSupplier($sids[0]);
        $sidStepWhere = sprintf(' supplier_id=%d and step=%d and statement_id=0 ', $sids[0], Conf_Stockin_Refund::HAD_REFUND);
        $stockinIdsWhere = sprintf(' stockin_id in(%s) and step=%d and statement_id=0 ', join(',', $this->stockinIds), Conf_Stockin_Refund::HAD_REFUND);

        $stockRefund4SidStep = $sir->getListWhere($sidStepWhere);
        $stockRefund4StockinIds = $sir->getListWhere($stockinIdsWhere);
        $keys = array_keys($stockRefund4SidStep);

        //判断是否有重复的数据
        if (!empty($keys))
        {
            foreach ($keys  as $key)
            {
                if (array_key_exists($key, $stockRefund4StockinIds))
                {
                    unset($stockRefund4StockinIds[$key]);
                }
            }
        }

        $stockRefundList = array_merge($stockRefund4SidStep, $stockRefund4StockinIds);

        /*算总应付*/
        $refundPrice = Tool_Array::getFields($stockRefundList, 'price');
        $stockinPrice = Tool_Array::getFields($stockInInfos, 'price');
        $totalPay = (array_sum($stockinPrice) - array_sum($refundPrice))/100;

        $this->smarty->assign('stockinList', $stockInInfos);
        $this->smarty->assign('stockRefundList', $stockRefundList);
        $this->smarty->assign('supplier', $this->supplier);
        $this->smarty->assign('warehouse', Conf_Warehouse::$WAREHOUSES);
        $this->smarty->assign('purchase', Conf_In_Order::$In_Order_Source);
        $this->smarty->assign('totalPay', $totalPay);

        $this->html = $this->smarty->fetch('warehouse/aj_get_statements_detail.html');
    }

    protected function outputPage()
    {
        $result = array('header' => $this->supplier['name'].'('.$this->supplier['sid'].')', 'html' => $this->html);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();