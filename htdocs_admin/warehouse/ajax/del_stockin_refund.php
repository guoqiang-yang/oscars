<?php


include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $srid;
    private $retSt;

    protected function getPara()
    {
        $this->srid = Tool_Input::clean('r', 'srid', TYPE_UINT);
    }

    protected function main()
    {
        $wsir = new Warehouse_Stock_In_Refund();
        $refundInfo = $wsir->get($this->srid);

        if (empty($refundInfo))
        {
            throw new Exception('退货单不存在！');
        }

        if ($refundInfo['step'] == Conf_Stockin_Refund::UN_REFUND)
        {
            $data = array(
                'status' => Conf_Base::STATUS_DELETED,
            );

            $this->retSt = $wsir->update($this->srid, $data);

            $wsip = new Warehouse_Stock_In_Product();
            $wsip->deleteBySrid($this->srid);
        }
        else{
            throw new Exception('退货单已退货，不能删除！');
        }
    }

    protected function outputBody()
    {
        $result = array('st' => $this->retSt);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App();
$app->run();