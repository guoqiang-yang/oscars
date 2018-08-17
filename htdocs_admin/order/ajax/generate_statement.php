<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    private $ids;
    private $adminId;
    private $payment_type;
    private $wid;

    protected function getPara()
    {
        $this->ids = Tool_Input::clean('r', 'ids', TYPE_ARRAY);
        $this->adminId = $this->_user['suid'];
        $this->payment_type = Tool_Input::clean('r', 'payment_type', TYPE_UINT);
        $this->wid = Tool_Input::clean('r', 'wid', TYPE_UINT);
    }

    protected function checkPara()
    {
        if (empty($this->ids))
        {
            throw  new Exception('请勾选订单');
        }

        if (empty($this->_user['wid']) && empty($this->wid))
        {
            throw new Exception('参数错误，请刷新重试！');
        }
    }

    protected function main()
    {
        $wid = empty($this->_user['wid']) ? $this->wid : $this->_user['wid'];

        Logistics_Coopworker_Api::generateStatement($this->ids, $this->payment_type, $this->adminId, $wid);
    }

    protected function outputBody()
    {
        $result = array('ids' => $this->ids);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App();
$app->run();