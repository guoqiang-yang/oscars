<?php

include_once ('../../../global.php');

class App extends App_Admin_Ajax
{
    private $id;
    private $pid;
    private $stock_num;
    private $note;

    protected function checkAuth()
    {
        parent::checkAuth('/activity/ajax/save_cpoint_product_stock_history');
    }

    protected function getPara()
    {
        $this->pid = Tool_Input::clean('r', 'pid', TYPE_UINT);
        $this->stock_num = Tool_Input::clean('r', 'stock_num', TYPE_UINT);
        $this->note = Tool_Input::clean('r', 'note', TYPE_STR);
    }

    protected function checkPara()
    {
        if (empty($this->pid))
        {
            throw new Exception('商品ID不能为空');
        }

        if (empty($this->note))
        {
            throw new Exception('修改原因不能为空');
        }
    }

    protected function main()
    {
        $this->id = Cpoint_Api::updateStock4Ajdust($this->pid, $this->stock_num, $this->note, $this->_uid);
    }

    protected function outputPage()
    {
        $result = array('id' => $this->id);
        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();
        exit;
    }
}

$app = new App('pri');
$app->run();

