<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $paidSource;
    private $paidObjs;
    private $paymentType;
    private $statement_id;
    private $amount=0;
    private $sid;
    private $useAmount;
    private $supplier;
    private $supplierId;
    private $ws;
    private $sumAmount;

    protected function getPara()
    {
        $this->paidSource = Tool_Input::clean('r', 'paid_source', TYPE_UINT);
        $this->paidObjs = json_decode(Tool_Input::clean('r', 'bluk_datas', TYPE_STR), true);
        $this->statement_id = Tool_Input::clean('r', 'statement_id', TYPE_UINT);
        $this->useAmount = Tool_Input::clean('r', 'use_amount', TYPE_UINT);
        $this->supplierId = Tool_Input::clean('r', 'supplier_id', TYPE_UINT);
        $this->ws = new Warehouse_Supplier();
        $this->supplier = $this->ws->get($this->supplierId);
    }

    protected function checkPara()
    {
        if (empty($this->paidSource) || empty($this->paidObjs) || empty($this->statement_id))
        {
            throw new Exception('common:params error');
        }

        foreach($this->paidObjs as &$one)
        {
            if (!isset($one['id']) ||empty($one['id']) )
            {
                throw new Exception('请联系管理员查看问题！');
            }

            $one['price'] *= 100;
        }
        if ($this->supplier['amount'] < $this->useAmount || $this->useAmount < 0)
        {
            throw new Exception('输入金额错误！');
        }
    }

    protected function main()
    {
        $stockids = Tool_Array::getFields($this->paidObjs, 'id');
        $stockInfos = Warehouse_Api::getStockInInfos($stockids);

        foreach($stockInfos as $one)
        {
            if ($one['paid'] == Conf_Stock_In::UN_PAID)
            {
                throw new Exception('入库单：'.$one['id'].' 采购未兑账，不能支付！');
            }
            if ($one['paid'] == Conf_Stock_In::HAD_PAID)
            {
                throw new Exception('入库单：'.$one['id'].' 已经支付完成！');
            }
            if ($one['paid'] == Conf_Stock_In::CHECKED_ACCOUNT)
            {
                throw new Exception('入库单：'.$one['id'].' 财务未兑账，不能支付！');
            }
            if($one['statement_id'] == 0 || $one['statement_id'] != $this->statement_id)
            {
                throw new Exception('入库单：'.$one['id'].' 不属于结算单：'.$this->statement_id);
            }
            $this->sumAmount += $one['price'];
        }
        $useAmount = $this->useAmount;
        foreach($this->paidObjs as $obj)
        {
            if (!array_key_exists($obj['id'], $stockInfos))
            {
                throw new Exception('入库单不存在，支付失败，请联系管理员！');
            }

            $this->paymentType = !empty($stockInfos[$obj['id']]['payment_type'])?
            $stockInfos[$obj['id']]['payment_type']: Conf_Stock::PAYMENT_TRANSFER;

            // 更新：入库单表 t_stock_in
            $upData = array(
                'paid' => Conf_Stock_In::HAD_PAID,
                'real_amount' => $obj['price'],
                'payment_type' => $this->paymentType,
                'paid_source' => $this->paidSource,
            );
            $this->amount += $obj['price'];
            Warehouse_Api::updateStockIn($this->_uid, $obj['id'], $upData);

            // 插入记录：付款记录表 t_money_out_history
            $note = '[批量支付] 采购单ID：'.$stockInfos[$obj['id']]['oid'];
            if ($this->useAmount > 0)
            {
                if ($this->useAmount >= $obj['price'])
                {
                    $objPrice = $obj['price'];
                    $this->useAmount -= $obj['price'];
                    //只记余额流水
                    Finance_Api::addMoneyOutHistory($stockInfos[$obj['id']], $objPrice,
                        Conf_Money_Out::FINANCE_PAID, $note, $this->_uid, $this->paymentType, Conf_Finance::MO_BALANCE);
                } else {
                    $objPrice = $obj['price'] - $this->useAmount;
                    //记两条流水
                    Finance_Api::addMoneyOutHistory($stockInfos[$obj['id']], $objPrice,
                        Conf_Money_Out::FINANCE_PAID, $note, $this->_uid, $this->paymentType, $this->paidSource);
                    Finance_Api::addMoneyOutHistory($stockInfos[$obj['id']], $this->useAmount,
                        Conf_Money_Out::FINANCE_PAID, $note, $this->_uid, $this->paymentType, Conf_Finance::MO_BALANCE);
                    $this->useAmount = 0;
                }
            }
            else
            {
                Finance_Api::addMoneyOutHistory($stockInfos[$obj['id']], $obj['price'],
                    Conf_Money_Out::FINANCE_PAID, $note, $this->_uid, $this->paymentType, $this->paidSource);
            }
            $this->sid = $stockInfos[$obj['id']]['sid'];
        }
        $upData = array(
            'paid' => 2,
            'amount' => $this->sumAmount,
            'payment_type' => $this->paidSource,
            'payer_suid' => $this->_uid
        );
        Finance_StockIn_Statements_Api::updateStatement($this->statement_id, $upData);

        //写供应商余额流水
        if ($useAmount > 0)
        {
            $info = array(
                'sid' => $this->sid,
                'objid' => $this->statement_id,
                'price' => -$useAmount,
                'city_id' => $_COOKIE['city_id'],
                'type' => Conf_Finance::AMOUNT_TYPE_SETTLEMENT,
                'suid' => $this->_uid,
                'payment_type' => 0,
                'note' => '结算单' . $this->statement_id . '支付',
            );
            $this->ws->addSupplierAmountRecord($info);
        }
        //修改入库退货单状态
        $wsir = new Warehouse_Stock_In_Refund();
        $refundOrdList = $wsir->getByStatementId($this->statement_id);
        foreach ($refundOrdList as $item)
        {
            $type = Conf_Money_Out::STOCKIN_REFUND;
            $note = '入库单/退货单('.$item['stockin_id'].'/'.$item['srid'].')';

            $stockInInfo['srid'] = $item['srid'];
            $stockInInfo['id'] = $item['stockin_id'];
            $stockInInfo['sid'] = $item['supplier_id'];
            $stockInInfo['wid'] = $item['wid'];

            Finance_Api::addMoneyOutHistory($stockInInfo, $item['price'], $type, $note, $this->_uid);

            $wsir->update($item['srid'], array('step' => Conf_Stockin_Refund::REFUND_COMPLETED));
        }
    }

    protected function outputBody()
    {
        $result = array('st'=>1,'sid'=>$this->sid);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App();
$app->run();