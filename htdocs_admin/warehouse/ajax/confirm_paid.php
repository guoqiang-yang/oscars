<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $inOrderId; //采购单id，现款后货的采购单预付使用
	private $id;
	private $realAmount;
	private $paymentType;
    private $paidSource;
	private $note;
	private $type;
    private $stockInInfo;
	
	protected function getPara ()
	{
        $this->inOrderId = Tool_Input::clean('r', 'in_orderId', TYPE_UINT);
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
		$this->realAmount = Tool_Input::clean('r', 'real_amount', TYPE_NUM)*100; 
		$this->paymentType = Tool_Input::clean('r', 'payment_type', TYPE_UINT);
        $this->paidSource = Tool_Input::clean('r', 'paid_source', TYPE_UINT);
		$this->note = Tool_Input::clean('r', 'note', TYPE_STR);		//只用于更新 t_money_out_history
		$this->type = Tool_Input::clean('r', 'type', TYPE_UINT);
	}
	
	protected function checkPara ()
	{
		if (empty($this->id) && empty($this->inOrderId))
		{
			throw new Exception('common: id is empty');
		}
        
		if (Conf_Money_Out::FINANCE_PAID != $this->type
            && Conf_Money_Out::FINANCE_PRE_PAY != $this->type) //财务付款or财务预付
		{
			throw new Exception('common: type is not right');
		}
        if ($this->paymentType==0 || $this->paidSource==0)
        {
            throw new Exception('参数错误！');
        }
	}
	
	protected function main() 
	{
        // 入库单付款
        if (!empty($this->id))
        {
            //入库单详情
            $this->stockInInfo = Warehouse_Api::getStockInInfo($this->id);
            
            if (empty($this->stockInInfo))
            {
                throw new Exception('入库单异常');
            }
            
            if ($this->stockInInfo['paid']==Conf_Stock_In::UN_PAID)
            {
                throw new Exception('请联系 采购兑账！');
            }
            if ($this->stockInInfo['paid']==Conf_Stock_In::CHECKED_ACCOUNT)
            {
                throw new Exception('请联系 财务兑账！');
            }
            if ($this->stockInInfo['paid']==Conf_Stock_In::HAD_PAID)
            {
                throw new Exception('已经完成支付，请刷新页面！');
            }
            
            // 更新：入库单表 t_stock_in
            $upData = array(
                'paid' => 1,
                'real_amount' => $this->realAmount,
                'payment_type' => $this->paymentType,
                'paid_source' => $this->paidSource,
            );
            Warehouse_Api::updateStockIn($this->_uid, $this->id, $upData);

            // 获取入库单信息
            $moneyOutInfo = Warehouse_Api::getStockInInfo($this->id);
            $moneyOutInfo['wid'] = $moneyOutInfo['wid']==0? Conf_Warehouse::WID_3: $moneyOutInfo['wid'];
        }
        else // 采购单预付
        {
            $inOrderDetail = Warehouse_Api::getOrderBase($this->inOrderId);
            
            if ($inOrderDetail['paid'] !=0)
            {
                throw new Exception('已经支付！');
            }

            //写供应商余额流水
            $info = array(
                'sid' => $inOrderDetail['sid'],
                'objid' => $this->inOrderId,
                'price' => $this->realAmount,
                'city_id' => Conf_Warehouse::$WAREHOUSE_CITY_MAPPING[$inOrderDetail['wid']],
                'type' => Conf_Finance::AMOUNT_TYPE_PREPAY,
                'suid' => $this->_uid,
                'payment_type' => $this->paidSource,
                'note' => '采购单' . $this->inOrderId . '预付',
            );
            $ws = new Warehouse_Supplier();
            $ws->addSupplierAmountRecord($info);
            
            $upData['paid'] = 1;
            $upData['paid_source'] = $this->paidSource;
            Warehouse_Api::updateOrder(0, $this->inOrderId, $upData);
            
            $moneyOutInfo['sid'] = $inOrderDetail['sid'];
            $moneyOutInfo['wid'] = $inOrderDetail['wid'];
            $moneyOutInfo['oid'] = $this->inOrderId;
        }
		// 插入记录：付款记录表 t_money_out_history
        if ($this->type != Conf_Money_Out::FINANCE_PRE_PAY)
        {
            Finance_Api::addMoneyOutHistory($moneyOutInfo, $this->realAmount,  $this->type, $this->note, $this->_uid, $this->paymentType, $this->paidSource);
        }
	}
	
	protected function outputBody()
	{
		$result = array('id' => $this->id);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App();
$app->run();