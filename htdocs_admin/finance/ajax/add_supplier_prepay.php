<?php
/**
 * Created by PhpStorm.
 * User: libaolong
 * Date: 2018/5/15
 * Time: 上午10:18
 */
include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    private $info;
    private $msg;

    protected function getPara()
    {
        $this->info = array(
            'sid' => Tool_Input::clean('r', 'sid', TYPE_UINT),
            'price' => Tool_Input::clean('r', 'price', TYPE_STR) * 100,
            'payment_type' => Tool_Input::clean('r', 'payment_type', TYPE_UINT),
            'note' => Tool_Input::clean('r', 'note', TYPE_STR),
            'city_id' => Tool_Input::clean('r', 'city_id', TYPE_UINT),
        );
    }

    protected function checkPara()
    {
        if (empty($this->info['sid']))
        {
            throw new Exception('sid不能为空！');
        }
        if (empty($this->info['price']))
        {
            throw new Exception('请输入金额！');
        }
        if ($this->info['price'] < 0)
        {
            throw new Exception('预付金额不能为负数！');
        }
        if (empty($this->info['city_id']))
        {
            throw new Exception('请选择城市！');
        }
        if (empty($this->info['payment_type']))
        {
            throw new Exception('请选择支付类型！');
        }
        if (empty($this->info['note']))
        {
            throw new Exception('请填写备注！');
        }
    }

    protected function main()
    {
        $sah = new Data_Dao('t_supplier_amount_history');
        $supDao = new Data_Dao('t_supplier');
        $amountHistory = $sah->order(' order by id desc ')->limit(0, 1)->getListWhere(sprintf(' sid=%d and status=%d ', $this->info['sid'], Conf_Base::STATUS_NORMAL));
        $amountHistory = current($amountHistory);

        if (!empty($amountHistory))
        {
            $this->info['amount'] = $amountHistory['amount'] + $this->info['price'];
        } else {
            $this->info['amount'] = $this->info['price'];
        }

        $suid = $this->_uid;
        $this->info['suid'] = $suid;
        $this->info['type'] = Conf_Finance::AMOUNT_TYPE_PREPAY;

        $res = $sah->add($this->info);
        if ($res)
        {
            $supDao->update($this->info['sid'], array('amount' => $this->info['amount']));
            $this->msg = '增加成功！';
        } else {
            $this->msg = '增加失败！';
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