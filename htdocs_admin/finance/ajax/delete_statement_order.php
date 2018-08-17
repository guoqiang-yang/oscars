<?php
/**
 * Created by PhpStorm.
 * User: libaolong
 * Date: 2018/5/22
 * Time: 下午1:17
 */
include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
    private $msg;

    protected function checkAuth()
    {
        parent::checkAuth('/finance/ajax/recall_stockin');
    }


    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->id))
        {
            throw new Exception('结算单ID不能为空！');
        }
    }

    protected function main()
    {
        $statementOrdDetail = Finance_StockIn_Statements_Api::getStatementDetail($this->id);

        if (empty($statementOrdDetail) || $statementOrdDetail['status'] == Conf_Base::STATUS_DELETED)
        {
            throw new Exception('该结算单已删除，请刷新重试！');
        }
        //入库退货单解除绑定
        $wsir = new Warehouse_Stock_In_Refund();
        $refundOrdList = $wsir->getByStatementId($this->id);
        if (!empty($refundOrdList))
        {
            foreach ($refundOrdList as $refund)
            {
                $wsir->update($refund['srid'], array('statement_id' => 0));
            }
        }

        //入库单解除绑定
        $si = new Data_Dao('t_stock_in');
        $stockinOrdList = $si->getListWhere(sprintf(' statement_id=%d ', $this->id));
        foreach ($stockinOrdList as $stockIn)
        {
            $si->update($stockIn['id'], array('statement_id' => 0));
        }

        //删除结算单
        $res = Finance_StockIn_Statements_Api::updateStatement($this->id, array('status' => Conf_Base::STATUS_DELETED));
        if ($res)
        {
            $this->msg = '删除成功！';
        }
    }

    protected function outputPage()
    {
        $result = array('msg' => $this->msg);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();