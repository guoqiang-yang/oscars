<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $paidSource;
    private $paidObjs;
    private $paymentType;
    
    private $sid;
    
    protected function getPara()
    {
        $this->paidSource = Tool_Input::clean('r', 'paid_source', TYPE_UINT);
        $this->paidObjs = json_decode(Tool_Input::clean('r', 'bluk_datas', TYPE_STR), true);
    }
    
    protected function checkPara()
    {
        if (empty($this->paidSource) || empty($this->paidObjs))
        {
            throw new Exception('common:params error');
        }
        
        foreach($this->paidObjs as &$one)
        {
            if (!isset($one['id'])||empty($one['id']))
            {
                throw new Exception('请联系管理员查看问题！');
            }
            
            $one['price'] *= 100;
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
            if ($one['statement_id']>0)
            {
                throw new Exception('入库单：'.$one['id'].' 已生成结算单，请走结算单支付！');
            }
        }
        
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
            Warehouse_Api::updateStockIn($this->_uid, $obj['id'], $upData);
            
            // 插入记录：付款记录表 t_money_out_history
            $note = '[批量支付] 采购单ID：'.$stockInfos[$obj['id']]['oid'];
            Finance_Api::addMoneyOutHistory($stockInfos[$obj['id']], $obj['price'],
                Conf_Money_Out::FINANCE_PAID, $note, $this->_uid, $this->paymentType, $this->paidSource);
		
            $this->sid = $stockInfos[$obj['id']]['sid'];
        }
    }
    
    protected function outputBody()
    {
        $result = array('st'=>1, 'sid'=>$this->sid);

		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();

		exit;
    }
}

$app = new App();
$app->run();