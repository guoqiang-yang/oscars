<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $cid;
    private $amount;
    private $note;

    protected function getPara()
    {
        $this->cid = Tool_Input::clean('r', 'cid', TYPE_UINT);
        $this->amount = Tool_Input::clean('r', 'amount', TYPE_STR);
        $this->note = Tool_Input::clean('r', 'note', TYPE_STR);
    }

    protected function main()
    {
        $balance = intval(floatval(strval($this->amount)) * 100);

        if ($balance > 0)
        {
            //余额从客户账务流水 转移到 客户余额流水中
            Finance_Api::addMoneyInHistory($this->cid, Conf_Money_In::CUSTOMER_AMOUNT_TRANSFER, abs($balance), $this->_uid, 0, 0, '客户余额转移(备注：' . $this->note . ')', Conf_Base::PT_BALANCE);

            //插入客户账务余额流水
            $saveData = array(
                'type' => Conf_Finance::CRM_AMOUNT_TRANSFER,
                'price' => abs($balance),
                'payment_type' => Conf_Base::PT_BALANCE,
                'note' => '客户余额转移(备注：' . $this->note . ')',
                'objid' => 0,
                'suid' => $this->_uid,
            );

            Finance_Api::addCustomerAmountHistory($this->cid, $saveData);
        }
        else
        {
            throw new Exception('common:failure');
        }
    }

    protected function outputPage()
    {
        $result = array('cid' => $this->cid);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App('pri');
$app->run();