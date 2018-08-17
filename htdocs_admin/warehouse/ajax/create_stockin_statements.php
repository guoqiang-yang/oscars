<?php
/**
 * 兑账.
 */

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $stockinIds = array();
    private $refundIds = array();
    private $res;

    protected function getPara()
    {
        $this->stockinIds = Tool_Input::clean('r', 'stockinIds', TYPE_ARRAY);
        $this->refundIds = Tool_Input::clean('r', 'refundIds', TYPE_ARRAY);

        $this->res = array(
            'errno' => 0,
            'errmsg' => '',
            'data' => array(),
        );
    }
    
    protected function checkPara()
    {
        if (empty($this->stockinIds))
        {
            throw new Exception('common:params error');
        }
    }

    protected function main()
    {
        $wsir = new Warehouse_Stock_In_Refund();
        $stockInInfos = Warehouse_Api::getStockInInfos($this->stockinIds);

        if (!empty($this->refundIds))
        {
            $stockRefundInfos = $wsir->getBulk($this->refundIds);
            $statementIds4Refund = Tool_Array::getFields($stockRefundInfos, 'statement_id');
            if (array_sum($statementIds4Refund) > 0)
            {
                throw new Exception('退货单不能多次绑定结算单！');
            }
        }

        if(empty($stockInInfos))
        {
            throw new Exception('common:params error');
        }

        $statement_ids = Tool_Array::getFields($stockInInfos, 'statement_id');
        $statement_ids = array_unique($statement_ids);

        if(count($statement_ids)>1 || current($statement_ids)!=0)
        {
            throw new Exception('一个入库单只能绑定一个结算单');
        }

        $sids = Tool_Array::getFields($stockInInfos, 'sid');
        $sids = array_unique($sids);

        if(count($sids)>1)
        {
            throw new Exception('同一供应商的多个入库单才能生成结算单！');
        }

        $amount = Tool_Array::sumFields($stockInInfos, 'price');
        $id = Finance_StockIn_Statements_Api::addStockInStatements(current($sids),$amount,$this->stockinIds, $this->_uid);

        if(!$id)
        {
            throw new Exception('生成结算单失败');
        }else{
            if (!empty($this->refundIds))
            {
                foreach ($this->refundIds as $refundId)
                {
                    $wsir->update($refundId, array('statement_id' => $id));
                }
            }
            $this->res['data'] = array('id'=>$id);
        }

    }
    
    protected function outputBody()
    {
        $response = new Response_Ajax();
		$response->setContent($this->res);
		$response->send();
		exit;
    }
}

$app = new App();
$app->run();