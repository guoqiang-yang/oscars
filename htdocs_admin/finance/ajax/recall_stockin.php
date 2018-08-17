<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
	private $id;
	private $num;
	private $statementId;

	protected function getPara()
	{
		$this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
		$this->statementId = Tool_Input::clean('r', 'statement_id', TYPE_UINT);
		$this->num = Tool_Input::clean('r', 'num', TYPE_UINT);
	}

	protected function checkPara()
    {
        if(empty($this->id))
        {
            throw new Exception('common:params error');
        }
    }

    protected function main()
	{
        $wsir = new Warehouse_Stock_In_Refund();
        $wsi = new Warehouse_Stock_In();
        $stockinOrdList = $wsi->getList(array('statement_id' => $this->statementId), $total, '');
        $refundOrdList = $wsir->getByStatementId($this->statementId);
        if (!empty($refundOrdList))
        {
            $refundPrice = Tool_Array::getFields($refundOrdList, 'price');
            $totalRefundPrice = array_sum($refundPrice);

            $stockinPrice = Tool_Array::getFields($stockinOrdList, 'price');
            $totalStockinPrice = array_sum($stockinPrice);

            if (($totalStockinPrice - $stockinOrdList[$this->id]['price']) < $totalRefundPrice)
            {
                throw new Exception('撤销后应付金额小于0，如想撤销，请删除该结算单！');
            }
        }
        $upData = array(
            'statement_id' => 0
        );
		Finance_StockIn_Statements_Api::recallStockIn($this->id, $upData);
	}
	
	protected function outputPage()
	{
		$result = array('res'=> 'succ');
		$response = new Response_Ajax();
		$response->setContent($result);
		$response->send();
		exit;
	}
}

$app = new App('pri');
$app->run();