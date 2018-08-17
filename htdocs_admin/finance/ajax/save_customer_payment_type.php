<?php
/**
 * Created by PhpStorm.
 * User: libaolong
 * Date: 2018/5/24
 * Time: 下午6:09
 */
include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
    private $paymentType;
    private $msg;

    protected function checkAuth()
    {
        parent::checkAuth('/finance/ajax/save_customer_payment_type');
    }

    protected function getPara()
    {
        $this->id = Tool_Input::clean('r', 'id', TYPE_UINT);
        $this->paymentType = Tool_Input::clean('r', 'payment_type', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->id))
        {
            throw new Exception('参数错误，请联系管理员！');
        }
        if (empty($this->paymentType))
        {
            throw new Exception('请选择支付方式！');
        }
    }

    protected function main()
    {
        $cah = new Data_Dao('t_customer_amount_history');

        $res = $cah->update($this->id, array('payment_type' => $this->paymentType));
        if ($res)
        {
            $this->msg = '修改成功！';
        } else {
            $this->msg = '修改失败！';
        }

    }

    protected function outputBody()
    {
        $result = array('msg' => $this->msg);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App();
$app->run();