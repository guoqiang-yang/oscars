<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $sid;
    private $paidSource;
    private $price;        //调账金额
    private $prePre;    //财务预付
    private $privilege; //优惠/返现
    private $note;
    private $adtype;    //财务调账使用 应付增加:1 应付减少:2

    protected function getPara()
    {
        $this->sid = Tool_Input::clean('r', 'sid', TYPE_UINT);
        $this->paidSource = Tool_Input::clean('r', 'paid_source', TYPE_UINT);
        $this->price = 100 * Tool_Input::clean('r', 'price', TYPE_NUM);
        $this->prePre = 100 * Tool_Input::clean('r', 'pre_pay', TYPE_UINT);
        $this->privilege = 100 * Tool_Input::clean('r', 'privilege', TYPE_UINT);
        $this->note = Tool_Input::clean('r', 'note', TYPE_STR);
        $this->adtype = Tool_Input::clean('r', 'adtype', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->paidSource) && $this->price == 0)
        {
            throw new Exception('请选择 款项来源！');
        }
        if (empty($this->price) && empty($this->prePre) && empty($this->privilege))
        {
            throw new Exception('finance:empty money');
        }
    }

    protected function main()
    {
        $stockInInfo = array(
            'id' => 0,
            'sid' => $this->sid,
            'wid' => 0,
        );

        if ($this->price)    //调账
        {
            $this->price = $this->adtype == 2 ? 0 - abs($this->price) : abs($this->price);
            $type = Conf_Money_Out::FINANCE_ADJUST;

            Finance_Api::addMoneyOutHistory($stockInInfo, $this->price, $type, $this->note, $this->_uid, 0);
        }
        else
        {
            if ($this->prePre)    //预付
            {
                $type = Conf_Money_Out::FINANCE_PRE_PAY;
                $price = 0 - abs($this->prePre);

                Finance_Api::addMoneyOutHistory($stockInInfo, $price, $type, $this->note, $this->_uid, 0, $this->paidSource);
            }
            if ($this->privilege) //返现
            {
                $type = Conf_Money_Out::SUPPLIER_PRIVILEGE;
                $price = 0 - abs($this->privilege);

                Finance_Api::addMoneyOutHistory($stockInInfo, $price, $type, $this->note, $this->_uid, 0);
            }
        }
    }

    protected function outputPage()
    {
        $result = array('objid' => 0);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();