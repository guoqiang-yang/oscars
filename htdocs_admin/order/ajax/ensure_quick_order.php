<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/10/19
 * Time: 上午11:25
 */
include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $time;

    protected function getPara()
    {
        $this->method = Tool_Input::clean('r', 'method', TYPE_STR);
        $this->info = array(
            'oid' => Tool_Input::clean('r', 'oid', TYPE_UINT),
            'uid' => $this->_uid,
        );
    }
    protected function checkPara()
    {
        if ($this->method != 'ensure' || empty($this->info['oid']))
        {
            throw new Exception('非法操作');
        }
    }
    protected function main()
    {
        //确认订单
        $this->ret = Order_Quick_Api::update($this->info['oid'], array('ensure_id' => $this->_uid, 'ensure_status' => 1));

    }
    protected function outputPage()
    {
        $result = array('time' => $this->time);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App('pri');
$app->run();