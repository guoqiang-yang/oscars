<?php

include_once('../../../global.php');

class App extends App_Admin_Ajax
{
    private $ids;

    protected function getPara()
    {
        $this->ids = Tool_Input::clean('r', 'ids', TYPE_ARRAY);
    }

    protected function checkPara()
    {
        if (empty($this->ids)) {
            throw new Exception('请勾选结算单');
        }
    }

    protected function main()
    {
        Logistics_Coopworker_Api::generateBatch($this->ids);
    }

    protected function outputPage()
    {
        $result = array('ids' => $this->ids);

        $response = new Response_Ajax();
        $response->setContent($result);
        $response->send();

        exit;
    }
}

$app = new App('pri');
$app->run();

