<?php

include_once('../../global.php');

class App extends App_Admin_Page
{
    private $totalAmount;
    private $searchConf;
    private $statementInfo;
    private $stockInLists;
    private $financeList;
    private $supplier;
    private $refundOrdList;
    private $totalRefundPrice;
    private $sumAmount;

    protected function getPara()
    {
        $this->start = Tool_Input::clean('r', 'start', TYPE_UINT);
        $this->searchConf = array(
            'id' => Tool_Input::clean('r', 'id', TYPE_UINT),
            'statement_id' => Tool_Input::clean('r', 'statement_id', TYPE_UINT),
        );
    }

    protected function checkPara()
    {
        if(empty($this->searchConf['statement_id']))
        {
            throw new Exception('common:params error');
        }
    }

    protected function main()
    {
        //根据结算单ID查询退货单
        $sirDao = new Data_Dao('t_stock_in_refund');
        $this->refundOrdList = $sirDao->getListWhere(sprintf(' statement_id=%d ', $this->searchConf['statement_id']));
        $priceList = Tool_Array::getFields($this->refundOrdList, 'price');
        $this->totalRefundPrice = array_sum($priceList);

        $this->statementInfo = Finance_StockIn_Statements_Api::getStatementDetail($this->searchConf['statement_id']);
        if(empty($this->statementInfo) || $this->statementInfo['status'] == Conf_Base::STATUS_DELETED)
        {
            echo '<script charset="utf-8" type="text/javascript">alert("该结算单已被删除！"); window.location.href = "/finance/stock_in_statements.php"</script>';
            exit;
        }
        $this->supplier = Warehouse_Api::getSupplier($this->statementInfo['supplier_id']);
        $field = array('*');
        $order = 'order by id';
        $res = Warehouse_Api::getSupplierProductList($this->statementInfo['supplier_id'],$this->searchConf, $field, $order, 0, 0);
        $this->stockInLists = $res['data'];
        if(empty($this->stockInLists))
        {
            $this->totalAmount = 0;
        }else{
            if($this->searchConf['id']>0)
            {
                $ids = array();
                $ids[] = $this->searchConf['id'];
            }else{
                $ids = Tool_Array::getFields($this->stockInLists, 'id');
            }
            $totalAmount = Finance_StockIn_Statements_Api::getSumByStockInID($this->searchConf);
            $this->sumAmount = $totalAmount;
//            $refundAmount = Finance_StockIn_Statements_Api::getRefundSumByStockInID(array('ids'=>$ids));
            $this->totalAmount = $totalAmount-$this->totalRefundPrice;
        }

        $this->calChargeMoney($this->stockInLists, $this->refundOrdList);

        $this->total = $res['total'];
        $as = new Admin_Staff();
        $financeList = $as->getAll();
        $this->financeList = Tool_Array::list2Map($financeList, 'suid', 'name');

        $oids = array_unique(Tool_Array::getFields($res['list'], 'oid'));
        $orderInfos = Warehouse_Api::getOrderInfos($oids);

        foreach ($this->stockInLists as &$stockIn)
        {
            $stockIn['in_order_type'] = Conf_In_Order::$IN_ORDER_TYPES[$orderInfos[$stockIn['oid']]['in_order_type']];
        }
        $this->addFootJs(array('js/apps/stock.js'));
    }

    /**
     * 计算核销过后需支付的金额
     * @author libaolong
     * @param $stockinOrdList
     * @param $refundOrdList
     * @throws Exception
     */
    protected function calChargeMoney(&$stockinOrdList, $refundOrdList)
    {
        $refTotalPriceList = Tool_Array::getFields($refundOrdList, 'price');
        $stockTotalPriceList = Tool_Array::getFields($stockinOrdList, 'price');

        if (array_sum($refTotalPriceList) > array_sum($stockTotalPriceList))
        {
            echo '<script charset="utf-8" type="text/javascript">alert("金额错误，请删除该结算单！"); window.location.href = "/finance/stock_in_statements.php"</script>';
            exit;
        }

        foreach($refundOrdList as $k => $item)
        {
            if (array_key_exists($item['stockin_id'], $stockinOrdList))
            {
                $stockinOrdList[$item['stockin_id']]['_srid'][] = $item['srid'];
                $stockinOrdList[$item['stockin_id']]['_refund_info'][] = array('srid' => $item['srid'], 'price' => $item['price']);
                $stockinOrdList[$item['stockin_id']]['_srid_price'] += $item['price'];
                unset($refundOrdList[$k]);
            }
        }

        $_stockin = current($stockinOrdList);
        foreach($refundOrdList as $refItem)
        {
            foreach ($stockinOrdList as $key => &$siItem)
            {
                $canDealPrice = min($refItem['price'], $_stockin['price'] - $stockinOrdList[$key]['_srid_price']);
                $siItem['_srid_price'] += $canDealPrice;
                $siItem['_srid'][]  = $refItem['srid'];

                $refItem['price'] -= $canDealPrice;

                if ($refItem['price'] == 0) break;
            }
        }
    }

    protected function outputBody()
    {

        $this->smarty->assign('financeList', $this->financeList);
        $this->smarty->assign('searchConf', $this->searchConf);
        $this->smarty->assign('stock_in_lists', $this->stockInLists);
        $this->smarty->assign('stockin_order_num', count($this->stockInLists));
        $this->smarty->assign('all_pay_types', Conf_Stock::$PAYMENT_TYPES);
        $this->smarty->assign('all_steps', Conf_Stock_In::$Step_Descs);
        $this->smarty->assign('_warehouseList', $this->getAllowedWarehouses());
        $this->smarty->assign('statement_info', $this->statementInfo);
        $this->smarty->assign('paid_sources', Conf_Finance::$MONEY_OUT_PAID_TYPES);
        $this->smarty->assign('total_amount', number_format($this->totalAmount/100,2));
        $this->smarty->assign('sum_amount', number_format($this->sumAmount/100,2));
        $this->smarty->assign('total_refund_price', $this->totalRefundPrice);
        $this->smarty->assign('supplier', $this->supplier);
        $this->smarty->assign('refundOrdList', $this->refundOrdList);
        $this->smarty->display('finance/stockin_statement_detail.html');
    }
}

$app = new App();
$app->run();